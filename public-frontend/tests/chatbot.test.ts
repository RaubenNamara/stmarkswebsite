// Run with `npm test` (Node's built-in test runner - no extra dependencies).
import { test, describe } from 'node:test'
import assert from 'node:assert/strict'
import { routes } from '../src/router/index.ts'
import { ENTRIES, QUICK_REPLIES } from '../src/chatbot/knowledge.ts'
import { respond, welcomeReply, looksSensitive, EMPTY_CONTEXT, MAX_INPUT_LENGTH, type ChatContext } from '../src/chatbot/engine.ts'
import { classifyLink, sanitizeLinks } from '../src/chatbot/links.ts'
import { tokens, normalize } from '../src/chatbot/text.ts'

const ask = (q: string, ctx: ChatContext = EMPTY_CONTEXT) => respond(q, ctx)

/** Asserts that `q` is answered by entry `id`. */
function expectEntry(q: string, id: string, ctx: ChatContext = EMPTY_CONTEXT) {
  const r = ask(q, ctx)
  assert.equal(r.entryId, id, `"${q}" -> ${r.kind}:${r.entryId ?? '-'} (expected ${id})\n${r.text.slice(0, 120)}`)
  assert.equal(r.kind, 'answer', `"${q}" kind`)
}

describe('normalisation', () => {
  test('case, whitespace and punctuation', () => {
    assert.equal(normalize("  HOW   do I APPLY?!  "), 'how do i apply')
    assert.equal(normalize("St. Mark's"), 'st marks')
  })
  test('plurals, synonyms and misspellings fold together', () => {
    assert.deepEqual(tokens('school fees'), tokens('tuition'))
    assert.deepEqual(tokens('admision'), tokens('admissions'))
    assert.deepEqual(tokens('How much'), tokens('fees'))
    assert.deepEqual(tokens('e-learning'), tokens('elearning'))
  })
})

describe('knowledge base integrity', () => {
  test('ids are unique and related/next ids exist', () => {
    const ids = ENTRIES.map((e) => e.id)
    assert.equal(new Set(ids).size, ids.length)
    for (const e of ENTRIES) {
      for (const r of e.related ?? []) assert.ok(ids.includes(r), `${e.id} -> related ${r}`)
      if (e.next) assert.ok(ids.includes(e.next.entryId), `${e.id} -> next ${e.next.entryId}`)
      assert.ok(e.questions.length >= 2, `${e.id} needs example questions`)
      assert.ok(e.answer.trim().length > 0)
    }
  })

  test('every link is approved, and every internal link is a real route', () => {
    const routePaths = new Set(routes.map((r) => r.path).filter((p) => !p.includes(':')))
    for (const e of ENTRIES) {
      for (const link of e.links ?? []) {
        const kind = classifyLink(link.url)
        assert.ok(kind, `${e.id}: invalid link ${link.url}`)
        if (kind === 'internal') {
          const path = link.url.split(/[?#]/)[0]
          assert.ok(routePaths.has(path), `${e.id}: ${path} is not a route`)
        }
      }
    }
  })

  test('every example question is answered by its own entry', () => {
    const failures: string[] = []
    for (const e of ENTRIES) {
      for (const q of e.questions) {
        const r = ask(q)
        if (r.entryId !== e.id || r.kind !== 'answer') failures.push(`[${e.id}] "${q}" -> ${r.kind}:${r.entryId ?? '-'}`)
      }
    }
    assert.deepEqual(failures, [])
  })

  test('quick replies each lead to a real answer', () => {
    for (const q of QUICK_REPLIES) {
      const r = ask(q.query)
      assert.equal(r.kind, 'answer', q.label)
    }
    assert.equal(QUICK_REPLIES.length, 8)
  })

  test('suggestion buttons always resolve to their own entry', () => {
    for (const e of ENTRIES) {
      for (const s of respond(e.questions[0]).suggestions) {
        if (s.query === 'yes') continue
        assert.equal(ask(s.query).kind, 'answer', `${e.id} chip "${s.label}"`)
      }
    }
  })

  test('answers contain no HTML and no invented placeholders', () => {
    for (const e of ENTRIES) {
      assert.ok(!/[<>]/.test(e.answer), `${e.id} contains markup`)
      assert.ok(!/TODO|lorem|xxx/i.test(e.answer), `${e.id} has placeholder text`)
    }
  })
})

describe('small talk', () => {
  test('greetings', () => {
    for (const g of ['hi', 'Hello!', 'hey there', 'Good morning', 'good evening', 'oli otya', 'Hello, how are you?']) {
      assert.equal(ask(g).kind, 'greeting', g)
    }
  })
  test('thanks, goodbye, help, identity', () => {
    assert.equal(ask('thanks').kind, 'thanks')
    assert.equal(ask('Thank you very much!').kind, 'thanks')
    assert.equal(ask('ok thanks').kind, 'thanks')
    assert.equal(ask('weebale').kind, 'thanks')
    assert.equal(ask('bye').kind, 'goodbye')
    assert.equal(ask("that's all").kind, 'goodbye')
    assert.equal(ask('help').kind, 'help')
    assert.equal(ask('what can you do?').kind, 'help')
    assert.equal(ask('are you a bot?').kind, 'identity')
    assert.match(ask('are you ChatGPT?').text, /not powered by an AI/i)
  })
  test('a greeting or thanks in front of a question does not swallow the question', () => {
    expectEntry('Hello, what are the school fees?', 'fees')
    expectEntry('thanks, where is the school located?', 'location')
  })
  test('yes/no without a pending offer are handled politely', () => {
    assert.equal(ask('yes').kind, 'ack')
    assert.equal(ask('no thanks').kind, 'ack')
  })
  test('empty input shows help instead of failing', () => {
    assert.equal(ask('   ').kind, 'help')
  })
})

describe('each supported category', () => {
  const cases: Array<[string, string]> = [
    ['Tell me about the school', 'about-school'],
    ['When was St Mark\'s founded', 'about-school'],
    ['is it a boarding school', 'boarding'],
    ['what is your mission', 'mission-vision'],
    ['what is the motto', 'motto-values'],
    ['who is the head teacher', 'leadership'],
    ['how do I apply for admission', 'admissions-apply'],
    ['what are the admission requirements', 'admissions-requirements'],
    ['school fees', 'fees'],
    ['how do I pay fees', 'fees-payment'],
    ['what is the school phone number', 'contact'],
    ['what is your email address', 'contact'],
    ['which office should I contact', 'parents-offices'],
    ['what are the office hours', 'office-hours'],
    ['where is the school', 'location'],
    ['directions to the school', 'location'],
    ['what subjects do you teach', 'subjects'],
    ['A level combinations', 'subjects'],
    ['academic programmes', 'academics-overview'],
    ['UNEB results', 'uneb-results'],
    ['what are the term dates', 'calendar'],
    ['do you have sports', 'co-curricular'],
    ['what clubs are there', 'clubs'],
    ['empowerment programmes', 'empowerment'],
    ['school uniform', 'uniform'],
    ['personal needs list', 'personal-needs'],
    ['what are the school rules', 'rules'],
    ['chaplaincy', 'chaplaincy'],
    ['what facilities do you have', 'facilities'],
    ['latest news', 'news-events'],
    ['what is eSpace', 'elearning-espace'],
    ['I forgot my eSpace password', 'espace-login-help'],
    ['job vacancies', 'careers'],
    ['do you have a facebook page', 'social-media'],
    ['faq', 'faq'],
  ]
  for (const [q, id] of cases) test(`"${q}" -> ${id}`, () => expectEntry(q, id))
})

describe('alternative phrasings land on the same topic', () => {
  const admissions = [
    'How do I join the school?',
    'How can I apply?',
    "I want my child to study at St. Mark's.",
    'how do i enrol my son',
    'admision',
    'can I register my daughter',
  ]
  for (const q of admissions) test(`admissions: ${q}`, () => expectEntry(q, 'admissions-apply'))

  test('requirements phrasings', () => {
    for (const q of ['What are the admission requirements?', 'what documents are needed to apply', 'entry requirements', 'what is required for admission']) {
      expectEntry(q, 'admissions-requirements')
    }
  })
  test('fee phrasings', () => {
    for (const q of ['how much is tuition', 'what do you charge per term', 'fees?', 'school fee structure']) {
      expectEntry(q, 'fees')
    }
  })
  test('"how much do I pay" stays within the fees topic', () => {
    assert.equal(ask('how much do I pay').context.lastTopic, 'fees')
  })
  test('contact phrasings', () => {
    for (const q of ['phone number', 'how can I reach you', 'give me your email', 'contct the school', 'whatsapp number']) {
      expectEntry(q, 'contact')
    }
  })
  test('location phrasings', () => {
    for (const q of ['where are you located', 'how do I get there', 'school address', 'how far is it from Kampala', 'where can I find the school']) {
      expectEntry(q, 'location')
    }
  })
})

describe('honesty: nothing is invented', () => {
  test('unrelated questions get the fallback, not a school answer', () => {
    for (const q of ['what is the weather today', 'who won the football world cup', 'tell me a joke', 'what is the capital of France', 'asdfghjkl', 'can you write my homework']) {
      const r = ask(q)
      assert.ok(r.kind === 'fallback' || r.kind === 'clarify', `"${q}" -> ${r.kind}:${r.entryId ?? '-'}`)
      assert.equal(r.entryId, undefined)
    }
  })
  test('fallback names the school office and gives contact links', () => {
    const r = ask('what is the weather today')
    assert.ok(r.links.some((l) => l.url.startsWith('tel:')))
    assert.match(r.text, /don't have that information|isn't available/i)
  })
  test('fallback wording changes on repeat and escalates to the office', () => {
    let ctx = EMPTY_CONTEXT
    const texts = new Set<string>()
    for (let i = 0; i < 3; i++) {
      const r = ask('qwertyuiop zxcvbnm', ctx)
      texts.add(r.text)
      ctx = r.context
    }
    assert.equal(texts.size, 3)
    assert.equal(ctx.fallbackCount, 3)
  })
  test('unpublished details never produce an invented number or date', () => {
    const fees = ask('what are the school fees').text
    assert.ok(!/UGX|Shs|USD|\d{3},\d{3}/.test(fees), 'fee answer must not quote amounts')
    assert.match(ask('what are the admission requirements').text, /aren't published/)
    assert.match(ask('do you provide school transport').text, /isn't available/)
  })
  test('every phone number or email in an answer is a verified one', () => {
    const allowedPhones = ['+256 393 103 418', '+256 775 831 844', '+256 750 330 158', '+256 772 996 680']
    const allowedEmails = ['director@stmark.sc.ug', 'info@stmark.sc.ug']
    for (const e of ENTRIES) {
      const text = respond(e.questions[0]).text
      for (const m of text.match(/\+256[\d ]{9,}\d/g) ?? []) assert.ok(allowedPhones.includes(m), `${e.id}: unknown phone ${m}`)
      for (const m of text.match(/[\w.+-]+@[\w-]+\.[\w.]+/g) ?? []) assert.ok(allowedEmails.includes(m), `${e.id}: unknown email ${m}`)
    }
  })
})

describe('ambiguity and low confidence', () => {
  test('a question touching two topics asks which was meant', () => {
    const r = ask('fees and location')
    assert.equal(r.kind, 'clarify')
    assert.ok(r.suggestions.length >= 2)
  })
  test('clarification buttons lead to real answers', () => {
    for (const s of ask('fees and location').suggestions) assert.equal(ask(s.query).kind, 'answer')
  })
  test('a long question of which only one word matches is not answered confidently', () => {
    const r = ask('my cousin lives near a river and wants a new bicycle for fees')
    assert.notEqual(r.kind, 'answer')
  })
})

describe('conversation context', () => {
  test('answers do not end with an extra follow-up question', () => {
    for (const q of ['what academic programmes do you offer', 'how do I apply for admission', 'where is the school']) {
      const r = ask(q)
      assert.ok(!r.text.trim().endsWith('?'), q)
      assert.equal(r.context.pendingEntryId, undefined)
    }
  })
  test('"no" clears the offer', () => {
    const first = ask('how do I apply for admission')
    assert.equal(ask('no', first.context).context.pendingEntryId, undefined)
  })
  test('"tell me more" offers related topics of the last answer', () => {
    const first = ask('where is the school')
    const more = ask('tell me more', first.context)
    assert.ok(more.suggestions.length > 0)
    assert.equal(more.entryId, 'location')
  })
  test('"more" without a previous answer shows help', () => {
    assert.equal(ask('tell me more').kind, 'help')
  })
  test('follow-up topics stay in the conversation', () => {
    const first = ask('how do I apply for admission')
    expectEntry('and the requirements?', 'admissions-requirements', first.context)
    expectEntry('and how much?', 'fees', first.context)
  })
  test('answers update the context', () => {
    const r = ask('school uniform')
    assert.equal(r.context.lastEntryId, 'uniform')
    assert.equal(r.context.lastTopic, 'life')
    assert.equal(r.context.fallbackCount, 0)
  })
})

describe('privacy and safety', () => {
  test('credentials, card numbers and long numbers are flagged and not processed', () => {
    for (const q of ['my password is hunter2', 'pin 4821', 'card 4111 1111 1111 1111', 'my number is 0775831844']) {
      const r = ask(q)
      assert.equal(r.kind, 'privacy', q)
      assert.equal(r.sensitive, true)
    }
  })
  test('asking ABOUT passwords is not treated as sharing one', () => {
    assert.equal(looksSensitive('I forgot my password'), false)
    assert.equal(looksSensitive('what is the school phone number'), false)
  })
  test('HTML in a question is just text and never appears in a link', () => {
    const r = ask('<img src=x onerror=alert(1)> fees')
    assert.ok(['answer', 'clarify', 'fallback'].includes(r.kind))
    assert.ok(!r.text.includes('<img'), 'user markup must never be echoed into a reply')
    for (const l of r.links) assert.ok(classifyLink(l.url))
  })
  test('very long input is truncated', () => {
    const r = ask('fees '.repeat(500))
    assert.equal(r.entryId, 'fees')
    assert.ok(MAX_INPUT_LENGTH <= 300)
  })
})

describe('link validation', () => {
  test('approved links', () => {
    assert.equal(classifyLink('/contact'), 'internal')
    assert.equal(classifyLink('/academics/school-calendar#x'), 'internal')
    assert.equal(classifyLink('tel:+256775831844'), 'tel')
    assert.equal(classifyLink('mailto:info@stmark.sc.ug'), 'mail')
    assert.equal(classifyLink('https://espace.stmark.sc.ug/'), 'external')
    assert.equal(classifyLink('https://www.google.com/maps?q=0.28,32.47'), 'external')
    assert.equal(classifyLink('https://wa.me/256775831844'), 'external')
  })
  test('rejected links', () => {
    for (const bad of ['javascript:alert(1)', '//evil.com', 'http://espace.stmark.sc.ug/', 'https://evil.com/', 'https://stmark.sc.ug.evil.com/', 'https://user:pw@stmark.sc.ug/', 'data:text/html,hi', '/a b', '', 'ftp://stmark.sc.ug', 'https://www.google.com/search?q=x', 'tel:abc', 'mailto:not-an-email']) {
      assert.equal(classifyLink(bad), null, bad)
    }
  })
  test('sanitizeLinks drops bad entries', () => {
    const out = sanitizeLinks([{ label: 'ok', url: '/contact' }, { label: 'bad', url: 'javascript:alert(1)' }, { label: '', url: '/contact' }])
    assert.deepEqual(out, [{ label: 'ok', url: '/contact' }])
  })
})

describe('welcome', () => {
  test('welcome message introduces the assistant honestly and offers quick replies', () => {
    const w = welcomeReply()
    assert.match(w.text, /virtual assistant/)
    assert.equal(w.suggestions.length, QUICK_REPLIES.length)
  })
})
