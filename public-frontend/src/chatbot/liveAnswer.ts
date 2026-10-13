/**
 * Turns the live index (database content + PDF text) into precise answers.
 *
 * Precision rules:
 *   - Figures, dates, names and list items are quoted VERBATIM from the source and attributed
 *     ("From 'Boarding fees structure 2026': ...") with a link to the document or page.
 *   - Nothing is calculated, summed or rephrased into new claims (e.g. no "total per term").
 *   - If the question isn't covered well enough by any passage, the functions return null and the
 *     engine falls back to its built-in answers or an honest "I don't know".
 */

import { DIRECTOR_NAME, HEADTEACHER_FALLBACK } from './knowledge.ts'
import { sanitizeLinks, type ChatLink } from './links.ts'
import { pdfPassages, search, type Hit, type LiveIndex } from './liveIndex.ts'
import type { LiveSource, SourceKind } from './live.ts'
import { tokens } from './text.ts'

export interface LiveReply {
  text: string
  links: ChatLink[]
  /** Which resolver produced it (shown in tests/diagnostics). */
  resolver: 'fees' | 'requirements' | 'calendar' | 'leadership' | 'staff' | 'passage'
  /** 0-1: how well the question is covered. */
  confidence: number
  /** True when the reply is a question back to the visitor (with `suggestions`). */
  clarify?: boolean
  suggestions?: Array<{ label: string; query: string }>
}

const MONTH_NAMES = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
const DAY_NAMES = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
const MONTH_ABBR = new Set(['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'])

/** "2026-10-22" -> "Thu 22 October 2026" (done by hand so the output never depends on locale/timezone). */
export function formatDate(iso: string): string {
  const [y, m, d] = iso.split('-').map(Number)
  if (!y || !m || !d) return iso
  const dow = DAY_NAMES[new Date(Date.UTC(y, m - 1, d)).getUTCDay()]
  return `${dow} ${d} ${MONTH_NAMES[m - 1]} ${y}`
}

const todayIso = (now: Date) => `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`

function snippet(text: string, max = 420): string {
  if (text.length <= max) return text
  const cut = text.slice(0, max)
  const end = Math.max(cut.lastIndexOf('. '), cut.lastIndexOf('! '), cut.lastIndexOf('? '))
  return (end > max * 0.5 ? cut.slice(0, end + 1) : cut.replace(/\s+\S*$/, '') + '…').trim()
}

const clean = (s: string) => s.replace(/\s*\|\s*/g, ' · ')
const isDraft = (s: LiveSource) => /\(\s*draft\s*\)/i.test(s.text.slice(0, 400))

function linksFor(sources: LiveSource[], max = 3): ChatLink[] {
  const out: ChatLink[] = []
  const seen = new Set<string>()
  for (const s of sources) {
    const add = (label: string, url?: string) => {
      if (url && !seen.has(url)) {
        seen.add(url)
        out.push({ label: label.length > 34 ? label.slice(0, 33) + '…' : label, url })
      }
    }
    if (s.kind === 'document' || s.kind === 'results') add(`Open: ${s.title}`, s.fileUrl)
    else add(s.title, s.url)
    if (out.length >= max) break
  }
  return sanitizeLinks(out)
}

const NOT_DISTINCTIVE = new Set(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun', 'jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'])

/** A word rare enough in the school's content to carry real meaning (weekday/month names never count). */
export function isDistinctive(index: LiveIndex, t: string): boolean {
  return !NOT_DISTINCTIVE.has(t) && (index.idf.get(t) ?? 0) >= 0.7 * index.maxIdf
}

const docSources = (index: LiveIndex) => index.sources.filter((s) => s.kind === 'document' && s.readable)

// ---------------------------------------------------------------------------------------------
// Fee structures
// ---------------------------------------------------------------------------------------------

interface FeeRow { label: string; values: string[] }
interface FeeDoc { source: LiveSource; columns: string[]; rows: FeeRow[]; cover?: string }

const FEE_ROW = /^([A-Za-z][A-Za-z .'&/-]*?)\s*:?\s*(?:\|\s*)?(\d{1,3}(?:,\d{3})+(?:\s*\|?\s*\d{1,3}(?:,\d{3})+)*)$/

/** Reads "Label | amount [| amount]" rows, and the "O-level / A-level" column header if present. */
export function parseFeeDoc(source: LiveSource): FeeDoc | null {
  const doc: FeeDoc = { source, columns: [], rows: [] }
  for (const p of pdfPassages(source.text)) {
    if (/\bo-?level\b.*\ba-?level\b/i.test(p) && !/\d{3},\d{3}/.test(p)) {
      doc.columns = [...p.matchAll(/\b([OA])-?level\b/gi)].map((m) => `${m[1].toUpperCase()}-level`)
      continue
    }
    if (/^functional fees cover/i.test(p)) {
      doc.cover = p.replace(/^functional fees cover:?\s*/i, '')
      continue
    }
    const m = FEE_ROW.exec(p)
    if (!m) continue
    const values = m[2].split(/\s*\|?\s+(?=\d)|\s*\|\s*/).map((v) => v.trim()).filter(Boolean)
    doc.rows.push({ label: m[1].trim().replace(/:$/, ''), values })
  }
  return doc.rows.length ? doc : null
}

const FEE_WORDS = new Set(['fee', 'per', 'term', 'structure', 'boarding', 'dayscholar', 'olevel', 'alevel', 'admission', 'payment', 'cost', 'pay', 'student', 'day', 'level', 'amount', 'total', 'much', 'term1', 'term2', 'term3', 's1', 's2', 's3', 's4', 's5', 's6', '2026', '2027'])

/** Words that mean the question is about what the fees cover / what to bring, not the amounts. */
const NOT_AMOUNT = new Set(['cover', 'include', 'requirement', 'personal', 'bring', 'laptop', 'uniform', 'mattress', 'book'])

export function feeAnswer(query: string, index: LiveIndex): LiveReply | null {
  const tok = new Set(tokens(query))
  if ([...NOT_AMOUNT].some((w) => tok.has(w))) return null
  // Words the school's content has never used (e.g. "bicycle") mean the question isn't about the fee tables.
  if ([...tok].some((t) => !FEE_WORDS.has(t) && !index.idf.has(t))) return null

  let docs = docSources(index).map(parseFeeDoc).filter((d): d is FeeDoc => d !== null)
  if (docs.length === 0) return null

  const lower = query.toLowerCase()
  const wantBoarding = tok.has('boarding')
  const wantDay = tok.has('dayscholar') || /\bday\b/.test(lower)
  if (wantBoarding && !wantDay) docs = docs.filter((d) => /boarding/i.test(d.source.title))
  else if (wantDay && !wantBoarding) docs = docs.filter((d) => /day/i.test(d.source.title))
  if (docs.length === 0) return null

  const rowFilter = (['tuition', 'admission', 'functional'] as const).find((w) => lower.includes(w))
  const wantO = tok.has('olevel')
  const wantA = tok.has('alevel')

  const blocks: string[] = []
  for (const d of docs) {
    const rows = rowFilter ? d.rows.filter((r) => r.label.toLowerCase().includes(rowFilter)) : d.rows
    if (rows.length === 0) continue
    const lines = rows.map((r) => {
      if (d.columns.length === r.values.length && d.columns.length > 1) {
        const cols = d.columns.map((c, i) => ({ c, v: r.values[i] })).filter(({ c }) => (wantO && !wantA ? c === 'O-level' : wantA && !wantO ? c === 'A-level' : true))
        return `• ${r.label}: ${cols.map(({ c, v }) => `${c} ${v}`).join(' · ')}`
      }
      return `• ${r.label}: ${r.values.join(' · ')}`
    })
    // one figure from one document reads as a single line: "Tuition fee per term (Boarding fees ...): A-level 1,800,000"
    if (docs.length === 1 && rows.length === 1) blocks.push(lines[0].replace(/^• /, '').replace(/^([^:]+):/, `$1 (${d.source.title}):`))
    else blocks.push(`${d.source.title}\n${lines.join('\n')}`)
  }
  if (blocks.length === 0) return null

  const text = blocks.join('\n\n')

  return { text, links: [...linksFor(docs.map((d) => d.source)), { label: 'Fees, Rules & Calendar', url: '/fee-structures' }].slice(0, 4), resolver: 'fees', confidence: 1 }
}

// ---------------------------------------------------------------------------------------------
// Personal requirements (what students must bring)
// ---------------------------------------------------------------------------------------------

interface Requirements { source: LiveSource; items: string[]; notes: string[] }

function parseRequirements(source: LiveSource): Requirements | null {
  const ps = pdfPassages(source.text)
  const start = ps.findIndex((p) => /^personal requirements/i.test(p))
  if (start < 0) return null
  const items: string[] = []
  const notes: string[] = []
  for (const p of ps.slice(start + 1)) {
    if (/^\d{1,2}\.\s/.test(p)) items.push(p.replace(/\s*Please note:?$/i, ''))
    else if (/^[IVX]{1,4}\.\s/.test(p)) notes.push(p)
    else if (/^please note/i.test(p)) continue
    else if (items.length && notes.length) break
    else if (items.length) break
  }
  return items.length ? { source, items, notes } : null
}

/** Words that make a question generic ("what must a student bring?") rather than about one item. */
const REQ_GENERIC = new Set(['personal', 'requirement', 'need', 'list', 'bring', 'term', 'toiletry', 'effect', 'pack', 'carry', 'basic', 'student', 'boarding', 'dayscholar', 'item', 'what', 'report', 'school'])

export function requirementsAnswer(query: string, index: LiveIndex): LiveReply | null {
  const tok = new Set(tokens(query))
  if ([...tok].some((t) => !REQ_GENERIC.has(t))) return null // asks about a specific item -> passage search

  const docs = docSources(index).map(parseRequirements).filter((d): d is Requirements => d !== null)
  if (docs.length === 0) return null

  const lower = query.toLowerCase()
  const wantBoarding = tok.has('boarding')
  const wantDay = tok.has('dayscholar') || /\bday\b/.test(lower)
  let chosen = docs
  if (wantBoarding && !wantDay) chosen = docs.filter((d) => /boarding/i.test(d.source.title))
  else if (wantDay && !wantBoarding) chosen = docs.filter((d) => /day/i.test(d.source.title))

  if (chosen.length !== 1) {
    const boarding = docs.find((d) => /boarding/i.test(d.source.title))
    const day = docs.find((d) => /day/i.test(d.source.title))
    if (boarding && day) {
      return {
        text: 'The school publishes a separate list of personal requirements for boarding students and for day scholars. Which one do you need?',
        links: [],
        resolver: 'requirements',
        confidence: 1,
        clarify: true,
        suggestions: [
          { label: 'Boarding students', query: 'personal requirements for boarding students' },
          { label: 'Day scholars', query: 'personal requirements for day scholars' },
        ],
      }
    }
    if (chosen.length === 0) return null
  }

  const d = chosen[0]
  const text = `Personal requirements (${d.source.title}):\n\n` + d.items.join('\n')
  return { text, links: linksFor([d.source]), resolver: 'requirements', confidence: 1 }
}

// ---------------------------------------------------------------------------------------------
// Calendar: upcoming events and month listings
// ---------------------------------------------------------------------------------------------

const UPCOMING_WORDS = ['upcoming', 'next', 'event', 'happening', 'schedule', 'soon', 'coming', 'activity', 'programme']

function eventLine(s: LiveSource, now?: Date): string {
  const done = s.completed ? ' (already held)' : now && (s.date as string) < todayIso(now) ? ' (date has passed)' : ''
  return `• ${formatDate(s.date as string)} - ${s.title}${s.text ? `: ${s.text}` : ''}${done}`
}

/** Words that show a month/"upcoming" question is about the calendar rather than some other topic. */
const EVENT_WORDS = ['event', 'calendar', 'happening', 'schedule', 'activity', 'programme', 'date', 'holiday', 'day', 'term', 'exam']

/** "Upcoming events" and "events in <month>" - answered from the calendar whatever else the words resemble. */
export function calendarListing(query: string, index: LiveIndex, now: Date): LiveReply | null {
  const tok = new Set(tokens(query))
  const hasMonth = [...tok].some((t) => MONTH_ABBR.has(t))
  const upcoming = ['upcoming', 'happening', 'coming', 'soon'].some((w) => tok.has(w)) || (tok.has('event') && tok.has('next'))
  if (!(hasMonth ? EVENT_WORDS.some((w) => tok.has(w)) : upcoming)) return null
  return calendarAnswer(query, index, now, true)
}

export function calendarAnswer(query: string, index: LiveIndex, now: Date, listingOnly = false): LiveReply | null {
  const tok = new Set(tokens(query))
  const events = index.sources.filter((s) => s.kind === 'event' && s.date).sort((a, b) => (a.date as string).localeCompare(b.date as string))
  const months = [...tok].filter((t) => MONTH_ABBR.has(t))
  const link: ChatLink[] = [{ label: 'School calendar', url: '/academics/school-calendar' }]

  if (months.length) {
    const monthNo = months.map((m) => [...MONTH_ABBR].indexOf(m) + 1)
    const inMonth = events.filter((e) => monthNo.includes(Number((e.date as string).slice(5, 7))))
    const rows = docSources(index).flatMap((d) =>
      search({ ...index, passages: index.passages.filter((p) => p.source === d) }, months.join(' '))
        .filter((h) => h.matched.some((m) => MONTH_ABBR.has(m)) && /\d{1,2}-[A-Za-z]{3}-\d{2}/.test(h.passage.text))
        .map((h) => ({ d, text: h.passage.text })),
    )
    if (inMonth.length === 0 && rows.length === 0) {
      return { text: `No events are listed for ${months.map((m) => MONTH_NAMES[[...MONTH_ABBR].indexOf(m)]).join(' / ')}.`, links: link, resolver: 'calendar', confidence: 0.9 }
    }
    const parts: string[] = []
    if (inMonth.length) parts.push(inMonth.slice(0, 10).map((e) => eventLine(e, now)).join('\n'))
    if (rows.length) {
      const d = rows[0].d
      parts.push(`${rows.slice(0, 8).map((r) => `• ${clean(r.text)}`).join('\n')}${isDraft(d) ? '\n(From the term programme, which is marked as a draft.)' : ''}`)
    }
    return { text: parts.join('\n\n'), links: [...link, ...linksFor(rows.length ? [rows[0].d] : [], 1)], resolver: 'calendar', confidence: 0.9 }
  }

  if ([...UPCOMING_WORDS].some((w) => tok.has(w))) {
    const today = todayIso(now)
    const upcoming = events.filter((e) => !e.completed && (e.date as string) >= today)
    if (upcoming.length === 0) return { text: "No upcoming events are listed right now.", links: link, resolver: 'calendar', confidence: 0.9 }
    return {
      text: `Upcoming events:\n${upcoming.slice(0, 6).map((e) => eventLine(e, now)).join('\n')}${upcoming.length > 6 ? `\n…and ${upcoming.length - 6} more on the calendar page.` : ''}`,
      links: link,
      resolver: 'calendar',
      confidence: 0.9,
    }
  }

  if (listingOnly) return null

  // Specific lookup ("when does term 3 begin?", "when is reporting day?"): the best-matching calendar
  // event or programme row. Needs a distinctive word to match so vague questions are not answered.
  const hits = search(index, query, new Set<SourceKind>(['event', 'document'])).filter((h) => h.coverage >= 0.5 && (h.matched.length >= 2 || h.matched.some((t) => isDistinctive(index, t))))
  if (hits.length) {
    const src = hits[0].passage.source
    if (src.kind === 'event') {
      const evs = hits.filter((h) => h.passage.source.kind === 'event' && h.score >= hits[0].score * 0.5).slice(0, 6)
      return { text: evs.map((h) => eventLine(h.passage.source, now)).join('\n'), links: link, resolver: 'calendar', confidence: hits[0].coverage }
    }
    const near = hits.filter((h) => h.passage.source === src && h.score >= hits[0].score * 0.85).slice(0, 4)
    return {
      text: `${near.map((h) => `• ${clean(h.passage.text)}`).join('\n')}${isDraft(src) ? '\n(From the term programme, which is marked as a draft.)' : ''}`,
      links: linksFor([src]),
      resolver: 'calendar',
      confidence: hits[0].coverage,
    }
  }
  return null
}

// ---------------------------------------------------------------------------------------------
// Staff by department ("teachers in ICT", "who teaches chemistry", "head of physics")
// ---------------------------------------------------------------------------------------------

const STAFF_ROLE_WORDS = new Set(['teacher', 'teach', 'staff', 'head', 'hod', 'department', 'member', 'tutor', 'instructor'])

interface StaffRow { source: LiveSource; dept: string; deptTokens: Set<string>; category: string }

function staffRows(index: LiveIndex): StaffRow[] {
  return index.sources
    .filter((s) => s.kind === 'staff')
    .map((s) => {
      const m = /^(.*?)\s*\(([^)]*)\)\s*$/.exec(s.text)
      const dept = (m ? m[1] : s.text).trim()
      // a department literally named "IT" would vanish as the filler word "it": it is part of ICT
      const deptTokens = new Set(/^it$/i.test(dept) ? ['ict'] : tokens(dept))
      return { source: s, dept, deptTokens, category: m ? m[2].trim() : '' }
    })
}

/** Lists the staff of one department when the question names a role word plus a department the staff list knows. */
export function staffAnswer(query: string, index: LiveIndex): LiveReply | null {
  const tok = [...new Set(tokens(query))]
  if (!tok.some((t) => STAFF_ROLE_WORDS.has(t))) return null

  const rows = staffRows(index)
  if (rows.length === 0) return null
  const known = new Set(rows.flatMap((r) => [...r.deptTokens]))
  // department words in the question; unrelated words (typos, "i mean") are ignored
  const wanted = tok.filter((t) => !STAFF_ROLE_WORDS.has(t) && known.has(t))
  if (wanted.length === 0) return null

  const inDept = rows.filter((r) => wanted.every((w) => r.deptTokens.has(w)))
  if (inDept.length === 0) return null
  const onlyHead = tok.includes('head') || tok.includes('hod')
  const picked = onlyHead ? inDept.filter((r) => /head/i.test(r.category)) : inDept
  if (picked.length === 0) return null

  // heads of department first, then everyone else in the order the school lists them
  const ordered = [...picked].sort((a, b) => Number(/head/i.test(b.category)) - Number(/head/i.test(a.category)))
  const dept = ordered[0].dept
  const lines = ordered.map((r) => `• ${r.source.title}${/head/i.test(r.category) ? ' (Head of Department)' : ''}`)
  return {
    text: `${dept}:\n${lines.join('\n')}`,
    links: [{ label: 'Staff', url: '/staff' }],
    resolver: 'staff',
    confidence: 1,
  }
}

// ---------------------------------------------------------------------------------------------
// Leadership (names from the staff / board lists)
// ---------------------------------------------------------------------------------------------

export function leadershipAnswer(query: string, index: LiveIndex): LiveReply | null {
  const tok = new Set(tokens(query))
  const head = index.sources.find((s) => s.kind === 'staff' && /head ?teacher/i.test(s.text) && !/deputy|assistant/i.test(s.text))
  const chair = index.sources.find((s) => s.kind === 'board' && /chair/i.test(s.text))
  if (!head && !chair) return null

  // Answer just the role that was asked about - the bare name, since the question already names the
  // role. Only a general question lists several roles, and then each line needs its label.
  const all: Array<{ key: string; label: string; name: string }> = [
    { key: 'director', label: 'Director', name: DIRECTOR_NAME },
    { key: 'headteacher', label: 'Head Teacher', name: head ? head.title : HEADTEACHER_FALLBACK },
  ]
  if (chair) all.push({ key: 'chair', label: chair.text, name: chair.title })
  const asked = all.filter((r) => tok.has(r.key))
  const lines = asked.length === 1 ? [asked[0].name] : (asked.length ? asked : all).map((r) => `• ${r.label}: ${r.name}`)
  return {
    text: lines.join('\n'),
    links: [
      { label: "Head Teacher's message", url: '/headteacher' },
      { label: 'Board members', url: '/board-members' },
      { label: 'Staff', url: '/staff' },
    ],
    resolver: 'leadership',
    confidence: 1,
  }
}

// ---------------------------------------------------------------------------------------------
// Entry-specific dispatch
// ---------------------------------------------------------------------------------------------

/** Called when a built-in entry matched; may replace its generic answer with live, precise data. */
export function resolveForEntry(entryId: string, query: string, index: LiveIndex, now: Date): LiveReply | null {
  switch (entryId) {
    case 'fees':
      return feeAnswer(query, index)
    case 'personal-needs':
      return requirementsAnswer(query, index)
    case 'calendar':
    case 'news-events':
      return calendarAnswer(query, index, now)
    case 'leadership':
      return leadershipAnswer(query, index)
    default:
      return null
  }
}

// ---------------------------------------------------------------------------------------------
// General passage retrieval
// ---------------------------------------------------------------------------------------------

const MIN_COVERAGE = 0.6
/** A lone matching word must be rare (carry real information) for a passage to answer on its own. */
const MIN_SINGLE_IDF_RATIO = 0.55

const LIST_KINDS: ReadonlySet<SourceKind> = new Set(['staff', 'board', 'event', 'achiever'])

/** The matched sentences are worth showing only when they are not already in the item's opening. */
function extraNeeded(index: LiveIndex, h: Hit): boolean {
  if (h.passage.lead || h.passage.source.text.length <= 420) return false
  const lead = index.passages.find((p) => p.source === h.passage.source && p.lead)
  // labelled facts ("Speaker: ...", "Choir: ...") are appended at the end of an item's text
  return !!lead && (/\b(?:Speaker|Choir): /.test(h.passage.text) || h.matched.some((t) => !lead.tokens.has(t)))
}

export interface PassageResult { reply: LiveReply; matchedCount: number; sourceKind: SourceKind; distinctive: boolean }

export function passageAnswer(query: string, index: LiveIndex, now: Date): PassageResult | null {
  const hits = search(index, query)
  const top = hits[0]
  if (!top || top.coverage < MIN_COVERAGE) return null
  if (top.matched.length === 1 && (index.idf.get(top.matched[0]) ?? 0) < MIN_SINGLE_IDF_RATIO * index.maxIdf) return null

  const kind = top.passage.source.kind
  const spread = kind === 'event' ? 0.5 : 0.8 // list every event about the same thing (e.g. all visitation days)
  const near: Hit[] = hits.filter((h) => h.passage.source.kind === kind && h.coverage >= (kind === 'event' ? 0.4 : MIN_COVERAGE) && h.score >= top.score * spread)
  const src = top.passage.source
  let text: string
  let sources: LiveSource[] = [src]

  if (LIST_KINDS.has(kind)) {
    const seen = new Set<string>()
    const rows = near.filter((h) => !seen.has(h.passage.source.id) && seen.add(h.passage.source.id)).slice(0, 6)
    if (kind === 'event') rows.sort((a, b) => (a.passage.source.date ?? '').localeCompare(b.passage.source.date ?? ''))
    sources = rows.map((h) => h.passage.source)
    const lines = rows.map((h) => {
      const s = h.passage.source
      if (kind === 'event') return eventLine(s, now)
      return `• ${s.title}${s.text ? ` - ${s.text}` : ''}`
    })
    text = lines.join('\n')
  } else if (kind === 'document') {
    // Same line found in several documents (e.g. the boarding and day-scholar fee structures): show each.
    const bySource = new Map<LiveSource, string[]>()
    for (const h of near) {
      const rows = bySource.get(h.passage.source) ?? []
      if (!rows.includes(h.passage.text) && rows.length < 4) rows.push(h.passage.text)
      bySource.set(h.passage.source, rows)
    }
    sources = [...bySource.keys()].slice(0, 3)
    // the document name is only shown when more than one document answers (so they can be told apart)
    text = sources
      .map((s) => `${sources.length > 1 ? `${s.title}:\n` : ''}${(bySource.get(s) ?? []).map((r) => `• ${clean(snippet(r, 380))}`).join('\n')}${isDraft(s) ? '\n(From the term programme, which is marked as a draft.)' : ''}`)
      .join('\n\n')
  } else if (kind === 'news' || kind === 'post' || kind === 'voice') {
    text = `${src.title}${src.date ? ` (${formatDate(src.date)})` : ''}\n${snippet(src.text) || ''}`.trim()
    sources = [src, ...near.map((h) => h.passage.source).filter((s) => s !== src)].filter((s, i, a) => a.indexOf(s) === i).slice(0, 3)
  } else {
    // club / activity / programme / alumni: when several items match about equally (e.g. every
    // Inspiration Night), list them briefly; otherwise give the one best match.
    const siblings = near.filter((h, i, a) => a.findIndex((x) => x.passage.source === h.passage.source) === i && h.score >= top.score * 0.92)
    if (siblings.length >= 2) {
      sources = siblings.slice(0, 4).map((h) => h.passage.source)
      text = siblings.slice(0, 4).map((h) => `• ${h.passage.source.title}${h.passage.source.date ? ` (${formatDate(h.passage.source.date)})` : ''}: ${snippet(h.passage.source.text, 110)}${extraNeeded(index, h) ? ` … ${snippet(h.passage.text, 140)}` : ''}`).join('\n')
    } else {
      // the item's opening, plus the matched sentences when they come from further into the text
      text = `${src.title}\n${snippet(src.text)}${extraNeeded(index, top) ? `\n… ${snippet(top.passage.text, 300)}` : ''}`.trim()
    }
  }

  return {
    reply: { text, links: linksFor(sources), resolver: 'passage', confidence: top.coverage },
    matchedCount: top.matched.length,
    sourceKind: kind,
    distinctive: top.matched.some((t) => isDistinctive(index, t)),
  }
}
