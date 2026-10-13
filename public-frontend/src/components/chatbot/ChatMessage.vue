<script setup lang="ts">
/**
 * One message bubble. Text is always rendered through Vue's {{ }} interpolation (HTML-escaped),
 * and every link is re-checked against the allow-list in chatbot/links.ts before it is drawn.
 */
import { computed } from 'vue'
import { classifyLink, type ChatLink, type LinkKind } from '../../chatbot/links'
import type { ChatMessage } from '../../chatbot/storage'

const props = defineProps<{
  message: ChatMessage
  /** Only the newest bot message shows its suggestion buttons. */
  showSuggestions: boolean
  disabled: boolean
}>()

const emit = defineEmits<{
  (e: 'pick', query: string): void
  (e: 'navigate'): void
}>()

const time = computed(() => {
  const d = new Date(props.message.time)
  return Number.isNaN(d.getTime()) ? '' : d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' })
})

const links = computed(() =>
  (props.message.links ?? [])
    .map((l: ChatLink) => ({ ...l, kind: classifyLink(l.url) as LinkKind | null }))
    .filter((l): l is ChatLink & { kind: LinkKind } => l.kind !== null),
)
</script>

<template>
  <div class="smc-msg" :class="message.role === 'user' ? 'smc-msg--user' : 'smc-msg--bot'">
    <div class="smc-bubble">
      <span class="smc-sr">{{ message.role === 'user' ? 'You said:' : 'Assistant said:' }}</span>
      <p class="smc-text">{{ message.text }}</p>

      <ul v-if="links.length" class="smc-links" aria-label="Useful links">
        <li v-for="link in links" :key="link.url + link.label">
          <router-link v-if="link.kind === 'internal'" :to="link.url" class="smc-link" @click="emit('navigate')">{{ link.label }}</router-link>
          <a v-else-if="link.kind === 'external' || link.kind === 'file'" :href="link.url" target="_blank" rel="noopener noreferrer" class="smc-link">
            {{ link.label }}
            <svg aria-hidden="true" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M8 7h9v9" /></svg>
            <span class="smc-sr">(opens in a new tab)</span>
          </a>
          <a v-else :href="link.url" class="smc-link">{{ link.label }}</a>
        </li>
      </ul>
    </div>

    <p v-if="time" class="smc-time">{{ time }}</p>

    <div v-if="showSuggestions && message.suggestions?.length" class="smc-chips" role="group" aria-label="Suggested questions">
      <button v-for="s in message.suggestions" :key="s.label" type="button" class="smc-chip" :disabled="disabled" @click="emit('pick', s.query)">{{ s.label }}</button>
    </div>
  </div>
</template>

<style scoped>
.smc-msg { display: flex; flex-direction: column; max-width: 88%; }
.smc-msg--bot { align-self: flex-start; align-items: flex-start; }
.smc-msg--user { align-self: flex-end; align-items: flex-end; }

.smc-bubble { padding: 0.75rem 1rem; border-radius: 1rem; font-size: 1.125rem; line-height: 1.5; overflow-wrap: anywhere; }
.smc-msg--bot .smc-bubble { background: #fff; color: #111827; border: 1px solid #e5e7eb; border-top-left-radius: 0.25rem; }
.smc-msg--user .smc-bubble { background: #172554; color: #fff; border-top-right-radius: 0.25rem; }

.smc-text { margin: 0; white-space: pre-line; }

.smc-time { margin: 0.2rem 0.25rem 0; font-size: 0.8125rem; color: #4b5563; }

.smc-links { list-style: none; margin: 0.625rem 0 0; padding: 0; display: flex; flex-wrap: wrap; gap: 0.375rem; }
.smc-link {
  display: inline-flex; align-items: center; gap: 0.25rem;
  padding: 0.3125rem 0.625rem; border-radius: 999px;
  background: #fef9c3; color: #172554; border: 1px solid #facc15;
  font-size: 1rem; font-weight: 600; line-height: 1.2; text-decoration: none;
}
.smc-link:hover { background: #fde047; }
.smc-link:focus-visible { outline: 3px solid #172554; outline-offset: 2px; }

.smc-chips { display: flex; flex-wrap: wrap; gap: 0.375rem; margin-top: 0.5rem; }
.smc-chip {
  padding: 0.4375rem 0.75rem; border-radius: 999px; background: #fff; color: #172554;
  border: 1px solid #172554; font: inherit; font-size: 1rem; font-weight: 600; line-height: 1.2; cursor: pointer;
}
.smc-chip:hover:not(:disabled) { background: #eef2ff; }
.smc-chip:focus-visible { outline: 3px solid #172554; outline-offset: 2px; }
.smc-chip:disabled { opacity: 0.5; cursor: not-allowed; }

.smc-sr { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
</style>
