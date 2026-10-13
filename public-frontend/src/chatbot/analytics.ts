/**
 * Anonymous chatbot analytics: after each answer the browser tells the school's own server WHAT was asked
 * and HOW the bot answered, so the admin can see which questions visitors have and which the bot missed.
 *
 *  - Visitors are a random id made in their own browser (localStorage). No name, email, IP address or
 *    cookie is involved, and the id can't be traced back to a person.
 *  - Anything that looked like private information (passwords, card/PIN, long numbers) is NEVER sent:
 *    only a placeholder is, and the server redacts emails and phone-like numbers again before saving.
 *  - Nothing is sent when the visitor's browser says "Do Not Track".
 *  - It is fire-and-forget: a failure never affects the chat.

 */

import type { ReplyKind } from './engine.ts'

const VISITOR_KEY = 'smacon-chatbot:vid'
const CONVERSATION_KEY = 'smacon-chatbot:cid'

/** 24 random hex characters (96 bits) - unguessable, and carries no information about the person. */
function randomId(): string {
  const bytes = new Uint8Array(12)
  if (typeof crypto !== 'undefined' && crypto.getRandomValues) crypto.getRandomValues(bytes)
  else for (let i = 0; i < bytes.length; i++) bytes[i] = Math.floor(Math.random() * 256)
  return [...bytes].map((b) => b.toString(16).padStart(2, '0')).join('')
}

// used when localStorage is blocked: ids then last until the page is reloaded
let memoryVisitor: string | null = null
let memoryConversation: string | null = null

function readOrCreate(key: string, memory: () => string | null, setMemory: (v: string) => void): string {
  try {
    const existing = localStorage.getItem(key)
    if (existing && /^[a-f0-9]{16,32}$/.test(existing)) return existing
    const fresh = randomId()
    localStorage.setItem(key, fresh)
    return fresh
  } catch {
    const current = memory()
    if (current) return current
    const fresh = randomId()
    setMemory(fresh)
    return fresh
  }
}

export const getVisitorId = () => readOrCreate(VISITOR_KEY, () => memoryVisitor, (v) => (memoryVisitor = v))
export const getConversationId = () => readOrCreate(CONVERSATION_KEY, () => memoryConversation, (v) => (memoryConversation = v))

/** Starts a new conversation id (called when the visitor clears the chat). */
export function newConversation(): void {
  memoryConversation = null
  try {
    localStorage.setItem(CONVERSATION_KEY, randomId())
  } catch {
    /* storage blocked: a fresh in-memory id is created on next use */
  }
}

export interface Exchange {
  question: string
  kind: ReplyKind
  /** Built-in topic id or live-data resolver that produced the answer. */
  ref?: string
  /** True when the bot had to correct the visitor's spelling. */
  fixed: boolean
  /** Start of the bot's reply, so the admin can see what was actually answered. */
  preview: string
}

const doNotTrack = () => typeof navigator !== 'undefined' && (navigator.doNotTrack === '1' || (window as unknown as { doNotTrack?: string }).doNotTrack === '1')

/** Page the visitor is on, relative to the site's base path, without query string or hash. */
function currentPath(base: string): string {
  const prefix = base.replace(/\/$/, '')
  const path = location.pathname.startsWith(prefix) ? location.pathname.slice(prefix.length) : location.pathname
  return (path || '/').slice(0, 150)
}

/** Sends one exchange. Never throws, never blocks, never retries. */
export function logExchange(e: Exchange, base = '/'): void {
  try {
    if (doNotTrack()) return
    const body = JSON.stringify({
      visitor: getVisitorId(),
      conversation: getConversationId(),
      question: e.kind === 'privacy' ? '[private]' : e.question.slice(0, 300),
      kind: e.kind,
      ref: e.ref,
      fixed: e.fixed,
      page: currentPath(base),
      preview: e.kind === 'privacy' ? undefined : e.preview.slice(0, 300),
    })
    void fetch(base.replace(/\/?$/, '') + '/api/public/chatbot-log', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'omit',
      keepalive: true,
      body,
    }).catch(() => {
      /* analytics is best-effort */
    })
  } catch {
    /* ignore */
  }
}
