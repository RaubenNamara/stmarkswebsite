/**
 * "Live" knowledge: public content from the school's own database (calendar, news, clubs, staff
 * roles ...) plus the text of uploaded fee/programme PDFs, delivered by the backend endpoint
 * GET /api/public/chatbot-knowledge (see shared/src/Services/ChatbotKnowledgeService.php).
 *
 * The request is a plain GET to the school's own server: nothing the visitor types is ever sent.
 * If it fails (offline, server down), the chatbot just keeps answering from its built-in knowledge.
 *
 * Everything arriving from the server is re-validated here before use - unknown kinds are dropped,
 * text is length-capped, and every link goes through the same allow-list as built-in answers.
 */

import { classifyLink } from './links.ts'

export type SourceKind =
  | 'event' | 'news' | 'post' | 'voice' | 'club' | 'activity' | 'programme'
  | 'staff' | 'board' | 'achiever' | 'alumni' | 'document' | 'results'

const KINDS: ReadonlySet<string> = new Set<SourceKind>([
  'event', 'news', 'post', 'voice', 'club', 'activity', 'programme',
  'staff', 'board', 'achiever', 'alumni', 'document', 'results',
])

export interface LiveSource {
  kind: SourceKind
  id: string
  title: string
  text: string
  /** ISO date (YYYY-MM-DD) when the item has one. */
  date?: string
  completed?: boolean
  /** Page on this website where the item lives (validated internal path). */
  url?: string
  /** Browser-openable link to the uploaded PDF, for documents. */
  fileUrl?: string
  /** Whether a PDF's text could be read (false for scanned or withheld documents). */
  readable?: boolean
}

const MAX_SOURCES = 600
const MAX_TEXT = 70000
const CACHE_KEY = 'smacon-chatbot:live:v1'
const CACHE_MS = 10 * 60 * 1000

const str = (v: unknown, max: number): string => (typeof v === 'string' ? v.slice(0, max) : '')

/** Validates the raw feed. `base` is the site's base path (e.g. "/stmarkswebsite/"). */
export function parseSources(raw: unknown, base = '/'): LiveSource[] {
  if (!Array.isArray(raw)) return []
  const apiBase = base.replace(/\/?$/, '') + '/api/public'
  const out: LiveSource[] = []

  for (const item of raw.slice(0, MAX_SOURCES)) {
    if (!item || typeof item !== 'object') continue
    const r = item as Record<string, unknown>
    if (typeof r.kind !== 'string' || !KINDS.has(r.kind)) continue
    const title = str(r.title, 300).trim()
    if (!title) continue

    const src: LiveSource = {
      kind: r.kind as SourceKind,
      id: str(r.id, 80),
      title,
      text: str(r.text, MAX_TEXT),
    }
    if (typeof r.date === 'string' && /^\d{4}-\d{2}-\d{2}/.test(r.date)) src.date = r.date.slice(0, 10)
    if (typeof r.completed === 'boolean') src.completed = r.completed
    if (typeof r.readable === 'boolean') src.readable = r.readable

    const url = str(r.url, 200)
    if (url && classifyLink(url) === 'internal') src.url = url

    if (typeof r.endpoint === 'string' && /^\/(fee-structures|performances)\/\d+\/pdf$/.test(r.endpoint)) {
      src.fileUrl = apiBase + r.endpoint
    } else if (typeof r.file_url === 'string' && ['file', 'external'].includes(classifyLink(r.file_url) ?? '')) {
      src.fileUrl = r.file_url
    }
    out.push(src)
  }
  return out
}

/** Fetches the feed once (cached for 10 minutes in sessionStorage). Resolves to null on any failure. */
export async function loadLiveSources(base = '/', timeoutMs = 8000): Promise<LiveSource[] | null> {
  try {
    const cached = sessionStorage.getItem(CACHE_KEY)
    if (cached) {
      const { t, raw } = JSON.parse(cached) as { t: number; raw: unknown }
      const fromCache = Date.now() - t < CACHE_MS ? parseSources(raw, base) : []
      if (fromCache.length) return fromCache
    }
  } catch {
    /* storage unavailable - fetch normally */
  }

  try {
    const ctl = new AbortController()
    const timer = setTimeout(() => ctl.abort(), timeoutMs)
    const res = await fetch(base.replace(/\/?$/, '') + '/api/public/chatbot-knowledge', {
      headers: { Accept: 'application/json' },
      credentials: 'omit',
      signal: ctl.signal,
    })
    clearTimeout(timer)
    if (!res.ok) return null
    const body = (await res.json()) as { data?: { sources?: unknown } }
    const raw = body?.data?.sources
    const sources = parseSources(raw, base)
    if (sources.length === 0) return null
    try {
      // The server's raw feed is cached and re-validated on every read, never trusted as-is.
      sessionStorage.setItem(CACHE_KEY, JSON.stringify({ t: Date.now(), raw }))
    } catch {
      /* quota - fine */
    }
    return sources
  } catch {
    return null
  }
}
