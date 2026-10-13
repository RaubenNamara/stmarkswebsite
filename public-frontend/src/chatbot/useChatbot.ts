/**
 * Chat state for the widget: message history, typing indicator, persistence. All answering is
 * delegated to the pure engine in engine.ts - this file only manages state and timing.
 */

import { computed, onBeforeUnmount, ref } from 'vue'
import { respond, spellCheck, welcomeReply, looksSensitive, EMPTY_CONTEXT, MAX_INPUT_LENGTH, type BotReply, type ChatContext } from './engine.ts'
import { logExchange, newConversation } from './analytics.ts'
import { loadLiveSources } from './live.ts'
import { buildIndex, type LiveIndex } from './liveIndex.ts'
import { clearChat, loadChat, saveChat, type ChatMessage } from './storage.ts'

let counter = 0
const nextId = () => `${Date.now().toString(36)}-${(counter++).toString(36)}`
const prefersReducedMotion = () => typeof window !== 'undefined' && !!window.matchMedia?.('(prefers-reduced-motion: reduce)').matches

// Live knowledge (database content + PDF text) is shared by every widget instance and loaded once.
let liveIndex: LiveIndex | null = null
let livePromise: Promise<void> | null = null

/** Starts loading the live knowledge (once). Safe to call repeatedly; failures leave the bot on built-in answers. */
function warmLive(): Promise<void> {
  if (!livePromise) {
    livePromise = loadLiveSources(import.meta.env.BASE_URL)
      .then((sources) => {
        if (sources) liveIndex = buildIndex(sources)
      })
      .catch(() => {
        /* built-in answers still work */
      })
  }
  return livePromise
}

/** Waits for the live knowledge, but never longer than `ms` - a slow server must not freeze the chat. */
function waitForLive(ms = 2500): Promise<void> {
  if (liveIndex) return Promise.resolve()
  return Promise.race([warmLive(), new Promise<void>((resolve) => setTimeout(resolve, ms))])
}

const HIDDEN_TEXT = '(Message hidden - it looked like private information.)'

function botMessage(reply: BotReply): ChatMessage {
  return { id: nextId(), role: 'bot', text: reply.text, time: new Date().toISOString(), links: reply.links, suggestions: reply.suggestions }
}

export function useChatbot() {
  const messages = ref<ChatMessage[]>([])
  const typing = ref(false)
  let context: ChatContext = { ...EMPTY_CONTEXT }
  let timer: ReturnType<typeof setTimeout> | null = null
  let resetCount = 0

  const busy = computed(() => typing.value)

  function persist() {
    saveChat({ messages: messages.value, context })
  }

  /** Restores a saved conversation, or starts with the welcome message. */
  function init() {
    const saved = loadChat()
    if (saved) {
      messages.value = saved.messages
      context = saved.context
    } else {
      messages.value = [botMessage(welcomeReply())]
      context = { ...EMPTY_CONTEXT }
    }
  }

  async function send(raw: string) {
    const text = raw.trim().slice(0, MAX_INPUT_LENGTH)
    if (!text || typing.value) return

    messages.value.push({
      id: nextId(),
      role: 'user',
      // Private-looking input is shown as hidden and never written to storage.
      text: looksSensitive(text) ? HIDDEN_TEXT : text,
      time: new Date().toISOString(),
    })
    typing.value = true

    const generation = ++resetCount
    await waitForLive()
    if (generation !== resetCount) return // the chat was cleared while we waited

    const reply = respond(text, context, liveIndex)
    // Anonymous analytics for the admin (what was asked, how it was answered). Private-looking text is never sent.
    logExchange(
      {
        question: text,
        kind: reply.kind,
        ref: reply.entryId ?? reply.live,
        fixed: !reply.sensitive && spellCheck(text, liveIndex).changes.length > 0,
        preview: reply.text,
      },
      import.meta.env.BASE_URL,
    )
    // A short, length-based pause so the answer feels conversational (kept tiny for reduced motion).
    const delay = prefersReducedMotion() ? 120 : Math.min(900, 350 + reply.text.length * 2)
    timer = setTimeout(() => {
      timer = null
      typing.value = false
      context = reply.context
      messages.value.push(botMessage(reply))
      persist()
    }, delay)
  }

  /** Erases the conversation (memory and storage) and starts again. */
  function reset() {
    resetCount++
    newConversation()
    if (timer) clearTimeout(timer)
    timer = null
    typing.value = false
    clearChat()
    context = { ...EMPTY_CONTEXT }
    messages.value = [botMessage(welcomeReply())]
  }

  onBeforeUnmount(() => {
    if (timer) clearTimeout(timer)
  })

  return { messages, typing, busy, init, send, reset, warm: warmLive }
}
