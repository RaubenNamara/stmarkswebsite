// Tests for the "live" layer: answers built from database content and uploaded-PDF text.
// tests/fixtures/knowledge.json is a real snapshot of /api/public/chatbot-knowledge.
import { test, describe } from 'node:test'
import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import { respond, EMPTY_CONTEXT, type BotReply } from '../src/chatbot/engine.ts'
import { ENTRIES } from '../src/chatbot/knowledge.ts'
import { classifyLink } from '../src/chatbot/links.ts'
import { parseSources, type LiveSource } from '../src/chatbot/live.ts'
import { buildIndex, pdfPassages, search } from '../src/chatbot/liveIndex.ts'
import { parseFeeDoc, formatDate } from '../src/chatbot/liveAnswer.ts'
import { tokens } from '../src/chatbot/text.ts'

const BASE = '/stmarkswebsite/'
const raw = JSON.parse(readFileSync(new URL('./fixtures/knowledge.json', import.meta.url), 'utf8'))
const sources = parseSources(raw, BASE)
const live = buildIndex(sources)
const NOW = new Date(2026, 9, 13) // 13 Oct 2026

const ask = (q: string, ctx = EMPTY_CONTEXT): BotReply => respond(q, ctx, live, NOW)
const allText = JSON.stringify(raw)

describe('feed validation', () => {
  test('real fixture parses, PDFs are readable, results PDF is withheld', () => {
    assert.ok(sources.length > 100)
    const docs = sources.filter((s) => s.kind === 'document')
    assert.equal(docs.length, 3)
    assert.ok(docs.every((d) => d.readable && d.text.length > 500 && d.fileUrl?.startsWith(`${BASE}api/public/fee-structures/`)))
    const results = sources.find((s) => s.kind === 'results')
    assert.ok(results && results.text === '' && !results.readable)
  })
  test('hostile or malformed entries are dropped or neutralised', () => {
    const out = parseSources(
      [
        { kind: 'evil', title: 'x', text: 'y' },
        { kind: 'news', title: '', text: 'no title' },
        { kind: 'news', title: 'ok', text: 't', url: 'javascript:alert(1)' },
        { kind: 'news', title: 'ok2', text: 't', url: '//evil.com' },
        { kind: 'document', title: 'doc', text: 't', endpoint: '/../../etc/passwd', file_url: 'https://evil.com/a.pdf' },
        { kind: 'document', title: 'doc2', text: 't', endpoint: '/fee-structures/9/pdf' },
        'not an object',
        null,
      ],
      BASE,
    )
    assert.deepEqual(out.map((s) => s.title), ['ok', 'ok2', 'doc', 'doc2'])
    assert.equal(out[0].url, undefined)
    assert.equal(out[1].url, undefined)
    assert.equal(out[2].fileUrl, undefined)
    assert.equal(out[3].fileUrl, '/stmarkswebsite/api/public/fee-structures/9/pdf')
    assert.deepEqual(parseSources('nope'), [])
    assert.deepEqual(parseSources(undefined), [])
  })
  test('links to PDFs and pages are approved link kinds', () => {
    assert.equal(classifyLink('/stmarkswebsite/api/public/fee-structures/15/pdf'), 'file')
    assert.equal(classifyLink('/api/public/performances/36/pdf'), 'file')
    assert.equal(classifyLink('/uploads/fee_structures/a_b.pdf'), 'file')
    assert.equal(classifyLink('/api/public/users'), 'internal') // not a document endpoint: an ordinary (and unrouted) path
    assert.equal(classifyLink('/stmarkswebsite/api/public/fee-structures/x/pdf'), 'internal')
  })
})

describe('PDF text -> passages', () => {
  const doc = (title: string) => sources.find((s) => s.title.startsWith(title)) as LiveSource
  test('fee rows, numbered items and wrapped lines stay together', () => {
    const p = pdfPassages(doc('Boarding').text)
    assert.ok(p.includes('Tuition fee per term: 1,700,000 | 1,800,000'))
    assert.ok(p.includes('5. Fifteen (15) black books (School-branded books available at Shs10,000 each).'))
    assert.ok(p.some((x) => x.includes('out-of-class wear')), 'hyphenated line break is rejoined')
  })
  test('programme rows keep their continuation lines, empty days are dropped', () => {
    const p = pdfPassages(doc('Term one').text)
    assert.ok(p.some((x) => /^Tue 10-Feb-26 Reporting Day/.test(x)))
    assert.ok(!p.some((x) => /^Sun 8-Feb-26$/.test(x)))
  })
  test('fee table parsing maps O-level / A-level columns', () => {
    const f = parseFeeDoc(doc('Boarding'))
    assert.deepEqual(f?.columns, ['O-level', 'A-level'])
    assert.deepEqual(f?.rows.find((r) => /tuition/i.test(r.label))?.values, ['1,700,000', '1,800,000'])
    const day = parseFeeDoc(doc('Day Scholars'))
    assert.deepEqual(day?.columns, [])
    assert.deepEqual(day?.rows.find((r) => /tuition/i.test(r.label))?.values, ['1,100,000'])
    assert.equal(parseFeeDoc(doc('Term one')), null, 'a programme PDF is not a fee table')
  })
})

describe('fees come from the uploaded PDFs, exactly', () => {
  test('overview lists both documents with the printed amounts', () => {
    const r = ask('what are the school fees')
    assert.equal(r.live, 'fees')
    for (const n of ['100,000', '460,000', '1,100,000', '640,000', '1,700,000', '1,800,000']) assert.ok(r.text.includes(n), n)
    assert.ok(r.links.some((l) => classifyLink(l.url) === 'file'), 'links to the PDF')
  })
  test('boarding only / level only / single item', () => {
    const t = ask('how much is tuition for boarding students').text
    assert.ok(t.includes('1,700,000') && t.includes('1,800,000') && !t.includes('1,100,000'))
    const a = ask('tuition for a level boarders').text
    assert.ok(a.includes('A-level 1,800,000') && !a.includes('1,700,000'))
    const o = ask('o level boarding tuition fee').text
    assert.ok(o.includes('O-level 1,700,000') && !o.includes('1,800,000'))
    const d = ask('fees for day scholars').text
    assert.ok(d.includes('1,100,000') && d.includes('460,000') && !d.includes('1,700,000'))
    const adm = ask('how much is the admission fee').text
    assert.ok(adm.includes('100,000') && !adm.includes('Tuition'))
  })
  test('what the fees cover is quoted from the document', () => {
    const r = ask('what do functional fees cover')
    assert.match(r.text, /2 pairs of school uniform/)
    assert.match(r.text, /Boarding fees structure 2026/)
    assert.match(r.text, /Day Scholars Fee Structure 2026/)
  })
  test('never calculates totals or invents currency', () => {
    const r = ask('what is the total fees per term for a level boarders').text
    assert.ok(!/UGX|USD|total of|\b3,5\d\d,\d{3}\b|\b2,\d{3},\d{3}\b/.test(r.replace(/1,800,000/g, '')), r)
  })
  test('built-in fallback when the PDFs are unavailable', () => {
    const r = respond('what are the school fees', EMPTY_CONTEXT, null, NOW)
    assert.equal(r.entryId, 'fees')
    assert.equal(r.live, undefined)
    assert.ok(!/\d{3},\d{3}/.test(r.text))
  })
  test('unrelated words are not forced into the fee tables', () => {
    assert.notEqual(ask('how much is a bicycle').live, 'fees')
  })
})

describe('personal requirements come from the PDFs', () => {
  test('boarding list and day-scholar list differ', () => {
    const b = ask('what personal requirements do boarders bring')
    assert.equal(b.live, 'requirements')
    assert.match(b.text, /Mosquito net/)
    assert.match(b.text, /Medium size suitcase/)
    const d = ask('what should day scholars bring')
    assert.match(d.text, /canvas shoes for sports/)
    assert.ok(!/Mosquito net/.test(d.text))
  })
  test('asks which list when it is not clear', () => {
    const r = ask('what are the personal requirements')
    assert.equal(r.kind, 'clarify')
    assert.equal(r.suggestions.length, 2)
    assert.equal(ask(r.suggestions[0].query).live, 'requirements')
    assert.equal(ask(r.suggestions[1].query).live, 'requirements')
  })
  test('questions about one item find that exact line', () => {
    assert.match(ask('do students need a mosquito net').text, /23\. Mosquito net\./)
    const laptop = ask('is a laptop required').text
    assert.match(laptop, /Personal laptops are encouraged/)
    assert.match(laptop, /personal laptop is highly recommended/)
    assert.match(ask('are smart watches allowed').text, /Smart watches are not allowed/)
  })
})

describe('calendar answers come from the database and the term programme', () => {
  test('upcoming events are future, not-completed, in date order', () => {
    const r = ask('upcoming events')
    assert.equal(r.live, 'calendar')
    const dates = [...r.text.matchAll(/(\d{1,2}) (October|November) 2026/g)].map((m) => `${m[2]}-${m[1].padStart(2, '0')}`)
    assert.ok(dates.length >= 3)
    assert.ok(!/Sports day|S\.4 Dinner/.test(r.text), 'past events are not listed as upcoming')
    assert.ok(r.text.includes('Beginning-of-Term III'))
  })
  test('events in a month', () => {
    const r = ask('what events are in november')
    assert.match(r.text, /12 November 2026 - Exams: Beginning of UCE Examinations/)
    assert.equal(ask('events in december').text, 'No events are listed for December.')
  })
  test('specific lookups', () => {
    assert.match(ask('when is the S6 geography trip').text, /Sat 17 October 2026 - S6 Geog Trip: Kisoro/)
    assert.match(ask('when does term 3 begin').text, /Thu 22 October 2026 - Beginning-of-Term III/)
    assert.match(ask('when do exams start').text, /12 November 2026/)
    const reporting = ask('when do S1 students report').text
    assert.match(reporting, /Sun 22-Feb-26 Reporting of S1 Students/)
    assert.match(reporting, /marked as a draft/)
  })
  test('lists every matching event, labelled when the date has passed', () => {
    const r = ask('when are the visitation days').text
    assert.match(r, /2 October 2026 - vistation: s3, s5 \(date has passed\)/)
    assert.match(r, /28 October 2026/)
  })
  test('weekday names print correctly', () => {
    assert.equal(formatDate('2026-10-22'), 'Thu 22 October 2026')
    assert.equal(formatDate('2025-12-07'), 'Sun 7 December 2025')
  })
})

describe('people and programmes', () => {
  test('leadership answers only the role asked about', () => {
    const head = ask('who is the head teacher').text
    assert.equal(head, 'Wabwere Joseph')
    assert.equal(ask('who is the chairman of the board').text, 'Rtn. Geofrey kulubya')
    assert.equal(ask('who is the director').text, 'Omuk. DG Daniel Mukasa Ddamulira')
    assert.match(ask('who runs the school').text, /• Director: .*\n• Head Teacher: /)
  })
  test('staff, clubs and programmes', () => {
    assert.equal(ask('who is the head of security').text, 'Security:\n• Ariaka Fred (Head of Department)')
    const club = ask('tell me about the science club')
    assert.equal(club.live, 'passage')
    assert.match(club.text, /^Science Club\nThe Science Club of our school/)
    assert.ok(club.links.some((l) => l.url === '/clubs/science-club'))
    assert.match(ask('who spoke at inspiration night').text, /Speaker: Rtn Mpiima/)
  })
  test('high achievers', () => {
    assert.match(ask('what did kakembo get').text, /KAKEMBO GERALD KIZZA - UACE 2025\. 20 POINTS/)
  })
})

describe('staff by department', () => {
  const full = buildIndex(parseSources([
    { kind: 'staff', id: 'staff-1', title: 'Kisekka James', text: 'ICT (Head of Department)', url: '/staff' },
    { kind: 'staff', id: 'staff-2', title: 'Babirye Rose', text: 'ICT (Teaching Staff)', url: '/staff' },
    { kind: 'staff', id: 'staff-3', title: 'Ssebalamu Tom', text: 'ICT (Teaching Staff)', url: '/staff' },
    { kind: 'staff', id: 'staff-4', title: 'Sseguya Humphrey', text: 'Chemistry (Teaching Staff)', url: '/staff' },
    { kind: 'staff', id: 'staff-5', title: 'Wabwere Joseph', text: 'Head Teacher (Administration)', url: '/staff' },
  ], BASE))
  const q = (s: string) => respond(s, EMPTY_CONTEXT, full, NOW)
  test('lists the department, head first, however the question is phrased', () => {
    for (const s of ['teachers in ICT', 'ICT teachers', 'imean teachers in ICT', 'who teaches ICT', 'staff in the ICT department']) {
      const r = q(s)
      assert.equal(r.live, 'staff', s)
      assert.equal(r.text, 'ICT:\n• Kisekka James (Head of Department)\n• Babirye Rose\n• Ssebalamu Tom', s)
    }
  })
  test('head of a department, and other departments', () => {
    assert.equal(q('who is the head of ICT').text, 'ICT:\n• Kisekka James (Head of Department)')
    assert.equal(q('teachers of chemistry').text, 'Chemistry:\n• Sseguya Humphrey')
  })
  test('the Head Teacher question is not mistaken for a department', () => {
    assert.equal(q('who is the head teacher').text, 'Wabwere Joseph')
  })
  test('a department with no staff, or a general "list teachers", is not invented', () => {
    assert.notEqual(q('teachers in astronomy').live, 'staff')
    assert.equal(q('list teachers').entryId, 'staff-list')
  })
})

describe('subject short names and full names mean the same', () => {
  const groups: Array<[string, string[]]> = [
    ['Biology', ['bio', 'Biology', 'BIO']], ['Chemistry', ['chem', 'chemistry']], ['Physics', ['phy', 'phys', 'physics']],
    ['Mathematics', ['math', 'maths', 'Mathematics']], ['Geography', ['geog', 'geo', 'geography']], ['History', ['hist', 'history']],
    ['Economics', ['eco', 'econ', 'economics']], ['Entrepreneurship', ['ent', 'entrepreneurship']], ['Luganda', ['lug', 'luganda']],
    ['Kiswahili', ['kisw', 'swahili', 'kiswahili']], ['Agriculture', ['agric', 'agriculture']], ['English', ['eng', 'english']],
    ['CRE', ['cre', 'divinity', 'christian religious education']], ['ICT', ['ICT', 'computer', 'IT']],
    ['Physical Education', ['PE', 'physical education']], ['General Paper', ['GP', 'general paper']],
  ]
  const index = buildIndex(parseSources(
    groups.flatMap(([dept], i) => [
      { kind: 'staff', id: `h${i}`, title: `Head ${dept}`, text: `${dept} (Head of Department)`, url: '/staff' },
      { kind: 'staff', id: `t${i}`, title: `Teacher ${dept}`, text: `${dept} (Teaching Staff)`, url: '/staff' },
    ]),
    BASE,
  ))
  const names = (s: string) => {
    const r = respond(s, EMPTY_CONTEXT, index, NOW)
    return r.live === 'staff' ? r.text.split('\n').slice(1).join('|') : `NOT-STAFF:${r.kind}`
  }
  for (const [dept, aliases] of groups) {
    test(`${dept}: ${aliases.join(' = ')}`, () => {
      const expected = `• Head ${dept} (Head of Department)|• Teacher ${dept}`
      for (const a of [dept, ...aliases]) {
        for (const phrasing of [`teachers in ${a}`, `${a} teachers`, `who teaches ${a}`, `teachers for ${a}`, `${a} department staff`]) {
          assert.equal(names(phrasing), expected, phrasing)
        }
        assert.equal(names(`who is the head of ${a}`), `• Head ${dept} (Head of Department)`, `head of ${a}`)
      }
    })
  }
  test('normalisation: stemming never splits a short name from its full name', () => {
    for (const [a, b] of [['maths', 'mathematics'], ['phy', 'physics'], ['eco', 'economics'], ['bio', 'biology'], ['divinity', 'CRE']]) {
      assert.deepEqual(tokens(a), tokens(b), `${a} vs ${b}`)
    }
  })
  test('the pronoun "it" is not mistaken for the IT department', () => {
    assert.notEqual(names('is it open on saturday'), `• Head ICT (Head of Department)|• Teacher ICT`)
    assert.ok(!names('what is in it').startsWith('•'))
  })
})

describe('honesty with live data', () => {
  test('unknown topics still get the fallback', () => {
    for (const q of ['what is the weather', 'who won the world cup', 'tell me about the rugby team', 'what is the price of bitcoin', 'asdfgh']) {
      const r = ask(q)
      assert.ok(['fallback', 'clarify'].includes(r.kind), `${q} -> ${r.kind}`)
      assert.equal(r.live, undefined, q)
    }
  })
  test('individual UACE results are not read out from the results PDF', () => {
    const r = ask('what did Muwanguzi Terry score in UACE results 2025')
    assert.ok(!/U1664\/\d+/.test(r.text), 'no index numbers')
    assert.ok(!allText.includes('U1664/501'), 'the withheld PDF text is not even in the feed')
  })
  test('private details are not in the feed', () => {
    assert.ok(!/@|\+256|password|telephone/i.test(JSON.stringify(sources.filter((s) => s.kind === 'staff' || s.kind === 'board'))))
  })
})

describe('precision invariants (nothing invented)', () => {
  const questions = [
    'what are the school fees', 'fees for day scholars', 'boarding tuition', 'how much is the admission fee', 'what do functional fees cover',
    'personal requirements for boarding students', 'personal requirements for day scholars', 'upcoming events', 'events in november',
    'when does term 3 begin', 'when is reporting day', 'when are the visitation days', 'who is the head teacher', 'who is the chairman of the board',
    'who is the head of security', 'tell me about the science club', 'who got 20 points in uace', 'is a laptop required',
  ]
  const norm = (s: string) => s.replace(/[\s |]+/g, ' ')
  test('every amount quoted exists in the source documents', () => {
    const docText = norm(sources.filter((s) => s.kind === 'document').map((s) => s.text).join(' '))
    for (const q of questions) {
      for (const n of ask(q).text.match(/\d{1,3}(?:,\d{3})+/g) ?? []) assert.ok(docText.includes(n), `${q}: ${n}`)
    }
  })
  test('every full date quoted exists in the calendar data or the PDFs', () => {
    const dates = new Set(sources.filter((s) => s.date).map((s) => formatDate(s.date as string)))
    const docs = norm(sources.filter((s) => s.kind === 'document').map((s) => s.text).join(' '))
    for (const q of questions) {
      for (const m of ask(q).text.matchAll(/(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun) \d{1,2} [A-Z][a-z]+ \d{4}/g)) assert.ok(dates.has(m[0]), `${q}: ${m[0]}`)
      for (const m of ask(q).text.matchAll(/\d{1,2}-[A-Z][a-z]{2}-\d{2}/g)) assert.ok(docs.includes(m[0]), `${q}: ${m[0]}`)
    }
  })
  test('every name or phrase in a quoted answer line is in the source text', () => {
    const everything = norm(JSON.stringify(raw))
    for (const q of ['who is the head of security', 'who got 20 points in uace', 'what did kakembo get']) {
      for (const line of ask(q).text.split('\n').filter((l) => l.startsWith('• '))) {
        const body = norm(line.slice(2)).replace(/ - /g, ' ').trim()
        assert.ok(body.split(' ').every((w) => everything.includes(w)), `${q}: ${line}`)
      }
    }
  })
  test('all links in live answers are approved', () => {
    for (const q of questions) for (const l of ask(q).links) assert.ok(classifyLink(l.url), `${q}: ${l.url}`)
  })
})

describe('built-in answers keep working with live data on', () => {
  test('static topics are not hijacked by loosely related stories', () => {
    for (const [q, id] of [
      ['what is the school motto', 'motto-values'], ['where is the school located', 'location'], ['what is the school phone number', 'contact'],
      ['do you have a chaplaincy', 'chaplaincy'], ['what is the school uniform', 'uniform'], ['how do I apply for admission', 'admissions-apply'],
      ['how do I join the school?', 'admissions-apply'], ['what subjects do you offer', 'subjects'],
    ] as const) {
      assert.equal(ask(q).entryId, id, q)
    }
  })
  test('most example questions still reach their own entry', () => {
    const moved: string[] = []
    for (const e of ENTRIES) for (const q of e.questions) {
      const r = ask(q)
      if (r.entryId !== e.id && !r.live) moved.push(`[${e.id}] ${q} -> ${r.kind}:${r.entryId}`)
    }
    assert.deepEqual(moved, [])
  })
  test('search never throws on odd input', () => {
    for (const q of ['', '   ', '???', '😀', 'a'.repeat(400), '<script>alert(1)</script>', '%%%%']) {
      assert.doesNotThrow(() => respond(q, EMPTY_CONTEXT, live, NOW))
      assert.doesNotThrow(() => search(live, q))
    }
  })
})
