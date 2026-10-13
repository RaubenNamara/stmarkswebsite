<script setup lang="ts">
/**
 * Floating school assistant. Mounted once from App.vue (client-side only, after the page is idle).
 *
 * It is a rule-based helper: every answer comes from chatbot/engine.ts matching the visitor's text
 * against chatbot/knowledge.ts. No network request is made by anything in this component.
 */
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useChatbot } from '../../chatbot/useChatbot'
import { MAX_INPUT_LENGTH } from '../../chatbot/engine'
import { hasSeenChat, markChatSeen } from '../../chatbot/storage'
import { staticAsset } from '../../utils/staticAsset'
import ChatMessage from './ChatMessage.vue'

const { messages, typing, init, send, reset, warm } = useChatbot()

const open = ref(false)
const draft = ref('')
const confirmingReset = ref(false)
const showDot = ref(false)
const logoFailed = ref(false)
const fullScreen = ref(false) // small screens: the panel fills the screen and behaves like a modal dialog

const panel = ref<HTMLElement | null>(null)
const log = ref<HTMLElement | null>(null)
const input = ref<HTMLInputElement | null>(null)
const launcher = ref<HTMLButtonElement | null>(null)

let mq: MediaQueryList | null = null
let previousOverflow = ''

function onMediaChange() {
  fullScreen.value = !!mq?.matches
  if (!open.value) return
  lockScroll(fullScreen.value)
  syncViewport()
}

// ---- Phone keyboard / viewport handling --------------------------------------------------------
// On phones the on-screen keyboard shrinks the *visual* viewport but not the layout viewport, which
// would leave the input hidden behind it. Sizing the panel to visualViewport keeps it above the keyboard.
function syncViewport() {
  const el = panel.value
  const vv = window.visualViewport
  if (!el) return
  if (fullScreen.value && vv) {
    el.style.setProperty('--smc-h', `${vv.height}px`)
    el.style.setProperty('--smc-top', `${vv.offsetTop}px`)
  } else {
    el.style.removeProperty('--smc-h')
    el.style.removeProperty('--smc-top')
  }
}

function lockScroll(lock: boolean) {
  const el = document.documentElement
  if (lock) {
    if (el.style.overflow !== 'hidden') previousOverflow = el.style.overflow
    el.style.overflow = 'hidden'
  } else {
    el.style.overflow = previousOverflow
  }
}

// ---- Open / close ------------------------------------------------------------------------------
async function openChat() {
  open.value = true
  showDot.value = false
  markChatSeen()
  void warm() // fetch the school's live content (calendar, PDFs ...) in the background
  lockScroll(fullScreen.value)
  await nextTick()
  syncViewport()
  scrollToEnd(false)
  // Only auto-focus the text field where there is a hardware keyboard / precise pointer; on phones
  // that would pop the keyboard up and hide the welcome message and suggestions.
  if (window.matchMedia('(pointer: fine)').matches) input.value?.focus()
  else panel.value?.focus()
}

function closeChat(returnFocus = true) {
  open.value = false
  confirmingReset.value = false
  lockScroll(false)
  if (returnFocus) nextTick(() => launcher.value?.focus())
}

function toggle() {
  if (open.value) closeChat()
  else openChat()
}

/** Phones show the panel full-screen, so navigating to a page has to get it out of the way. */
function onNavigate() {
  if (fullScreen.value) closeChat(false)
}

// ---- Keyboard ----------------------------------------------------------------------------------
function onKeydown(e: KeyboardEvent) {
  if (!open.value) return
  if (e.key === 'Escape') {
    if (confirmingReset.value) confirmingReset.value = false
    else closeChat()
    e.stopPropagation()
    return
  }
  // Full-screen mode is modal: keep Tab inside the panel.
  if (e.key === 'Tab' && fullScreen.value && panel.value) {
    const focusable = [...panel.value.querySelectorAll<HTMLElement>('a[href], button:not([disabled]), input:not([disabled])')].filter((el) => el.offsetParent !== null)
    if (focusable.length === 0) return
    const first = focusable[0]
    const last = focusable[focusable.length - 1]
    const active = document.activeElement
    if (e.shiftKey && (active === first || active === panel.value)) {
      e.preventDefault()
      last.focus()
    } else if (!e.shiftKey && active === last) {
      e.preventDefault()
      first.focus()
    }
  }
}

// ---- Sending -----------------------------------------------------------------------------------
function submit() {
  const text = draft.value.trim()
  if (!text || typing.value) return
  draft.value = ''
  send(text)
}

function pick(query: string) {
  if (typing.value) return
  send(query)
  input.value?.focus({ preventScroll: true })
}

function doReset() {
  confirmingReset.value = false
  reset()
  nextTick(() => input.value?.focus())
}

// ---- Scrolling ---------------------------------------------------------------------------------
function scrollToEnd(smooth = true) {
  const el = log.value
  if (!el) return
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches
  el.scrollTo({ top: el.scrollHeight, behavior: smooth && !reduce ? 'smooth' : 'auto' })
}

watch(
  () => [messages.value.length, typing.value],
  async () => {
    await nextTick()
    scrollToEnd()
  },
)

// ---- Lifecycle ---------------------------------------------------------------------------------
onMounted(() => {
  init()
  showDot.value = !hasSeenChat()
  // Phones in portrait, and phones in landscape (too short for a floating window), go full-screen.
  mq = window.matchMedia('(max-width: 639px), (max-height: 520px)')
  fullScreen.value = mq.matches
  mq.addEventListener('change', onMediaChange)
  window.visualViewport?.addEventListener('resize', syncViewport)
  window.visualViewport?.addEventListener('scroll', syncViewport)
})

onBeforeUnmount(() => {
  mq?.removeEventListener('change', onMediaChange)
  window.visualViewport?.removeEventListener('resize', syncViewport)
  window.visualViewport?.removeEventListener('scroll', syncViewport)
  lockScroll(false)
})
</script>

<template>
  <div class="smc-root" @keydown="onKeydown">
    <Transition name="smc-pop">
      <section
        v-if="open"
        ref="panel"
        class="smc-panel"
        :class="{ 'smc-panel--full': fullScreen }"
        role="dialog"
        :aria-modal="fullScreen ? 'true' : 'false'"
        aria-labelledby="smc-title"
        tabindex="-1"
      >
        <header class="smc-header">
          <img v-if="!logoFailed" :src="staticAsset('images/logo.png')" alt="" class="smc-logo" width="40" height="40" @error="logoFailed = true">
          <span v-else class="smc-logo smc-logo--fallback" aria-hidden="true">SM</span>
          <div class="smc-header-text">
            <h2 id="smc-title" class="smc-title">SMACON Assistant</h2>
            <p class="smc-status"><span class="smc-online" aria-hidden="true" /> Online · St. Mark's College Namagoma</p>
          </div>
          <button type="button" class="smc-icon-btn" aria-label="Start a new chat" title="Start a new chat" @click="confirmingReset = !confirmingReset">
            <svg aria-hidden="true" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 15.5-6.2L21 8M21 3v5h-5M21 12a9 9 0 0 1-15.5 6.2L3 16M3 21v-5h5" /></svg>
          </button>
          <button type="button" class="smc-icon-btn" aria-label="Close chat" title="Close chat" @click="closeChat()">
            <svg aria-hidden="true" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18" /></svg>
          </button>
        </header>

        <div v-if="confirmingReset" class="smc-confirm" role="alertdialog" aria-label="Start a new chat?">
          <span>Erase this conversation and start again?</span>
          <button type="button" class="smc-confirm-btn smc-confirm-btn--primary" @click="doReset">Yes, erase</button>
          <button type="button" class="smc-confirm-btn" @click="confirmingReset = false">Cancel</button>
        </div>

        <div ref="log" class="smc-log" role="log" aria-live="polite" aria-relevant="additions" aria-label="Conversation">
          <ChatMessage
            v-for="(m, i) in messages"
            :key="m.id"
            :message="m"
            :show-suggestions="m.role === 'bot' && i === messages.length - 1 && !typing"
            :disabled="typing"
            @pick="pick"
            @navigate="onNavigate"
          />
          <div v-if="typing" class="smc-typing" role="status" aria-label="The assistant is typing">
            <span /><span /><span />
          </div>
        </div>

        <form class="smc-form" @submit.prevent="submit">
          <label for="smc-input" class="smc-sr">Type your question</label>
          <input
            id="smc-input"
            ref="input"
            v-model="draft"
            class="smc-input"
            type="text"
            :maxlength="MAX_INPUT_LENGTH"
            placeholder="Type your question…"
            autocomplete="off"
            autocapitalize="sentences"
            enterkeyhint="send"
          >
          <button type="submit" class="smc-send" :disabled="!draft.trim() || typing" aria-label="Send message">
            <svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z" /></svg>
          </button>
        </form>
      </section>
    </Transition>

    <button
      ref="launcher"
      type="button"
      class="smc-launcher"
      :class="{ 'smc-launcher--hidden-on-phone': open && fullScreen }"
      :aria-expanded="open"
      :aria-label="open ? 'Close chat assistant' : 'Open chat assistant'"
      title="Chat with the school assistant"
      @click="toggle"
    >
      <svg v-if="!open" aria-hidden="true" viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.4 8.4 0 0 1-8.5 8.3 8.7 8.7 0 0 1-3.7-.8L3 21l1.9-5.1A8.1 8.1 0 0 1 4 11.5 8.4 8.4 0 0 1 12.5 3.2 8.4 8.4 0 0 1 21 11.5z" /><path d="M8.5 11.5h.01M12.5 11.5h.01M16.5 11.5h.01" stroke-width="2.6" /></svg>
      <svg v-else aria-hidden="true" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round"><path d="M6 9l6 6 6-6" /></svg>
      <span v-if="showDot && !open" class="smc-dot" aria-hidden="true" />
      <span v-if="showDot && !open" class="smc-sr">New: ask our assistant a question</span>
    </button>
  </div>
</template>

<style scoped>
/* All rules are scoped to this component (and its root class), so nothing here can touch the
   rest of the website. The launcher (30) and window (35) sit above page content but below the mobile menu (40)
   and sticky header (50), so the open menu covers them; the full-screen phone panel is modal (120). */
.smc-root {
  --smc-navy: #172554;
  --smc-navy-dark: #0f1a3d;
  --smc-gold: #facc15;
  font-family: inherit;
  color: #111827;
}

/* ---- Launcher -------------------------------------------------------------------------------- */
.smc-launcher {
  position: fixed;
  right: max(1.5rem, env(safe-area-inset-right));
  bottom: max(1.5rem, env(safe-area-inset-bottom));
  z-index: 30;
  width: 3.5rem; height: 3.5rem;
  display: flex; align-items: center; justify-content: center;
  border-radius: 999px; border: 2px solid var(--smc-gold);
  background: var(--smc-navy); color: #fff;
  box-shadow: 0 8px 20px rgb(15 26 61 / 0.35);
  cursor: pointer;
  transition: transform 0.2s ease, background-color 0.2s ease;
}
.smc-launcher:hover { transform: scale(1.06); background: var(--smc-navy-dark); }
.smc-launcher:focus-visible { outline: 3px solid var(--smc-gold); outline-offset: 3px; }
.smc-launcher--hidden-on-phone { display: none; }

.smc-dot {
  position: absolute; top: -0.2rem; right: -0.2rem;
  width: 0.9rem; height: 0.9rem; border-radius: 999px;
  background: #dc2626; border: 2px solid #fff;
  animation: smc-ping 1.6s ease-out 3;
}
@keyframes smc-ping {
  0% { box-shadow: 0 0 0 0 rgb(220 38 38 / 0.55); }
  100% { box-shadow: 0 0 0 10px rgb(220 38 38 / 0); }
}

/* ---- Panel ----------------------------------------------------------------------------------- */
.smc-panel {
  position: fixed;
  right: max(1.5rem, env(safe-area-inset-right));
  bottom: calc(max(1.5rem, env(safe-area-inset-bottom)) + 4.5rem);
  z-index: 35;
  width: min(27rem, calc(100vw - 2rem));
  /* never taller than the space under the site header, so the navigation stays reachable */
  height: min(37.5rem, calc(100dvh - 15rem));
  display: flex; flex-direction: column;
  background: #f9fafb;
  border-radius: 1rem; overflow: hidden;
  box-shadow: 0 20px 45px rgb(15 26 61 / 0.3), 0 0 0 1px rgb(0 0 0 / 0.06);
  outline: none;
  overscroll-behavior: contain;
}
/* Phones: fill the screen (sized to the visual viewport so the keyboard never covers the input). */
.smc-panel--full {
  z-index: 120; /* modal: above everything, including Home's floating eCampus button (z-100) */
  inset: auto; left: 0; right: 0;
  top: var(--smc-top, 0px);
  width: 100%;
  height: var(--smc-h, 100dvh);
  border-radius: 0;
  box-shadow: none;
}

.smc-header {
  flex: none; display: flex; align-items: center; gap: 0.625rem;
  padding: 0.75rem 0.75rem 0.75rem 1rem;
  background: var(--smc-navy); color: #fff;
  border-bottom: 3px solid var(--smc-gold);
}
.smc-panel--full .smc-header { padding-top: max(0.75rem, env(safe-area-inset-top)); }
.smc-logo { flex: none; width: 2.5rem; height: 2.5rem; border-radius: 999px; background: #fff; object-fit: contain; border: 2px solid var(--smc-gold); }
.smc-logo--fallback { display: flex; align-items: center; justify-content: center; color: var(--smc-navy); font-weight: 800; font-size: 0.95rem; }
.smc-header-text { flex: 1; min-width: 0; }
.smc-title { margin: 0; font-size: 1.1875rem; font-weight: 700; line-height: 1.2; color: #fff; }
.smc-status { margin: 0.15rem 0 0; font-size: 0.875rem; color: #dbe4ff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.smc-online { display: inline-block; width: 0.5rem; height: 0.5rem; margin-right: 0.25rem; border-radius: 999px; background: #4ade80; }

.smc-icon-btn {
  flex: none; width: 2.25rem; height: 2.25rem; display: flex; align-items: center; justify-content: center;
  border: 0; border-radius: 999px; background: transparent; color: #fff; cursor: pointer;
}
.smc-icon-btn:hover { background: rgb(255 255 255 / 0.15); }
.smc-icon-btn:focus-visible { outline: 3px solid var(--smc-gold); outline-offset: 1px; }

.smc-confirm {
  flex: none; display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem;
  padding: 0.625rem 1rem; background: #fef9c3; color: #422006; font-size: 1rem;
  border-bottom: 1px solid #facc15;
}
.smc-confirm span { flex: 1 1 100%; }
.smc-confirm-btn { padding: 0.375rem 0.75rem; border-radius: 0.5rem; border: 1px solid var(--smc-navy); background: #fff; color: var(--smc-navy); font: inherit; font-size: 0.9375rem; font-weight: 600; cursor: pointer; }
.smc-confirm-btn--primary { background: var(--smc-navy); color: #fff; }
.smc-confirm-btn:focus-visible { outline: 3px solid var(--smc-navy); outline-offset: 2px; }

.smc-log {
  flex: 1; min-height: 0; overflow-y: auto; overscroll-behavior: contain;
  display: flex; flex-direction: column; gap: 0.75rem;
  padding: 1rem;
}

.smc-typing { align-self: flex-start; display: flex; gap: 0.3rem; padding: 0.75rem 0.875rem; background: #fff; border: 1px solid #e5e7eb; border-radius: 1rem; border-top-left-radius: 0.25rem; }
.smc-typing span { width: 0.4375rem; height: 0.4375rem; border-radius: 999px; background: #6b7280; animation: smc-bounce 1s infinite ease-in-out; }
.smc-typing span:nth-child(2) { animation-delay: 0.15s; }
.smc-typing span:nth-child(3) { animation-delay: 0.3s; }
@keyframes smc-bounce {
  0%, 60%, 100% { transform: translateY(0); opacity: 0.5; }
  30% { transform: translateY(-4px); opacity: 1; }
}

.smc-form {
  flex: none; display: flex; align-items: center; gap: 0.5rem;
  padding: 0.625rem 0.75rem 0.75rem; background: #fff; border-top: 1px solid #e5e7eb;
}
.smc-input {
  flex: 1; min-width: 0; box-sizing: border-box;
  height: 2.75rem; padding: 0 0.875rem;
  border: 1px solid #9ca3af; border-radius: 999px; background: #fff; color: #111827;
  font: inherit; font-size: 1.125rem; /* 16px: stops iOS Safari zooming the page on focus */
}
.smc-input::placeholder { color: #6b7280; }
.smc-input:focus-visible { outline: 3px solid var(--smc-navy); outline-offset: 1px; border-color: var(--smc-navy); }
.smc-send {
  flex: none; width: 2.75rem; height: 2.75rem; display: flex; align-items: center; justify-content: center;
  border: 0; border-radius: 999px; background: var(--smc-gold); color: var(--smc-navy-dark); cursor: pointer;
}
.smc-send:hover:not(:disabled) { background: #eab308; }
.smc-send:focus-visible { outline: 3px solid var(--smc-navy); outline-offset: 2px; }
.smc-send:disabled { opacity: 0.45; cursor: not-allowed; }


/* Phones: keep the input clear of the home indicator */
.smc-panel--full .smc-form { padding-bottom: max(0.75rem, env(safe-area-inset-bottom)); }

.smc-sr { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }

/* Phones: keep the input clear of the home indicator */

/* ---- Open / close animation ------------------------------------------------------------------ */
.smc-pop-enter-active, .smc-pop-leave-active { transition: opacity 0.18s ease, transform 0.18s ease; }
.smc-pop-enter-from, .smc-pop-leave-to { opacity: 0; transform: translateY(10px) scale(0.98); }

@media (prefers-reduced-motion: reduce) {
  .smc-pop-enter-active, .smc-pop-leave-active, .smc-launcher { transition: none; }
  .smc-dot, .smc-typing span { animation: none; }
  .smc-typing span { opacity: 0.8; }
}

</style>
