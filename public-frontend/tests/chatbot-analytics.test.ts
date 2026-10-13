// Anonymous chatbot analytics (browser side): what is sent, and what must never be.
import { test, describe, beforeEach } from 'node:test'
import assert from 'node:assert/strict'
import { logExchange, getVisitorId, getConversationId, newConversation } from '../src/chatbot/analytics.ts'

interface Sent { url: string; body: Record<string, unknown> }
const g = globalThis as unknown as Record<string, unknown>
let sent: Sent[] = []

function setup(opts: { dnt?: boolean; path?: string } = {}) {
  sent = []
  g.location = { pathname: opts.path ?? '/stmarkswebsite/contact', href: 'http://x/stmarkswebsite/contact?secret=1' }
  g.window = { doNotTrack: opts.dnt ? '1' : undefined }
  Object.defineProperty(globalThis, 'navigator', { value: { doNotTrack: opts.dnt ? '1' : null }, configurable: true })
  g.fetch = (url: string, init: { body: string }) => {
    sent.push({ url, body: JSON.parse(init.body) })
    return Promise.resolve({ ok: true })
  }
}

describe('what is sent', () => {
  beforeEach(() => setup())

  test('one exchange: ids, question, how it was answered, page without base or query', () => {
    logExchange({ question: 'what are the school fees', kind: 'answer', ref: 'fees', fixed: false, preview: 'Day Scholars Fee Structure 2026' }, '/stmarkswebsite/')
    assert.equal(sent.length, 1)
    assert.equal(sent[0].url, '/stmarkswebsite/api/public/chatbot-log')
    const b = sent[0].body
    assert.match(String(b.visitor), /^[a-f0-9]{24}$/)
    assert.match(String(b.conversation), /^[a-f0-9]{24}$/)
    assert.equal(b.question, 'what are the school fees')
    assert.equal(b.kind, 'answer')
    assert.equal(b.ref, 'fees')
    assert.equal(b.page, '/contact', 'base path and query string are removed')
    assert.equal(b.preview, 'Day Scholars Fee Structure 2026')
  })

  test('private-looking messages are never sent - only a placeholder', () => {
    logExchange({ question: 'my password is hunter2', kind: 'privacy', fixed: false, preview: 'That looks like private information...' }, '/stmarkswebsite/')
    const b = sent[0].body
    assert.equal(b.question, '[private]')
    assert.equal(b.preview, undefined)
    assert.ok(!JSON.stringify(b).includes('hunter2'))
  })

  test('nothing identifying is in the payload', () => {
    logExchange({ question: 'hi', kind: 'greeting', fixed: false, preview: 'Hello' }, '/stmarkswebsite/')
    // (no name, email, phone, IP address or cookie - and `ref` is simply absent when no topic matched)
    assert.deepEqual(Object.keys(sent[0].body).sort(), ['conversation', 'fixed', 'kind', 'page', 'preview', 'question', 'visitor'])
  })

  test('long text is capped', () => {
    logExchange({ question: 'x'.repeat(1000), kind: 'fallback', fixed: false, preview: 'y'.repeat(1000) })
    assert.equal(String(sent[0].body.question).length, 300)
    assert.equal(String(sent[0].body.preview).length, 300)
  })
})

describe('ids', () => {
  beforeEach(() => setup())

  test('the visitor id is stable; a new conversation gets a new conversation id but the same visitor', () => {
    const v = getVisitorId()
    const c = getConversationId()
    assert.equal(getVisitorId(), v)
    assert.equal(getConversationId(), c)
    newConversation()
    assert.notEqual(getConversationId(), c)
    assert.equal(getVisitorId(), v)
  })
})

describe('privacy settings and failures', () => {
  test('"Do Not Track" means nothing is sent', () => {
    setup({ dnt: true })
    logExchange({ question: 'what are the school fees', kind: 'answer', fixed: false, preview: 'x' }, '/stmarkswebsite/')
    assert.equal(sent.length, 0)
  })

  test('a failing network or missing browser APIs never throw', () => {
    setup()
    g.fetch = () => Promise.reject(new Error('offline'))
    assert.doesNotThrow(() => logExchange({ question: 'hi', kind: 'greeting', fixed: false, preview: 'Hello' }))
    g.fetch = () => {
      throw new Error('blocked')
    }
    assert.doesNotThrow(() => logExchange({ question: 'hi', kind: 'greeting', fixed: false, preview: 'Hello' }))
    delete g.location
    assert.doesNotThrow(() => logExchange({ question: 'hi', kind: 'greeting', fixed: false, preview: 'Hello' }))
  })
})
