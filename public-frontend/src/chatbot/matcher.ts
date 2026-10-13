/**
 * Relevance scoring: ranks knowledge-base entries against a visitor's question.
 *
 * Deterministic and explainable - the same text always gives the same ranking. For each entry:
 *
 *   + keyword hits   a single-word keyword in the question scores 3; a multi-word keyword scores
 *                    3 + 2 per extra word (+1 when the words are also next to each other).
 *   + example match  the question is compared with each example phrasing using the Dice
 *                    coefficient (shared words vs. total words), worth up to 8 points. This is
 *                    what makes "How do I join the school?" land on the admissions entry.
 *   + weak words     0.8 each (max 1.6) - context words that only help break ties.
 *   x exclusions     if the question contains an `exclude` word the score is cut to a quarter.
 *   + topic context  +1 when the entry is on the same topic as the previous answer, which makes
 *                    short follow-ups stick to the conversation.
 *
 * `coverage` reports how much of the question the winning entry actually explained - a long
 * question of which only one word matched is treated as low confidence rather than answered.
 */

import { ENTRIES, type KnowledgeEntry, type Topic } from './knowledge.ts'
import { tokens } from './text.ts'

interface Compiled {
  entry: KnowledgeEntry
  keywords: string[][]
  weak: Set<string>
  exclude: Set<string>
  questions: Set<string>[]
}

export interface Candidate {
  entry: KnowledgeEntry
  score: number
  /** Share (0-1) of the question's content words that this entry accounts for. */
  coverage: number
}

export interface Ranking {
  ranked: Candidate[]
  /** Number of content words in the question (after removing filler words). */
  tokenCount: number
}

const unique = (xs: string[]) => [...new Set(xs)]

/** Keywords that fold to the same canonical words ("apply", "join", "enrol") must only score once. */
function dedupe(keywords: string[][]): string[][] {
  const seen = new Set<string>()
  return keywords.filter((k) => {
    const key = [...k].sort().join(' ')
    if (seen.has(key)) return false
    seen.add(key)
    return true
  })
}

let compiled: Compiled[] | null = null

function compileAll(): Compiled[] {
  if (compiled) return compiled
  compiled = ENTRIES.map((entry) => ({
    entry,
    keywords: dedupe(entry.keywords.map((k) => unique(tokens(k))).filter((k) => k.length > 0)),
    weak: new Set((entry.weak ?? []).flatMap((w) => tokens(w))),
    exclude: new Set((entry.exclude ?? []).flatMap((w) => tokens(w))),
    questions: entry.questions.map((q) => new Set(tokens(q))).filter((q) => q.size > 0),
  }))
  return compiled
}

/** True when all words of `phrase` appear in `list` in order and next to each other. */
function contiguous(list: string[], phrase: string[]): boolean {
  outer: for (let i = 0; i + phrase.length <= list.length; i++) {
    for (let j = 0; j < phrase.length; j++) if (list[i + j] !== phrase[j]) continue outer
    return true
  }
  return false
}

/** True when `t` appears in the built-in knowledge (keywords, example questions or context words). */
export function isBuiltInWord(t: string): boolean {
  return compileAll().some((c) => c.weak.has(t) || c.questions.some((q) => q.has(t)) || c.keywords.some((k) => k.includes(t)))
}

export function rank(query: string, lastTopic?: Topic): Ranking {
  const list = tokens(query)
  const set = new Set(list)
  const out: Candidate[] = []

  if (set.size === 0) return { ranked: out, tokenCount: 0 }

  for (const c of compileAll()) {
    let score = 0
    const matched = new Set<string>()

    let keywordScore = 0
    for (const k of c.keywords) {
      if (!k.every((t) => set.has(t))) continue
      keywordScore += k.length === 1 ? 3 : 3 + 2 * (k.length - 1) + (contiguous(list, k) ? 1 : 0)
      k.forEach((t) => matched.add(t))
    }
    score += Math.min(keywordScore, 12)

    let best = 0
    let bestMatched: string[] = []
    for (const q of c.questions) {
      const shared = [...q].filter((t) => set.has(t))
      if (shared.length === 0 || shared.length < Math.min(2, q.size)) continue
      const dice = (2 * shared.length) / (q.size + set.size)
      if (dice > best) {
        best = dice
        bestMatched = shared
      }
    }
    score += best * 8
    bestMatched.forEach((t) => matched.add(t))

    let weak = 0
    for (const t of c.weak) {
      if (set.has(t)) {
        weak += 0.8
        matched.add(t)
      }
    }
    score += Math.min(weak, 1.6)

    if (score === 0) continue
    if ([...c.exclude].some((t) => set.has(t))) score *= 0.25
    if (lastTopic && c.entry.topic === lastTopic) score += 1

    out.push({ entry: c.entry, score, coverage: matched.size / set.size })
  }

  out.sort((a, b) => b.score - a.score)
  return { ranked: out, tokenCount: set.size }
}
