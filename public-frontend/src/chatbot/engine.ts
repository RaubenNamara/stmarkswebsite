/**
 * Response engine: turns a visitor's message (plus a little conversation context) into a reply.
 * Pure and deterministic - no network, no randomness, no AI model. The decision order is:
 *
 *   1. private-information check   (never echo or store passwords, card numbers ...)
 *   2. small talk                  (greeting, thanks, goodbye, yes/no, help, "are you a bot?", "more")
 *   3. knowledge-base ranking      (matcher.ts)
 *        - clear winner            -> answer
 *        - two different topics    -> ask the visitor which one they meant
 *        - weak / partial match    -> "did you mean ...?"
 *        - nothing reliable        -> honest fallback pointing to the school office
 */

import { ENTRIES, QUICK_REPLIES, CONTACT_LINKS, PHONE_HEADTEACHER, EMAIL_INFO, type KnowledgeEntry, type Topic } from './knowledge.ts'
import { sanitizeLinks, type ChatLink } from './links.ts'
import { isBuiltInWord, rank, type Candidate } from './matcher.ts'
import type { LiveIndex } from './liveIndex.ts'
import { calendarListing, passageAnswer, resolveForEntry, staffAnswer, type LiveReply } from './liveAnswer.ts'
import { normalize, startsWithPhrase, stopwordList, SYNONYMS, tokens } from './text.ts'
import { correctSpelling, LexiconBuilder, type Corrected, type Lexicon } from './spell.ts'

export interface Suggestion {
  label: string
  /** Text sent as the visitor's message when the button is tapped. */
  query: string
}

export interface ChatContext {
  lastEntryId?: string
  lastTopic?: Topic
  /** Entry a following "yes" should answer with. */
  pendingEntryId?: string
  /** Consecutive questions the bot could not answer (varies the wording, escalates to the office). */
  fallbackCount: number
}

export type ReplyKind = 'answer' | 'greeting' | 'thanks' | 'goodbye' | 'help' | 'identity' | 'clarify' | 'fallback' | 'privacy' | 'ack'

export interface BotReply {
  kind: ReplyKind
  text: string
  links: ChatLink[]
  suggestions: Suggestion[]
  entryId?: string
  /** Set when the answer was built from live data (database / uploaded PDFs) rather than built-in text. */
  live?: LiveReply['resolver']
  context: ChatContext
  /** True when the visitor's message looked like private information and should not be kept. */
  sensitive?: boolean
}

export const MAX_INPUT_LENGTH = 300
export const EMPTY_CONTEXT: ChatContext = { fallbackCount: 0 }

/** Minimum score for a confident answer. */
const MIN_SCORE = 3
/** Below MIN_SCORE but at least this: offer "did you mean ...?" buttons. */
const SUGGEST_SCORE = 2
/** A runner-up from another topic within this share of the winner triggers a clarifying question. */
const AMBIGUITY_RATIO = 0.7
/** Winners at or above this score are decisive - no clarification even if another topic is close. */
const DECISIVE_SCORE = 10

const AUTHORITATIVE: ReadonlySet<string> = new Set(['event', 'document', 'staff', 'board', 'achiever'])
const RESOLVER_ENTRIES: ReadonlySet<string> = new Set(['fees', 'personal-needs', 'calendar', 'leadership', 'news-events'])

const byId = new Map(ENTRIES.map((e) => [e.id, e]))

function chipFor(entry: KnowledgeEntry): Suggestion {
  return { label: entry.chip, query: entry.questions[0] }
}

const topLevelChips = (): Suggestion[] => QUICK_REPLIES.slice(0, 4).map(({ label, query }) => ({ label, query }))

// ---- Small-talk vocabulary (normalised form) ------------------------------------------------------

const GREETINGS = [
  'hi', 'hello', 'hey', 'hallo', 'hiya', 'howdy', 'greetings', 'good morning', 'good afternoon', 'good evening',
  'good day', 'morning', 'afternoon', 'evening', 'hi there', 'hello there', 'hey there', 'how are you doing',
  'how are you', 'hows it going', 'whats up', 'sup', 'yo',
  // Luganda greetings: "oli otya" (how are you), "wasuze otya" (good morning), "osiibye otya" (good afternoon)
  'oli otya', 'wasuze otya', 'osiibye otya', 'gyebale',
]
const THANKS = ['thanks', 'thank you', 'thank u', 'thanks a lot', 'thanks so much', 'many thanks', 'thank you very much', 'cheers', 'much appreciated', 'i appreciate it', 'appreciate it', 'weebale', 'webale', 'weebale nnyo']
const GOODBYES = ['bye', 'goodbye', 'good bye', 'bye bye', 'see you', 'see you later', 'take care', 'good night', 'goodnight', 'farewell', 'thats all', 'that is all', 'thats it', 'nothing else', 'no more questions', 'i am done', 'im done', 'ssebo bye']
const AFFIRM = ['yes', 'yeah', 'yep', 'yup', 'sure', 'ok', 'okay', 'please do', 'go ahead', 'yes please', 'ok please', 'okay please', 'sounds good', 'alright', 'why not', 'please', 'of course', 'definitely']
const NEGATE = ['no', 'nope', 'nah', 'no thanks', 'no thank you', 'not now', 'maybe later', 'not really', 'no need']
const HELP = ['help', 'menu', 'options', 'what can you do', 'what can i ask', 'what do you know', 'how does this work', 'how do i use this', 'topics', 'what can you help with', 'commands', 'start', 'start over', 'can you help me', 'i need help']
const IDENTITY = [
  'who are you', 'what are you', 'are you a bot', 'are you a robot', 'are you human', 'are you a human', 'are you real',
  'are you a real person', 'are you ai', 'are you an ai', 'are you chatgpt', 'is this ai', 'is this a bot', 'is this a robot',
  'whats your name', 'what is your name', 'who am i talking to', 'who is this', 'are you a person', 'do you use ai', 'are you using ai',
  'are you powered by', 'are you artificial intelligence',
]
const MORE = ['more', 'tell me more', 'more info', 'more information', 'more details', 'what else', 'anything else', 'go on', 'continue', 'elaborate', 'explain more', 'say more', 'more please', 'show me more']
const LEAD_FILLERS = ['ok', 'okay', 'great', 'cool', 'nice', 'alright', 'awesome', 'perfect', 'good', 'well', 'so', 'and', 'then', 'um', 'umm', 'hmm']

/** Longest phrases first so "good morning" is removed before "good". */
const longestFirst = (xs: string[]) => [...xs].sort((a, b) => b.length - a.length)
const GREETINGS_L = longestFirst(GREETINGS)
const THANKS_L = longestFirst(THANKS)

/** Removes one leading phrase from `list`; returns the rest and whether anything was removed. */
function stripLeading(text: string, list: string[]): [string, boolean] {
  let rest = text
  let hit = false
  for (let again = true; again; ) {
    again = false
    for (const phrase of list) {
      if (startsWithPhrase(rest, phrase)) {
        rest = rest.slice(phrase.length).trim()
        hit = true
        again = true
        break
      }
    }
  }
  return [rest, hit]
}

const equalsAny = (text: string, list: string[]) => list.includes(text)

// ---- Spelling tolerance ---------------------------------------------------------------------------

let baseBuilder: LexiconBuilder | null = null
let baseLexicon: Lexicon | null = null
const lexiconCache = new WeakMap<LiveIndex, Lexicon>()

/** Live-content kinds whose words are topic vocabulary (names, subjects, dates, fee terms); article text is not. */
const CORE_BODY_KINDS: ReadonlySet<string> = new Set(['staff', 'board', 'event', 'achiever', 'document', 'results', 'alumni'])

/**
 * Words the bot knows. A typo is only ever corrected TO a "core" word (built-in keywords and questions,
 * small talk, and live titles/names/fee terms); words that merely occur in article text just protect
 * ordinary English from being "corrected".
 */
function lexiconFor(live: LiveIndex | null): Lexicon {
  if (!baseBuilder) {
    const b = new LexiconBuilder()
    for (const e of ENTRIES) {
      for (const q of e.questions) b.add(q, true, 3)
      for (const k of e.keywords) b.add(k, true, 4)
      for (const w of e.weak ?? []) b.add(w, true, 2)
      b.add(e.chip, true, 2)
      b.add(e.answer, false)
    }
    for (const list of [GREETINGS, THANKS, GOODBYES, AFFIRM, NEGATE, HELP, IDENTITY, MORE, LEAD_FILLERS]) for (const p of list) b.add(p, true, 3)
    for (const w of stopwordList()) b.addWord(w, true, 1)
    // every alias the normaliser knows: months, weekdays, subject short names and full names ...
    for (const w of Object.keys(SYNONYMS)) b.addWord(w, true, 3)
    baseBuilder = b
  }
  if (!live) return (baseLexicon ??= baseBuilder.build())
  let lex = lexiconCache.get(live)
  if (!lex) {
    const b = baseBuilder.clone()
    for (const src of live.sources) {
      b.add(src.title, true, 2)
      b.add(src.text, CORE_BODY_KINDS.has(src.kind), 2)
    }
    lex = b.build()
    lexiconCache.set(live, lex)
  }
  return lex
}

/** The question as the bot reads it after fixing spelling (exposed for tests and diagnostics). */
export function spellCheck(input: string, live: LiveIndex | null = null): Corrected {
  return correctSpelling(normalize(input.trim().slice(0, MAX_INPUT_LENGTH)), lexiconFor(live))
}

// ---- Private information -------------------------------------------------------------------------

/**
 * Credentials ("my password is ...", "pin: 1234"), card-like numbers and long digit strings.
 * Asking ABOUT passwords ("I forgot my password") is fine and does not match.
 */
export function looksSensitive(input: string): boolean {
  const n = normalize(input)
  if (/\b(password|passcode|pin|cvv|otp)\s*(is|was|=|:)?\s*\S*\d/.test(input.toLowerCase())) return true
  if (/\b(my )?(password|passcode|pin|cvv|otp) (is|was) \S+/.test(n)) return true
  if (/\b\d{4}[ -]?\d{4}[ -]?\d{4}[ -]?\d{1,7}\b/.test(input)) return true
  return /\d{9,}/.test(input.replace(/[ \-.()+]/g, ''))
}

// ---- Reply builders --------------------------------------------------------------------------------

function reply(kind: ReplyKind, text: string, ctx: ChatContext, extra: Partial<BotReply> = {}): BotReply {
  return { kind, text, links: [], suggestions: [], context: ctx, ...extra }
}

function helpReply(ctx: ChatContext, intro?: string): BotReply {
  return reply(
    'help',
    (intro ? intro + '\n\n' : '') +
      "I can answer questions about admissions, fees, contacts, location, academics, school activities, uniform, the school calendar and the eSpace eLearning platform. I'm an automated assistant that answers from the information published on this website and its documents (fee structures, the term programme, the calendar). Tap a topic or type your question.",
    { ...ctx, pendingEntryId: undefined, fallbackCount: 0 },
    { suggestions: QUICK_REPLIES.map(({ label, query }) => ({ label, query })) },
  )
}

export function welcomeReply(): BotReply {
  return reply(
    'greeting',
    "Hello! Welcome to St. Mark's College Namagoma. I'm the school's virtual assistant. What would you like to know?",
    { ...EMPTY_CONTEXT },
    { suggestions: QUICK_REPLIES.map(({ label, query }) => ({ label, query })) },
  )
}

function answerReply(entry: KnowledgeEntry): BotReply {
  const links = sanitizeLinks(entry.links)
  const related = (entry.related ?? []).map((id) => byId.get(id)).filter((e): e is KnowledgeEntry => !!e)
  let text = entry.answer
  let suggestions: Suggestion[]

  if (entry.id === 'faq') {
    suggestions = related.map(chipFor)
  } else {
    suggestions = related.slice(0, 4).map(chipFor)
  }

  return reply('answer', text, {
    lastEntryId: entry.id,
    lastTopic: entry.topic,
    pendingEntryId: undefined,
    fallbackCount: 0,
  }, { links, suggestions, entryId: entry.id })
}

function clarifyReply(cands: Candidate[], base: ChatContext, text: string): BotReply {
  return reply('clarify', text, { ...base, pendingEntryId: undefined }, { suggestions: cands.map((c) => chipFor(c.entry)) })
}

const FALLBACKS = [
  "Sorry, I don't have that information.",
  "Sorry, that information isn't available to me. The school office can help:",
  `That information isn't available to me. Please call the Headteacher's Office on ${PHONE_HEADTEACHER[1]} or email ${EMAIL_INFO}.`,
]

function fallbackReply(ctx: ChatContext): BotReply {
  const text = FALLBACKS[ctx.fallbackCount % FALLBACKS.length]
  return reply('fallback', text, { ...ctx, pendingEntryId: undefined, fallbackCount: ctx.fallbackCount + 1 }, {
    links: sanitizeLinks(CONTACT_LINKS),
    suggestions: topLevelChips(),
  })
}

// ---- Main entry point ------------------------------------------------------------------------------

export function respond(rawInput: string, ctx: ChatContext = EMPTY_CONTEXT, live: LiveIndex | null = null, now: Date = new Date()): BotReply {
  const input = rawInput.trim().slice(0, MAX_INPUT_LENGTH)
  const base: ChatContext = { ...ctx }

  if (!input) return helpReply(base)

  // 1. Private information: acknowledge, never process.
  if (looksSensitive(input)) {
    return reply(
      'privacy',
      "That looks like private information (a password, PIN, card or long number). Please don't share it here - I can't use it, and messages typed in this chat are not passed on to staff. For anything account- or payment-related, please contact the school office directly.",
      { ...base, pendingEntryId: undefined },
      { links: sanitizeLinks(CONTACT_LINKS), suggestions: topLevelChips(), sensitive: true },
    )
  }

  // 2. Small talk. Greetings/thanks are peeled off so "hello, what are the fees?" still gets answered.
  // Fix spelling first, so greetings, small talk and topic matching all see the corrected words.
  let q = correctSpelling(normalize(input), lexiconFor(live)).text
  let greeted = false
  let thanked = false

  const [afterGreeting, g] = stripLeading(q, GREETINGS_L)
  q = afterGreeting
  greeted = g
  const [afterFiller] = stripLeading(q, LEAD_FILLERS)
  const [afterThanks, t] = stripLeading(afterFiller, THANKS_L)
  if (t) {
    // "thank you so much": whatever is left carries no topic, so it is still just thanks.
    q = tokens(afterThanks).length === 0 ? '' : afterThanks.replace(/^(and|then)\s+/, '')
    thanked = true
  }

  if (q === '' || equalsAny(q, LEAD_FILLERS)) {
    if (thanked) return reply('thanks', "You're welcome! Is there anything else you'd like to know about the school?", { ...base, pendingEntryId: undefined }, { suggestions: topLevelChips() })
    if (greeted) {
      return reply('greeting', 'Hello! How can I help you today? You can pick a topic below or type your question.', { ...base, pendingEntryId: undefined }, {
        suggestions: QUICK_REPLIES.map(({ label, query }) => ({ label, query })),
      })
    }
  }

  const [leanQ] = stripLeading(q, LEAD_FILLERS)
  const probe = leanQ || q

  if (equalsAny(probe, GOODBYES)) {
    return reply('goodbye', 'Goodbye, and thank you for visiting St. Mark\'s College Namagoma. Come back any time!', { ...EMPTY_CONTEXT })
  }
  if (equalsAny(probe, NEGATE)) {
    return reply('ack', "No problem. Is there anything else I can help you with?", { ...base, pendingEntryId: undefined }, { suggestions: topLevelChips() })
  }
  if (equalsAny(probe, AFFIRM)) {
    const pending = base.pendingEntryId ? byId.get(base.pendingEntryId) : undefined
    if (pending) return answerReply(pending)
    return reply('ack', 'Great - what would you like to know?', { ...base, pendingEntryId: undefined }, { suggestions: topLevelChips() })
  }
  if (equalsAny(probe, IDENTITY)) {
    return reply(
      'identity',
      "I'm the school's virtual assistant. I'm not a person, and I'm not powered by an AI model - I'm a rule-based helper that matches your question against information published on this website. If I can't answer, I'll tell you and point you to the school office.",
      { ...base, pendingEntryId: undefined },
      { suggestions: topLevelChips() },
    )
  }
  if (equalsAny(probe, MORE) || (probe.includes('more') && tokens(probe).length === 0)) {
    const last = base.lastEntryId ? byId.get(base.lastEntryId) : undefined
    if (last && last.related?.length) {
      const related = last.related.map((id) => byId.get(id)).filter((e): e is KnowledgeEntry => !!e)
      return reply('answer', 'Here are some related things I can help with:', { ...base, pendingEntryId: undefined }, {
        links: sanitizeLinks(last.links),
        suggestions: related.slice(0, 4).map(chipFor),
        entryId: last.id,
      })
    }
    return helpReply(base)
  }
  if (equalsAny(probe, HELP) || (probe.includes('help') && tokens(probe).length === 0)) return helpReply(base)

  // 3. Knowledge: built-in entries first, then live data (database content + uploaded PDFs).
  const { ranked, tokenCount } = rank(q, base.lastTopic)
  const top = ranked[0]
  // With live data loaded the vocabulary is rich, so a word found in neither the built-in knowledge nor
  // the school's content (e.g. "bitcoin") means the question isn't really about what matched.
  const alien = live && tokenCount >= 2 ? tokens(q).some((t) => !live.idf.has(t) && !isBuiltInWord(t)) : false
  const partial = !!top && ((tokenCount >= 4 && top.coverage < 0.34 && top.score < 9) || (alien && top.coverage <= 0.6 && top.score < 12))
  const builtInOk = !!top && tokenCount > 0 && top.score >= MIN_SCORE && !partial

  // Month / "upcoming" listings of calendar events work whatever else the question resembles.
  const listing = live ? calendarListing(q, live, now) : null
  if (listing) return liveReply(listing, builtInOk && top.entry.topic === 'academics' ? top.entry : undefined)

  // "Teachers in ICT", "who teaches chemistry": the staff list for that department.
  const staff = live ? staffAnswer(q, live) : null
  if (staff) return liveReply(staff)

  // 3a. A matched built-in topic that has a precise live resolver (fee amounts, personal-requirement
  //     lists, calendar events, leadership names) answers from the live data instead of generic text.
  if (live && builtInOk) {
    const precise = resolveForEntry(top.entry.id, q, live, now)
    if (precise) return liveReply(precise, top.entry)
  }

  // 3b. Any other question: look for a passage in the database content / PDFs. It wins when the
  //     built-in knowledge has nothing confident, or when the passage is a specific, strong match
  //     (two or more distinctive words covered) - e.g. "science club" beats the generic Clubs entry.
  const found = live ? passageAnswer(q, live, now) : null
  // Calendar rows, PDF lines, staff and board entries are authoritative: one distinctive word is enough.
  const authoritative = !!found && AUTHORITATIVE.has(found.sourceKind) && found.distinctive && found.reply.confidence >= 0.6
  // For topics with a precise resolver (fees, requirements ...) a loose news story must not replace the built-in answer.
  const guarded = builtInOk && RESOLVER_ENTRIES.has(top.entry.id) && !(found && AUTHORITATIVE.has(found.sourceKind))
  if (found && !guarded && (!builtInOk || authoritative || (found.reply.confidence >= 0.8 && found.matchedCount >= 2))) {
    return liveReply(found.reply, builtInOk ? top.entry : undefined)
  }

  // 3c. Built-in knowledge only.
  if (!top || tokenCount === 0 || top.score < SUGGEST_SCORE) return fallbackReply(base)

  if (top.score < MIN_SCORE || partial) {
    const options = pickDistinct(ranked, 3, SUGGEST_SCORE)
    return clarifyReply(options, base, "I'm not sure what you mean. Did you mean one of these?")
  }

  // Ambiguity: a strong runner-up from a different topic.
  if (top.score < DECISIVE_SCORE) {
    const rival = ranked.find((c) => c.entry.topic !== top.entry.topic && c.score >= top.score * AMBIGUITY_RATIO)
    if (rival) {
      const options = [top, ...pickDistinct(ranked.filter((c) => c.entry.topic !== top.entry.topic), 2, top.score * AMBIGUITY_RATIO)]
      return clarifyReply(options, base, 'Which of these do you mean?')
    }
  }

  return answerReply(top.entry)
}

/** Wraps a live-data answer as a reply, keeping conversation context when a built-in topic matched. */
function liveReply(r: LiveReply, entry?: KnowledgeEntry): BotReply {
  const related = (entry?.related ?? []).map((id) => byId.get(id)).filter((e): e is KnowledgeEntry => !!e)
  const suggestions = r.suggestions ?? (related.length ? related.slice(0, 4).map(chipFor) : topLevelChips())
  return reply(
    r.clarify ? 'clarify' : 'answer',
    r.text,
    { lastEntryId: entry?.id, lastTopic: entry?.topic, pendingEntryId: undefined, fallbackCount: 0 },
    { links: sanitizeLinks(r.links), suggestions, entryId: entry?.id, live: r.resolver },
  )
}

/** Up to `n` candidates scoring at least `min`, at most one per topic. */
function pickDistinct(cands: Candidate[], n: number, min: number): Candidate[] {
  const seen = new Set<Topic>()
  const out: Candidate[] = []
  for (const c of cands) {
    if (c.score < min || seen.has(c.entry.topic)) continue
    seen.add(c.entry.topic)
    out.push(c)
    if (out.length === n) break
  }
  return out
}
