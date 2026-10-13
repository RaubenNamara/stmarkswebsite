// Spelling tolerance: a misspelt question must get exactly the reply the correctly spelled one gets.
import { test, describe } from 'node:test'
import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import { respond, spellCheck, EMPTY_CONTEXT } from '../src/chatbot/engine.ts'
import { parseSources } from '../src/chatbot/live.ts'
import { buildIndex } from '../src/chatbot/liveIndex.ts'
import { distance } from '../src/chatbot/spell.ts'

const raw = JSON.parse(readFileSync(new URL('./fixtures/knowledge.json', import.meta.url), 'utf8'))
const live = buildIndex(parseSources(raw, '/stmarkswebsite/'))
const NOW = new Date(2026, 9, 13)
const ask = (q: string, withLive = true) => respond(q, EMPTY_CONTEXT, withLive ? live : null, NOW)

describe('edit distance', () => {
  test('insert, delete, replace and swapped letters each count as one', () => {
    assert.equal(distance('admission', 'admision', 3), 1)
    assert.equal(distance('admission', 'adimssion', 3), 1)
    assert.equal(distance('school', 'scool', 3), 1)
    assert.equal(distance('chemistry', 'chemestry', 3), 1)
    assert.equal(distance('abc', 'xyz', 1), 2) // gives up early: "more than max"
  })
})

describe('typos get the same reply as the correct spelling', () => {
  const pairs: Array<[string, string]> = [
    ['how do i aply for admision', 'how do i apply for admission'],
    ['adimssion requirments', 'admission requirements'],
    ['wat are the scool fes', 'what are the school fees'],
    ['tuishun fees for boardin', 'tuition fees for boarding'],
    ['hw much is tution', 'how much is tuition'],
    ['wher is teh schol locatd', 'where is the school located'],
    ['direcions to the scool', 'directions to the school'],
    ['contakt number', 'contact number'],
    ['telefone number of the school', 'telephone number of the school'],
    ['emial adress', 'email address'],
    ['whatsap number', 'whatsapp number'],
    ['wat subjets do you teach', 'what subjects do you teach'],
    ['curiculum', 'curriculum'],
    ['schol uniforn', 'school uniform'],
    ['school calender', 'school calendar'],
    ['chaplancy', 'chaplaincy'],
    ['hedteacher', 'headteacher'],
    ['is it a bording scool', 'is it a boarding school'],
    ['elerning', 'elearning'],
    ['forgot my pasword', 'forgot my password'],
    ['teachers in chemestry', 'teachers in chemistry'],
    ['biolgy techers', 'biology teachers'],
    ['who teaches mathmatics', 'who teaches mathematics'],
    ['phisics teachers', 'physics teachers'],
    ['kiswahli teachers', 'kiswahili teachers'],
    ['enterpreneurship teachers', 'entrepreneurship teachers'],
    ['personl requirments for boardin students', 'personal requirements for boarding students'],
    ['wat shud day scholars brng', 'what should day scholars bring'],
    ['upcomin evnts', 'upcoming events'],
    ['evnts in novembr', 'events in november'],
    ['wen does term 3 begn', 'when does term 3 begin'],
    ['helo', 'hello'],
    ['gud mornin', 'good morning'],
    ['thnks', 'thanks'],
    ['gudbye', 'goodbye'],
    ['whats ur name', 'whats your name'],
    ['facbook page', 'facebook page'],
    ['mosqito net', 'mosquito net'],
    ['is a laptp required', 'is a laptop required'],
    ['whats the schol mottoo', 'whats the school motto'],
    ['who is kisekka jaems', 'who is kisekka james'],
  ]
  for (const [typo, ok] of pairs) {
    test(`"${typo}"`, () => {
      const a = ask(typo)
      const b = ask(ok)
      assert.equal(a.text, b.text)
      assert.equal(a.kind, b.kind)
    })
  }
  test('also works without live data (built-in answers only)', () => {
    for (const [typo, ok] of [['wher is teh schol locatd', 'where is the school located'], ['adimssion requirments', 'admission requirements'], ['school uniforn', 'school uniform']]) {
      assert.equal(ask(typo, false).text, ask(ok, false).text, typo)
    }
  })
})

describe('words that are not school topics are left alone', () => {
  test('everyday words are never turned into school words', () => {
    for (const q of ['what is the weather', 'tell me about cooking', 'can you write my homework', 'who won the world cup', 'what is the price of bitcoin', 'tell me a joke']) {
      assert.deepEqual(spellCheck(q, live).changes.filter(([from]) => ['weather', 'cooking', 'write', 'bitcoin', 'joke', 'cup', 'world'].includes(from)), [], q)
      assert.notEqual(ask(q).live, 'fees', q)
      assert.ok(['fallback', 'clarify'].includes(ask(q).kind) || ask(q).live === undefined, q)
    }
  })
  test('gibberish still gets the honest fallback', () => {
    for (const q of ['asdfgh', 'qwertyuiop', 'zzzz xxxx']) assert.equal(ask(q).kind, 'fallback', q)
  })
  test('short real words are not "corrected" into other words', () => {
    for (const [word, wrong] of [['cooking', 'cool'], ['write', 'white'], ['cart', 'card']]) {
      assert.ok(!spellCheck(word, live).changes.some(([, to]) => to === wrong), word)
    }
  })
  test('correct spelling is untouched', () => {
    assert.deepEqual(spellCheck('what are the school fees', live).changes, [])
    assert.deepEqual(spellCheck('teachers in ICT', live).changes, [])
  })
})
