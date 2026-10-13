/**
 * Turns the text an admin typed or pasted into safe, well-structured HTML for an article page.
 * Shared by the eVoices article page and the club pages.
 *
 * Content reaches the database in two shapes:
 *   - plain text, with a blank line between paragraphs (clubs, many older articles). Shown as-is in HTML
 *     it would collapse into one block, so it is escaped and split into <p> paragraphs.
 *   - HTML from the rich-text editor, often pasted from Word, which drags along inline styles
 *     (Times New Roman, tiny point sizes, fixed margins, "MsoNormal" classes) that override the site's
 *     typography and ignore the screen size. Those are removed; the structure (paragraphs, line breaks,
 *     bold, lists, links, images) is kept.
 */

const escapeHtml = (s: string) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')

export function cleanWordHtml(html: string): string {
  return html
    .replace(/<\/?o:p[^>]*>/gi, '')
    .replace(/\s(?:style|lang|dir|align|face|size)="[^"]*"/gi, '')
    .replace(/\sclass="Mso[^"]*"/gi, '')
    .replace(/<span>\s*<\/span>/gi, '')
    .replace(/<p>(?:\s|&nbsp;|<br\s*\/?>)*<\/p>/gi, '')
}

/** Blank line = new paragraph, single line break = <br>; everything is escaped first. */
export function plainTextToHtml(text: string): string {
  return text
    .replace(/\r\n?/g, '\n')
    .split(/\n\s*\n/)
    .map((p) => p.trim())
    .filter(Boolean)
    .map((p) => `<p>${escapeHtml(p).replace(/\n/g, '<br>')}</p>`)
    .join('\n')
}

/** Picks the right treatment: HTML (has tags) is cleaned, plain text is converted. */
export function toArticleHtml(content: unknown): string {
  const text = String(content ?? '')
  return /<\/?[a-z][\s\S]*>/i.test(text) ? cleanWordHtml(text) : plainTextToHtml(text)
}
