/**
 * Link validation for the chatbot. Every link a reply can show is classified here first; anything
 * that is not on the approved list is dropped, so a typo in the knowledge base (or a future edit)
 * can never put a `javascript:` URL, an unknown website or a protocol-relative URL in front of a
 * visitor.
 */

export interface ChatLink {
  label: string
  url: string
}

export type LinkKind = 'internal' | 'external' | 'tel' | 'mail' | 'file'

/** Hosts (other than stmark.sc.ug and its sub-domains) the chatbot may link to. */
const EXTERNAL_HOSTS = new Set([
  'wa.me',
  'x.com',
  'www.facebook.com',
  'www.tiktok.com',
  'www.youtube.com',
])

/** Uploaded documents: the public PDF endpoints (/api/public/.../pdf) and files under /uploads/. */
const FILE_PATH = /^(?:\/[A-Za-z0-9_-]+)*\/(?:api\/public\/(?:fee-structures|performances)\/\d+\/pdf|uploads\/[A-Za-z0-9_\-./,%]+\.pdf)$/i
const INTERNAL_PATH = /^\/(?!\/)[A-Za-z0-9\-_./#?=&%]*$/
const TEL = /^tel:\+?[0-9]{7,15}$/
const MAIL = /^mailto:[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/

/** Returns what kind of link `url` is, or null when it is not approved. */
export function classifyLink(url: string): LinkKind | null {
  if (typeof url !== 'string' || url.length === 0 || url.length > 600) return null
  if (FILE_PATH.test(url)) return 'file'
  if (INTERNAL_PATH.test(url)) return 'internal'
  if (TEL.test(url)) return 'tel'
  if (MAIL.test(url)) return 'mail'

  let parsed: URL
  try {
    parsed = new URL(url)
  } catch {
    return null
  }
  if (parsed.protocol !== 'https:' || parsed.username || parsed.password) return null

  const host = parsed.hostname.toLowerCase()
  if (host === 'stmark.sc.ug' || host.endsWith('.stmark.sc.ug')) return 'external'
  if (host === 'www.google.com' && parsed.pathname.startsWith('/maps')) return 'external'
  if (EXTERNAL_HOSTS.has(host)) return 'external'
  return null
}

/** Keeps only approved links with a non-empty label. */
export function sanitizeLinks(links: ChatLink[] | undefined): ChatLink[] {
  if (!links) return []
  return links.filter((l) => l.label.trim() !== '' && classifyLink(l.url) !== null)
}
