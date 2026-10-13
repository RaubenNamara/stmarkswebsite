/**
 * Search index over the "live" knowledge (see live.ts): splits every source into short passages
 * and ranks passages against a question.
 *
 * Ranking is deterministic keyword retrieval, not a language model:
 *   - each word is weighted by IDF (how rare it is across all passages), so "boarding" or
 *     "chemistry" counts for much more than "term" or "fee";
 *   - `coverage` = share of the question's total weight that a passage actually contains. A question
 *     with words that appear nowhere in the school's content (e.g. "weather") gets low coverage
 *     for every passage, so the bot declines instead of answering with something loosely related.
 */

import type { LiveSource, SourceKind } from './live.ts'
import { tokens } from './text.ts'

export interface Passage {
  source: LiveSource
  /** Exact text shown to the visitor (verbatim from the source). */
  text: string
  tokens: Set<string>
  titleTokens: Set<string>
  /** First passage of a source (preferred when several passages tie). */
  lead: boolean
}

export interface LiveIndex {
  sources: LiveSource[]
  passages: Passage[]
  idf: Map<string, number>
  maxIdf: number
}

export interface Hit {
  passage: Passage
  score: number
  /** 0-1: how much of the question this passage accounts for. */
  coverage: number
  matched: string[]
}

/** Words that describe the kind of question ("is X required?") rather than its topic. */
const QUESTION_WORDS = new Set(['requirement', 'include', 'provide'])

const MONTHS = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec']

/** "2026-10-22" -> ["22", "oct", "2026"] so events can be found by month or day number. */
function dateWords(iso?: string): string[] {
  if (!iso) return []
  const [y, m, d] = iso.split('-')
  const month = MONTHS[Number(m) - 1]
  return month ? [String(Number(d)), month, y] : []
}

const DAY_ROW = /^(?:\d+\s+)?(?:mon|tue|wed|thu|fri|sat|sun)[a-z]*\.?\s+\d{1,2}[- ][A-Za-z]{3}/i
const DATE_ONLY = /^(?:\d+\s+)?[A-Za-z]{3,9}\.?\s+\d{1,2}[- ][A-Za-z]{3}[- ]\d{2,4}$/
const LIST_ITEM = /^(?:\d{1,2}|[IVX]{1,4})\.\s/
const AMOUNT_END = /\d{1,3}(?:,\d{3})+\s*$/
const SENTENCE_END = /[.:!?)]\s*$/

/**
 * Splits extracted PDF text into passages that keep one logical row/item together:
 * a fee line, a numbered requirement (even when it wraps), a dated programme row with its
 * "in charge" continuation lines, or a running paragraph.
 */
export function pdfPassages(text: string): string[] {
  const out: string[] = []
  let cur = ''
  let open = false

  const flush = () => {
    const t = cur.replace(/\s+/g, ' ').trim()
    // drop empty programme days (a date with no activity), keep everything else of substance
    if (t.length >= 6 && !DATE_ONLY.test(t)) out.push(t.length > 600 ? t.slice(0, 600) : t)
    cur = ''
    open = false
  }

  for (const raw of text.split('\n')) {
    const line = raw.replace(/\t+/g, ' | ').replace(/\s+/g, ' ').trim()
    if (!line || /^\d{1,3}$/.test(line)) {
      // blank lines and lone week/page numbers end a paragraph but never a dated row's continuation
      if (!DAY_ROW.test(cur)) flush()
      continue
    }

    const isDay = DAY_ROW.test(line)
    const isRow = raw.includes('\t') || AMOUNT_END.test(line)
    const isItem = LIST_ITEM.test(line)
    const isHeading = /^[A-Z][A-Z0-9 .,'&\-–]{5,}$/.test(line) || /^please note/i.test(line)

    if (isDay || isRow || isItem || isHeading || !open) {
      flush()
      cur = line
      open = isDay || isItem || (!isRow && !isHeading && !SENTENCE_END.test(line))
      if (isHeading) open = false
    } else {
      cur += cur.endsWith('-') ? line : ' ' + line // "out-of-" + "class" -> "out-of-class"
      if (!DAY_ROW.test(cur) && SENTENCE_END.test(line)) open = false
    }
  }
  flush()
  return out
}

/** Breaks long prose into 2-sentence windows (1-sentence overlap) so a precise sentence can win. */
function proseWindows(text: string): string[] {
  // (no regex lookbehind: older iPhones' Safari would fail to parse the whole module)
  const s = (text.match(/[^.!?]+[.!?]+(?:\s+|$)|[^.!?]+$/g) ?? [text]).map((x) => x.trim()).filter(Boolean)
  if (s.length <= 2) return [text]
  const out: string[] = []
  for (let i = 0; i < s.length; i += 1) out.push(s.slice(i, i + 2).join(' '))
  return out
}

function toSet(...parts: string[]): Set<string> {
  return new Set(tokens(parts.join(' ')))
}

export function buildIndex(sources: LiveSource[]): LiveIndex {
  const passages: Passage[] = []

  const seenSources = new Set<LiveSource>() // by identity: ids can repeat across different tables
  const add = (source: LiveSource, text: string, extra = '') => {
    const titleTokens = toSet(source.title)
    // A PDF line must be found by its own words: adding the document title to every line would make
    // all of a document's lines match a title-only question (e.g. "day scholars fee structure").
    const own = source.kind === 'document' ? toSet(text, extra) : toSet(text, source.title, extra)
    const lead = !seenSources.has(source)
    seenSources.add(source)
    passages.push({ source, text, tokens: own, titleTokens, lead })
  }

  for (const s of sources) {
    switch (s.kind) {
      case 'event':
        add(s, s.text ? `${s.title} - ${s.text}` : s.title, dateWords(s.date).join(' '))
        break
      case 'staff':
      case 'board':
      case 'achiever':
      case 'alumni':
        add(s, s.text ? `${s.title} - ${s.text}` : s.title)
        break
      case 'document':
        if (s.readable) for (const p of pdfPassages(s.text)) add(s, p)
        else add(s, s.title)
        break
      case 'results':
        add(s, s.title) // title only - individual results are not read out
        break
      default: {
        // news, posts, voices, clubs, activities, programmes: lead passage + sentence windows.
        // Programme-type items are also found by the page they live on (e.g. "inspiration night").
        const pageWords = s.kind === 'programme' || s.kind === 'activity' ? (s.url ?? '').replace(/[/-]+/g, ' ') : ''
        const lead = s.text.length > 380 ? s.text.slice(0, 380).replace(/\s+\S*$/, '') + '…' : s.text
        const extra = `${dateWords(s.date).join(' ')} ${pageWords}`
        add(s, lead ? `${s.title}. ${lead}` : s.title, extra)
        if (s.text.length > 380) for (const w of proseWindows(s.text)) add(s, w, extra)
      }
    }
  }

  const df = new Map<string, number>()
  for (const p of passages) for (const t of p.tokens) df.set(t, (df.get(t) ?? 0) + 1)
  const n = Math.max(1, passages.length)
  const idf = new Map<string, number>()
  for (const [t, c] of df) idf.set(t, Math.log(1 + n / c))
  return { sources, passages, idf, maxIdf: Math.log(1 + n) }
}

/**
 * Ranks passages for `query`. `kinds` restricts the search to some source kinds.
 * Words not found anywhere in the index are weighted as maximally rare, so they pull coverage down.
 */
export function search(index: LiveIndex, query: string, kinds?: ReadonlySet<SourceKind>): Hit[] {
  const q = [...new Set(tokens(query))]
  if (q.length === 0) return []
  const weight = (t: string) => (index.idf.get(t) ?? index.maxIdf) * (QUESTION_WORDS.has(t) ? 0.35 : 1)
  const total = q.reduce((a, t) => a + weight(t), 0)

  const hits: Hit[] = []
  for (const p of index.passages) {
    if (kinds && !kinds.has(p.source.kind)) continue
    const matched = q.filter((t) => p.tokens.has(t))
    if (matched.length === 0) continue // a title-only match is never enough on its own
    let score = 0
    for (const t of matched) score += weight(t) * (p.titleTokens.has(t) ? 1.25 : 1)
    let covered = matched.reduce((a, t) => a + weight(t), 0)
    // For PDF lines the document title can still narrow the search ("boarding" -> the boarding fees
    // document), but only on top of a real match in the line itself.
    if (p.source.kind === 'document') {
      for (const t of q) if (!p.tokens.has(t) && p.titleTokens.has(t)) {
        covered += weight(t)
        score += weight(t) * 0.5
        matched.push(t)
      }
    }
    const coverage = Math.min(1, covered / total)
    hits.push({ passage: p, score: (score / (1 + 0.08 * Math.log(1 + p.text.length))) * (p.lead ? 1.05 : 1), coverage, matched })
  }
  hits.sort((a, b) => b.score - a.score)
  return hits
}
