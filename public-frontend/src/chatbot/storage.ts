/**
 * Optional conversation persistence in the visitor's own browser (localStorage).
 *
 *  - Nothing is ever sent anywhere; the data never leaves the device.
 *  - It is plain chat text only (no names, no form data, no identifiers).
 *  - History older than 7 days is discarded, and only the last 60 messages are kept.
 *  - "Start new chat" (and anything that looks like private information) removes it.
 *  - Everything is wrapped in try/catch: private browsing, blocked storage or a full quota simply
 *    mean the chat works without remembering.
 *
 * Whatever is read back is re-validated field by field, so a tampered value cannot inject anything:
 * messages are only ever rendered as text, and links go through the same allow-list as live replies.
 */

import type { ChatContext, Suggestion } from './engine.ts'
import { sanitizeLinks, type ChatLink } from './links.ts'

export interface ChatMessage {
  id: string
  role: 'bot' | 'user'
  text: string
  /** ISO timestamp. */
  time: string
  links?: ChatLink[]
  suggestions?: Suggestion[]
}

export interface StoredChat {
  messages: ChatMessage[]
  context: ChatContext
}

const KEY = 'smacon-chatbot:v1'
const SEEN_KEY = 'smacon-chatbot:seen'
const MAX_AGE_MS = 7 * 24 * 60 * 60 * 1000
const MAX_MESSAGES = 60

const isString = (v: unknown, max: number): v is string => typeof v === 'string' && v.length <= max

function cleanMessage(raw: unknown): ChatMessage | null {
  if (!raw || typeof raw !== 'object') return null
  const m = raw as Record<string, unknown>
  if (!isString(m.id, 64) || !isString(m.text, 4000) || !isString(m.time, 40)) return null
  if (m.role !== 'bot' && m.role !== 'user') return null
  if (Number.isNaN(Date.parse(m.time))) return null

  const links = Array.isArray(m.links)
    ? sanitizeLinks(m.links.filter((l): l is ChatLink => !!l && isString((l as ChatLink).label, 120) && isString((l as ChatLink).url, 600)).map((l) => ({ label: l.label, url: l.url })))
    : []
  const suggestions = Array.isArray(m.suggestions)
    ? m.suggestions
        .filter((s): s is Suggestion => !!s && isString((s as Suggestion).label, 80) && isString((s as Suggestion).query, 300))
        .map((s) => ({ label: s.label, query: s.query }))
    : []

  return { id: m.id, role: m.role, text: m.text, time: m.time, links, suggestions }
}

export function loadChat(): StoredChat | null {
  try {
    const raw = localStorage.getItem(KEY)
    if (!raw) return null
    const data = JSON.parse(raw) as Record<string, unknown>
    if (typeof data.savedAt !== 'number' || Date.now() - data.savedAt > MAX_AGE_MS) {
      localStorage.removeItem(KEY)
      return null
    }
    if (!Array.isArray(data.messages)) return null
    const messages = data.messages.map(cleanMessage).filter((m): m is ChatMessage => m !== null)
    if (messages.length === 0) return null

    const c = (data.context ?? {}) as Record<string, unknown>
    const context: ChatContext = {
      lastEntryId: typeof c.lastEntryId === 'string' ? c.lastEntryId : undefined,
      lastTopic: typeof c.lastTopic === 'string' ? (c.lastTopic as ChatContext['lastTopic']) : undefined,
      pendingEntryId: typeof c.pendingEntryId === 'string' ? c.pendingEntryId : undefined,
      fallbackCount: typeof c.fallbackCount === 'number' && c.fallbackCount >= 0 && c.fallbackCount < 100 ? c.fallbackCount : 0,
    }
    return { messages, context }
  } catch {
    return null
  }
}

export function saveChat(chat: StoredChat): void {
  try {
    const payload = { savedAt: Date.now(), messages: chat.messages.slice(-MAX_MESSAGES), context: chat.context }
    localStorage.setItem(KEY, JSON.stringify(payload))
  } catch {
    /* storage unavailable or full - the chat still works, it just won't be remembered */
  }
}

export function clearChat(): void {
  try {
    localStorage.removeItem(KEY)
  } catch {
    /* nothing to clear */
  }
}

/** Whether the visitor has already opened the chat once (controls the launcher's notification dot). */
export function hasSeenChat(): boolean {
  try {
    return localStorage.getItem(SEEN_KEY) === '1'
  } catch {
    return false
  }
}

export function markChatSeen(): void {
  try {
    localStorage.setItem(SEEN_KEY, '1')
  } catch {
    /* ignore */
  }
}
