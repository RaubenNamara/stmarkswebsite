/**
 * Spelling tolerance. Visitors type fast, on phones, and in a second language: "adimssion", "scool fes",
 * "chemestry techers", "wher is teh schol". Before a question is matched, each word the bot does not
 * recognise is replaced by the closest word it DOES know.
 *
 * Two vocabularies keep this safe:
 *   - KNOWN words: everything in the school's content, including article text. A word that is known is
 *     never "corrected" (so ordinary words like "weather" or "write" are left alone).
 *   - CORE words: the topic vocabulary - built-in keywords and questions, small-talk words, subjects,
 *     staff and club names, titles, calendar entries, fee/PDF terms. A typo can only be corrected TO a
 *     core word, so "chemestry" becomes "chemistry" but "weather" can't become "whether".
 * A word that resembles nothing (e.g. "bitcoin") is left alone, so it still reads as "not about this
 * school" and the bot says it doesn't know instead of forcing a wrong answer.
 *
 * Deterministic and offline: Damerau-Levenshtein edit distance (insert / delete / replace / swap two
 * letters) with limits that grow with word length, plus splitting run-together words ("schoolfees")
 * and joining split ones ("ad mission").
 */

import { canon, isStopword, MISSPELLINGS, normalize } from './text.ts'

export interface Lexicon {
  known: Set<string>
  /** core word -> weight (more central words win ties). */
  core: Map<string, number>
  byLength: Map<number, string[]>
}

/** Collects words, then freezes them into a Lexicon. */
export class LexiconBuilder {
  known = new Set<string>()
  core = new Map<string, number>()

  /** `core` words may be the target of a correction; the rest only protect words from being "corrected". */
  add(text: string, isCore: boolean, weight = 1): this {
    for (const raw of normalize(text).split(' ')) this.addWord(raw, isCore, weight)
    return this
  }

  addWord(raw: string, isCore: boolean, weight = 1): this {
    if (raw.length < 3 || /\d/.test(raw)) return this
    const c = canon(raw)
    if (c.length < 3) return this
    this.known.add(c)
    this.known.add(raw)
    if (isCore) {
      // The word as written is a valid correction target ("apply", "upcoming", "whatsapp" - the question is
      // re-normalised afterwards), and so is its canonical form when that maps back to itself.
      this.core.set(raw, Math.max(this.core.get(raw) ?? 0, weight))
      if (canon(c) === c) this.core.set(c, Math.max(this.core.get(c) ?? 0, weight))
    }
    return this
  }

  build(): Lexicon {
    const byLength = new Map<number, string[]>()
    for (const w of this.core.keys()) {
      const list = byLength.get(w.length) ?? []
      list.push(w)
      byLength.set(w.length, list)
    }
    return { known: this.known, core: this.core, byLength }
  }

  clone(): LexiconBuilder {
    const b = new LexiconBuilder()
    b.known = new Set(this.known)
    b.core = new Map(this.core)
    return b
  }
}

/** Optimal-string-alignment distance; stops early and returns max+1 when it exceeds `max`. */
export function distance(a: string, b: string, max: number): number {
  if (a === b) return 0
  if (Math.abs(a.length - b.length) > max) return max + 1
  const prev2: number[] = []
  let prev: number[] = Array.from({ length: b.length + 1 }, (_, j) => j)
  for (let i = 1; i <= a.length; i++) {
    const cur: number[] = [i]
    let rowMin = i
    for (let j = 1; j <= b.length; j++) {
      const cost = a[i - 1] === b[j - 1] ? 0 : 1
      let v = Math.min(prev[j] + 1, cur[j - 1] + 1, prev[j - 1] + cost)
      if (i > 1 && j > 1 && a[i - 1] === b[j - 2] && a[i - 2] === b[j - 1]) v = Math.min(v, (prev2[j - 2] ?? Infinity) + 1)
      cur[j] = v
      if (v < rowMin) rowMin = v
    }
    if (rowMin > max) return max + 1
    for (let j = 0; j <= b.length; j++) prev2[j] = prev[j]
    prev = cur
  }
  return prev[b.length]
}

/** True when `a` and `b` have the same length and differ in exactly one position (a one-letter substitution). */
function isSubstitution(a: string, b: string): boolean {
  if (a.length !== b.length) return false
  let diff = 0
  for (let i = 0; i < a.length; i++) if (a[i] !== b[i] && ++diff > 1) return false
  return diff === 1
}

/** True when `b` is `a` with one doubled letter collapsed ("fredd" -> "fred", "bookk" -> "book"). */
function isDoubledLetter(a: string, b: string): boolean {
  if (a.length !== b.length + 1) return false
  for (let i = 1; i < a.length; i++) if (a[i] === a[i - 1] && a.slice(0, i) + a.slice(i + 1) === b) return true
  return false
}

/** True when `a` and `b` differ only by two adjacent letters swapped ("jaems" / "james"). */
function isSwap(a: string, b: string): boolean {
  if (a.length !== b.length) return false
  let i = 0
  while (i < a.length && a[i] === b[i]) i++
  return i < a.length - 1 && a[i] === b[i + 1] && a[i + 1] === b[i] && a.slice(i + 2) === b.slice(i + 2)
}

/** Edits tolerated for a word of this length: none for tiny words, one for ordinary words, two for long ones. */
const maxEdits = (len: number) => (len <= 3 ? 0 : len <= 8 ? 1 : 2)

const isKnown = (lex: Lexicon, w: string) => lex.known.has(w) || lex.known.has(canon(w)) || isStopword(w) || isStopword(canon(w))

/** Best core word within the edit limit, or null. */
function closest(lex: Lexicon, w: string): string | null {
  // three-letter filler words typed with swapped letters ("teh", "hte", "adn")
  if (w.length === 3) {
    for (const cand of lex.byLength.get(3) ?? []) {
      if (!isStopword(cand)) continue
      if ((w[0] === cand[1] && w[1] === cand[0] && w[2] === cand[2]) || (w[0] === cand[0] && w[1] === cand[2] && w[2] === cand[1])) return cand
    }
    return null
  }

  // judge the typed word and its stem ("techers" is compared with "teacher" as "techer" too)
  const forms = [...new Set([w, canon(w)])]
  let best: { word: string; d: number; weight: number } | null = null

  for (const form of forms) {
    const limit = maxEdits(form.length)
    if (limit === 0) continue
    for (let len = form.length - limit; len <= form.length + limit; len++) {
      for (const cand of lex.byLength.get(len) ?? []) {
        // a typo rarely changes the first letter (unless the first two letters were swapped)
        if (cand[0] !== form[0] && !(cand[0] === form[1] && cand[1] === form[0])) continue
        const d = distance(form, cand, limit)
        if (d > limit) continue
        // On short words a one-letter substitution is more often a different real word (cook/cool, write/white)
        // than a typo; insertions, deletions and swapped letters are the typical slips and stay allowed.
        if (d === 1 && form.length <= 5 && isSubstitution(form, cand)) continue
        const weight = lex.core.get(cand) ?? 0
        // Words that only occur in titles and names (weight < 3, e.g. "writer" from a club name) are a poor target
        // when the typed word is just that word minus its ending: "write" is a real word, not a slip. A letter
        // missing from the middle ("gerld" -> "gerald"), swapped letters and doubled letters are still fixed.
        const suffixVariant = cand.startsWith(form) || form.startsWith(cand) // write/writer, book/books
        if (weight < 3 && form.length < 7 && suffixVariant && !(isSwap(form, cand) || isDoubledLetter(form, cand))) continue
        if (!best || d < best.d || (d === best.d && cand.length > best.word.length) || (d === best.d && cand.length === best.word.length && weight > best.weight) || (d === best.d && cand.length === best.word.length && weight === best.weight && cand < best.word)) {
          best = { word: cand, d, weight }
        }
      }
    }
  }
  return best?.word ?? null
}

/** "schoolfees" -> "school fees": two core words (3+ letters each) written together. */
function split(lex: Lexicon, w: string): string | null {
  if (w.length < 7) return null
  for (let i = 3; i <= w.length - 3; i++) {
    const a = w.slice(0, i)
    const b = w.slice(i)
    const core = (x: string) => lex.core.has(canon(x)) || isStopword(x)
    if (core(a) && core(b)) return `${a} ${b}`
  }
  return null
}

export interface Corrected {
  text: string
  /** [typed, corrected] pairs, for tests and diagnostics. */
  changes: Array<[string, string]>
}

/** Returns the question with unrecognised words replaced by the nearest core word. */
export function correctSpelling(normalized: string, lex: Lexicon): Corrected {
  const words = normalized.split(' ').filter(Boolean)
  const changes: Array<[string, string]> = []
  const out: string[] = []

  for (let i = 0; i < words.length; i++) {
    // well-known slips and chat shorthand first ("ur" -> "your", "gud" -> "good", "wat" -> "what")
    const listed = MISSPELLINGS[words[i]]
    if (listed && !listed.includes(' ')) {
      if (canon(listed) !== canon(words[i])) changes.push([words[i], listed])
      out.push(listed)
      continue
    }
    const w = words[i]
    if (w.length < 3 || /\d/.test(w) || isKnown(lex, w)) {
      out.push(w)
      continue
    }
    // two unknown pieces that together make a core word: "ad mission", "uni form"
    const next = words[i + 1]
    if (next && !isKnown(lex, next) && (w + next).length >= 5 && lex.core.has(canon(w + next))) {
      out.push(w + next)
      changes.push([`${w} ${next}`, w + next])
      i++
      continue
    }
    const fixed = closest(lex, w) ?? split(lex, w)
    if (fixed) {
      out.push(fixed)
      changes.push([w, fixed])
    } else {
      out.push(w)
    }
  }
  return { text: out.join(' '), changes }
}
