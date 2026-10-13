<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { api, apiErrorMessage } from '@/services/api'
import { useToast } from '@/composables/useToast'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'

interface Daily { date: string; messages: number; conversations: number }
interface TopQuestion { question: string; total: number; answered: number }
interface Unanswered { question: string; total: number; last_asked: string }
interface Topic { topic: string; total: number }
interface Hour { hour: number; total: number }
interface Device { device: string; total: number }
interface Summary {
  from: string
  to: string
  visitors: number
  new_visitors: number
  returning_visitors: number
  conversations: number
  messages: number
  unanswered: number
  answered_percent: number | null
  spelling_fixed: number
  avg_messages_per_conversation: number
  devices: Device[]
  daily: Daily[]
  top_questions: TopQuestion[]
  unanswered_questions: Unanswered[]
  by_topic: Topic[]
  by_hour: Hour[]
  retention_days: number
}
interface Pagination { page: number; limit: number; total: number; pages: number }
interface Conversation {
  id: number
  visitor_uid: string
  device: string
  page_path: string | null
  started_date: string
  message_count: number
  unanswered_count: number
  last_message_at: string | null
  first_question: string | null
}
interface MessageRow {
  id: number
  conversation_id: number
  question: string
  answer_kind: string
  answer_ref: string | null
  answered: number
  spelling_fixed: number
  answer_preview: string | null
  created_at: string
  device: string
  page_path: string | null
}
interface TranscriptMessage {
  id: number
  question: string
  answer_kind: string
  answer_ref: string | null
  answered: number
  spelling_fixed: number
  answer_preview: string | null
  created_at: string
}

const toast = useToast()
const tab = ref<'overview' | 'conversations' | 'messages'>('overview')
const loading = ref(false)
const summary = ref<Summary | null>(null)

const conversations = ref<Conversation[]>([])
const convPagination = ref<Pagination>({ page: 1, limit: 20, total: 0, pages: 1 })
const messages = ref<MessageRow[]>([])
const msgPagination = ref<Pagination>({ page: 1, limit: 25, total: 0, pages: 1 })
const msgFilter = ref<'all' | 'answered' | 'unanswered'>('all')
const msgSearch = ref('')

const transcript = ref<{ conversation: Conversation; messages: TranscriptMessage[] } | null>(null)
const transcriptLoading = ref(false)

const confirmDelete = ref<{ open: boolean; id: number | null }>({ open: false, id: null })
const purgeDays = ref(180)
const confirmPurge = ref(false)

function isoDaysAgo(n: number): string {
  const d = new Date()
  d.setDate(d.getDate() - n)
  return d.toISOString().slice(0, 10)
}
const from = ref(isoDaysAgo(29))
const to = ref(isoDaysAgo(0))

const maxDaily = computed(() => Math.max(1, ...(summary.value?.daily.map((d) => d.messages) ?? [0])))
const maxTopic = computed(() => Math.max(1, ...(summary.value?.by_topic.map((t) => t.total) ?? [0])))
const maxHour = computed(() => Math.max(1, ...(summary.value?.by_hour.map((h) => h.total) ?? [0])))
const maxDevice = computed(() => Math.max(1, ...(summary.value?.devices.map((d) => d.total) ?? [0])))

const KIND_LABELS: Record<string, string> = {
  answer: 'Answered', greeting: 'Greeting', thanks: 'Thanks', goodbye: 'Goodbye', help: 'Help', identity: 'About the bot',
  clarify: 'Asked to clarify', fallback: 'Not answered', privacy: 'Private info hidden', ack: 'Acknowledged',
}
function kindLabel(k: string): string {
  return KIND_LABELS[k] ?? k
}
function kindClass(k: string): string {
  if (k === 'fallback') return 'bg-red-50 text-red-700 ring-1 ring-red-600/20'
  if (k === 'clarify') return 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20'
  if (k === 'answer') return 'bg-green-50 text-green-700 ring-1 ring-green-600/20'
  if (k === 'privacy') return 'bg-purple-50 text-purple-700 ring-1 ring-purple-600/20'
  return 'bg-gray-100 text-gray-600 ring-1 ring-gray-500/20'
}
function topicLabel(t: string): string {
  return t.replace(/[-_]/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase())
}
function shortId(id: string): string {
  return id.slice(0, 6)
}
function formatDate(date: string): string {
  return new Date(date + 'T00:00:00').toLocaleDateString(undefined, { month: 'short', day: 'numeric' })
}
function formatDateTime(dt: string | null): string {
  return dt ? new Date(dt.replace(' ', 'T')).toLocaleString(undefined, { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—'
}
function hourLabel(h: number): string {
  return `${h % 12 === 0 ? 12 : h % 12}${h < 12 ? 'a' : 'p'}`
}

async function fetchSummary() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/chatbot/summary', { params: { from: from.value, to: to.value } })
    summary.value = data.data
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to load chatbot analytics'))
  } finally {
    loading.value = false
  }
}

async function fetchConversations(page = 1) {
  try {
    const { data } = await api.get('/admin/chatbot/conversations', { params: { from: from.value, to: to.value, page, limit: convPagination.value.limit } })
    conversations.value = data.data.data
    convPagination.value = data.data.pagination
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to load conversations'))
  }
}

async function fetchMessages(page = 1) {
  try {
    const { data } = await api.get('/admin/chatbot/messages', {
      params: { from: from.value, to: to.value, filter: msgFilter.value, q: msgSearch.value || undefined, page, limit: msgPagination.value.limit },
    })
    messages.value = data.data.data
    msgPagination.value = data.data.pagination
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to load messages'))
  }
}

function applyRange() {
  fetchSummary()
  if (tab.value === 'conversations') fetchConversations(1)
  if (tab.value === 'messages') fetchMessages(1)
}

function openTab(t: typeof tab.value) {
  tab.value = t
  if (t === 'conversations') fetchConversations(1)
  if (t === 'messages') fetchMessages(1)
}

async function openTranscript(id: number) {
  transcriptLoading.value = true
  transcript.value = null
  try {
    const { data } = await api.get(`/admin/chatbot/conversations/${id}`)
    transcript.value = data.data
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to load the conversation'))
  } finally {
    transcriptLoading.value = false
  }
}

function askDelete(id: number) {
  confirmDelete.value = { open: true, id }
}
async function doDelete() {
  const id = confirmDelete.value.id
  confirmDelete.value = { open: false, id: null }
  if (id === null) return
  try {
    await api.delete(`/admin/chatbot/conversations/${id}`)
    toast.success('Conversation deleted')
    transcript.value = null
    fetchSummary()
    fetchConversations(convPagination.value.page)
    if (tab.value === 'messages') fetchMessages(msgPagination.value.page)
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to delete the conversation'))
  }
}

async function doPurge() {
  confirmPurge.value = false
  try {
    const { data } = await api.post('/admin/chatbot/purge', { days: purgeDays.value })
    toast.success(`Deleted ${data.data.deleted} old conversation(s)`)
    fetchSummary()
    if (tab.value === 'conversations') fetchConversations(1)
    if (tab.value === 'messages') fetchMessages(1)
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to delete old conversations'))
  }
}

async function exportCsv() {
  try {
    const res = await api.get('/admin/chatbot/export', { params: { from: from.value, to: to.value }, responseType: 'blob' })
    const url = URL.createObjectURL(res.data as Blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `chatbot-messages_${from.value}_to_${to.value}.csv`
    a.click()
    URL.revokeObjectURL(url)
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to export'))
  }
}

function printReport() {
  window.print()
}

onMounted(fetchSummary)
</script>

<template>
  <div class="p-6 sm:p-8 space-y-8 max-w-6xl mx-auto">
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Chatbot Analytics</h1>
        <p class="mt-1 text-sm text-gray-500">Who uses the website assistant, what they ask, and what it couldn't answer.</p>
      </div>
      <div class="no-print flex flex-wrap items-center gap-3">
        <label class="flex items-center gap-1.5 text-xs font-semibold text-gray-500">
          From
          <input v-model="from" type="date" class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-sm shadow-sm focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20" @change="applyRange">
        </label>
        <label class="flex items-center gap-1.5 text-xs font-semibold text-gray-500">
          To
          <input v-model="to" type="date" class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-sm shadow-sm focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20" @change="applyRange">
        </label>
        <button type="button" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50" @click="exportCsv">Export CSV</button>
        <button type="button" class="rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800" @click="printReport">Print Report</button>
      </div>
    </div>

    <div class="no-print flex gap-1 border-b border-gray-200" role="tablist">
      <button
        v-for="t in (['overview', 'conversations', 'messages'] as const)"
        :key="t"
        type="button"
        role="tab"
        :aria-selected="tab === t"
        class="-mb-px border-b-2 px-4 py-2.5 text-sm font-semibold capitalize transition"
        :class="tab === t ? 'border-blue-800 text-blue-900' : 'border-transparent text-gray-500 hover:text-gray-800'"
        @click="openTab(t)"
      >{{ t }}</button>
    </div>

    <div v-if="loading && !summary" class="flex items-center justify-center gap-2 py-16 text-gray-400">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" /></svg>
      <span class="text-sm">Loading chatbot analytics...</span>
    </div>

    <!-- ===================================================== OVERVIEW -->
    <template v-else-if="summary && tab === 'overview'">
      <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">People who chatted</p>
          <p class="mt-2 text-2xl font-bold text-gray-900">{{ summary.visitors.toLocaleString() }}</p>
          <p class="mt-1 text-xs text-gray-500">{{ summary.new_visitors }} new · {{ summary.returning_visitors }} returning</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Conversations</p>
          <p class="mt-2 text-2xl font-bold text-gray-900">{{ summary.conversations.toLocaleString() }}</p>
          <p class="mt-1 text-xs text-gray-500">{{ summary.avg_messages_per_conversation }} messages each on average</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Messages sent</p>
          <p class="mt-2 text-2xl font-bold text-gray-900">{{ summary.messages.toLocaleString() }}</p>
          <p class="mt-1 text-xs text-gray-500">{{ summary.spelling_fixed }} had spelling corrected</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Answered</p>
          <p class="mt-2 text-2xl font-bold" :class="summary.answered_percent !== null && summary.answered_percent < 70 ? 'text-amber-600' : 'text-green-600'">
            {{ summary.answered_percent === null ? '—' : summary.answered_percent + '%' }}
          </p>
          <p class="mt-1 text-xs text-gray-500">{{ summary.unanswered }} not answered</p>
        </div>
      </div>

      <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 sm:p-8">
        <h2 class="mb-6 text-lg font-bold text-gray-900">Messages per day</h2>
        <div v-if="summary.messages === 0" class="py-10 text-center text-sm text-gray-400">No chatbot messages in this range yet.</div>
        <template v-else>
          <div class="flex h-48 items-end gap-1 overflow-x-auto pb-1">
            <div
              v-for="p in summary.daily"
              :key="p.date"
              class="group relative flex h-full min-w-[6px] flex-1 flex-col items-center justify-end"
              :title="`${formatDate(p.date)}: ${p.messages} messages in ${p.conversations} conversations`"
            >
              <span class="mb-1 text-[10px] font-semibold text-gray-500 opacity-0 transition group-hover:opacity-100">{{ p.messages }}</span>
              <div class="w-full rounded-t-sm bg-blue-800 transition group-hover:bg-blue-600" :style="{ height: `${(p.messages / maxDaily) * 100}%`, minHeight: p.messages > 0 ? '2px' : '0' }"></div>
            </div>
          </div>
          <div class="mt-2 flex justify-between text-xs text-gray-400">
            <span>{{ formatDate(summary.daily[0].date) }}</span>
            <span>{{ formatDate(summary.daily[summary.daily.length - 1].date) }}</span>
          </div>
        </template>
      </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
          <h2 class="mb-1 text-lg font-bold text-gray-900">Most asked questions</h2>
          <p class="mb-4 text-xs text-gray-500">Small talk (hello, thanks) is left out.</p>
          <p v-if="!summary.top_questions.length" class="py-6 text-center text-sm text-gray-400">Nothing yet.</p>
          <ol v-else class="space-y-2.5">
            <li v-for="(q, i) in summary.top_questions" :key="q.question" class="flex items-start gap-3 text-sm">
              <span class="w-5 shrink-0 text-right text-xs font-semibold text-gray-400">{{ i + 1 }}</span>
              <span class="min-w-0 flex-1 break-words text-gray-800">{{ q.question }}</span>
              <span v-if="q.answered < q.total" class="shrink-0 rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700 ring-1 ring-amber-600/20" title="Some of these were not answered">{{ q.total - q.answered }} missed</span>
              <span class="w-10 shrink-0 text-right font-semibold text-gray-900">{{ q.total }}</span>
            </li>
          </ol>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-2 ring-red-100">
          <h2 class="mb-1 text-lg font-bold text-gray-900">Questions it couldn't answer</h2>
          <p class="mb-4 text-xs text-gray-500">Add answers for these (in the chatbot's knowledge or by publishing the information on the site) to make it more useful.</p>
          <p v-if="!summary.unanswered_questions.length" class="py-6 text-center text-sm text-gray-400">Nothing missed in this range.</p>
          <ol v-else class="space-y-2.5">
            <li v-for="(q, i) in summary.unanswered_questions" :key="q.question" class="flex items-start gap-3 text-sm">
              <span class="w-5 shrink-0 text-right text-xs font-semibold text-gray-400">{{ i + 1 }}</span>
              <span class="min-w-0 flex-1 break-words text-gray-800">{{ q.question }}</span>
              <span class="w-10 shrink-0 text-right font-semibold text-red-700">{{ q.total }}×</span>
            </li>
          </ol>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
          <h2 class="mb-4 text-lg font-bold text-gray-900">Topics people ask about</h2>
          <p v-if="!summary.by_topic.length" class="py-6 text-center text-sm text-gray-400">Nothing yet.</p>
          <div v-else class="space-y-3">
            <div v-for="t in summary.by_topic" :key="t.topic" class="flex items-center gap-3">
              <p class="w-40 shrink-0 truncate text-sm font-medium text-gray-700">{{ topicLabel(t.topic) }}</p>
              <div class="h-3 flex-1 overflow-hidden rounded-full bg-gray-100"><div class="h-full rounded-full bg-blue-800" :style="{ width: `${(t.total / maxTopic) * 100}%` }"></div></div>
              <p class="w-10 shrink-0 text-right text-sm font-semibold text-gray-900">{{ t.total }}</p>
            </div>
          </div>
        </div>

        <div class="space-y-6">
          <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <h2 class="mb-4 text-lg font-bold text-gray-900">When people chat</h2>
            <div class="flex h-24 items-end gap-0.5">
              <div v-for="h in summary.by_hour" :key="h.hour" class="group flex h-full flex-1 flex-col items-center justify-end" :title="`${hourLabel(h.hour)}: ${h.total} messages`">
                <div class="w-full rounded-t-sm bg-blue-800 transition group-hover:bg-blue-600" :style="{ height: `${(h.total / maxHour) * 100}%`, minHeight: h.total > 0 ? '2px' : '0' }"></div>
              </div>
            </div>
            <div class="mt-1 flex justify-between text-[10px] text-gray-400"><span>12a</span><span>6a</span><span>12p</span><span>6p</span><span>11p</span></div>
          </div>
          <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <h2 class="mb-4 text-lg font-bold text-gray-900">Devices</h2>
            <p v-if="!summary.devices.length" class="text-sm text-gray-400">Nothing yet.</p>
            <div v-else class="space-y-3">
              <div v-for="d in summary.devices" :key="d.device" class="flex items-center gap-3">
                <p class="w-24 shrink-0 text-sm font-medium capitalize text-gray-700">{{ d.device }}</p>
                <div class="h-3 flex-1 overflow-hidden rounded-full bg-gray-100"><div class="h-full rounded-full bg-blue-800" :style="{ width: `${(d.total / maxDevice) * 100}%` }"></div></div>
                <p class="w-10 shrink-0 text-right text-sm font-semibold text-gray-900">{{ d.total }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="no-print rounded-xl bg-gray-50 p-5 ring-1 ring-gray-200">
        <h2 class="text-sm font-bold text-gray-800">Privacy &amp; data</h2>
        <p class="mt-1 text-xs leading-relaxed text-gray-600">
          Visitors are only a random code made in their own browser - no names, emails or IP addresses are stored. Emails and phone-like numbers in questions are blanked out before saving,
          and messages that look like passwords or card numbers are never sent. Visitors with "Do Not Track" turned on aren't recorded. Conversations are deleted automatically after {{ summary.retention_days }} days.
        </p>
        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-gray-600">
          Delete conversations older than
          <select v-model.number="purgeDays" class="rounded-md border border-gray-300 px-2 py-1 text-xs">
            <option :value="30">30 days</option><option :value="90">90 days</option><option :value="180">180 days</option><option :value="365">1 year</option>
          </select>
          <button type="button" class="rounded-md border border-red-300 bg-white px-3 py-1 text-xs font-semibold text-red-700 transition hover:bg-red-50" @click="confirmPurge = true">Delete now</button>
        </div>
      </div>
    </template>

    <!-- ===================================================== CONVERSATIONS -->
    <template v-else-if="tab === 'conversations'">
      <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        <table class="min-w-full text-left text-sm">
          <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
            <tr><th class="px-4 py-3">Visitor</th><th class="px-4 py-3">First question</th><th class="px-4 py-3 text-right">Messages</th><th class="px-4 py-3 text-right">Missed</th><th class="px-4 py-3">Device</th><th class="px-4 py-3">Page</th><th class="px-4 py-3">Last activity</th><th class="px-4 py-3"></th></tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="!conversations.length"><td colspan="8" class="px-4 py-10 text-center text-gray-400">No conversations in this range.</td></tr>
            <tr v-for="c in conversations" :key="c.id" class="cursor-pointer hover:bg-gray-50" @click="openTranscript(c.id)">
              <td class="px-4 py-3 font-mono text-xs text-gray-500">#{{ shortId(c.visitor_uid) }}</td>
              <td class="max-w-xs px-4 py-3 text-gray-800"><span class="line-clamp-2 break-words">{{ c.first_question ?? '—' }}</span></td>
              <td class="px-4 py-3 text-right font-semibold">{{ c.message_count }}</td>
              <td class="px-4 py-3 text-right" :class="c.unanswered_count ? 'font-semibold text-red-700' : 'text-gray-400'">{{ c.unanswered_count }}</td>
              <td class="px-4 py-3 capitalize text-gray-600">{{ c.device }}</td>
              <td class="px-4 py-3 text-gray-600">{{ c.page_path ?? '—' }}</td>
              <td class="whitespace-nowrap px-4 py-3 text-gray-600">{{ formatDateTime(c.last_message_at) }}</td>
              <td class="px-4 py-3 text-right"><button type="button" class="text-xs font-semibold text-blue-800 hover:underline" @click.stop="openTranscript(c.id)">View</button></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="convPagination.pages > 1" class="no-print flex items-center justify-between text-sm text-gray-600">
        <span>Page {{ convPagination.page }} of {{ convPagination.pages }} · {{ convPagination.total }} conversations</span>
        <div class="flex gap-2">
          <button type="button" class="rounded-md border border-gray-300 px-3 py-1.5 font-semibold disabled:opacity-40" :disabled="convPagination.page <= 1" @click="fetchConversations(convPagination.page - 1)">Previous</button>
          <button type="button" class="rounded-md border border-gray-300 px-3 py-1.5 font-semibold disabled:opacity-40" :disabled="convPagination.page >= convPagination.pages" @click="fetchConversations(convPagination.page + 1)">Next</button>
        </div>
      </div>
    </template>

    <!-- ===================================================== MESSAGES -->
    <template v-else-if="tab === 'messages'">
      <div class="no-print flex flex-wrap items-center gap-3">
        <select v-model="msgFilter" class="rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm" @change="fetchMessages(1)">
          <option value="all">All messages</option><option value="unanswered">Not answered only</option><option value="answered">Answered only</option>
        </select>
        <input v-model="msgSearch" type="search" placeholder="Search questions…" class="w-64 rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm" @keydown.enter="fetchMessages(1)">
        <button type="button" class="rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white" @click="fetchMessages(1)">Search</button>
      </div>
      <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        <table class="min-w-full text-left text-sm">
          <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
            <tr><th class="px-4 py-3">When</th><th class="px-4 py-3">Question</th><th class="px-4 py-3">Result</th><th class="px-4 py-3">Topic</th><th class="px-4 py-3">Device</th><th class="px-4 py-3"></th></tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="!messages.length"><td colspan="6" class="px-4 py-10 text-center text-gray-400">No messages match.</td></tr>
            <tr v-for="m in messages" :key="m.id" class="hover:bg-gray-50">
              <td class="whitespace-nowrap px-4 py-3 text-gray-600">{{ formatDateTime(m.created_at) }}</td>
              <td class="max-w-md px-4 py-3 text-gray-800">
                <span class="break-words">{{ m.question }}</span>
                <span v-if="m.spelling_fixed" class="ml-1.5 rounded bg-blue-50 px-1.5 py-0.5 text-[10px] font-semibold text-blue-700" title="The bot corrected the spelling">spelling fixed</span>
              </td>
              <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="kindClass(m.answer_kind)">{{ kindLabel(m.answer_kind) }}</span></td>
              <td class="px-4 py-3 text-gray-600">{{ m.answer_ref ? topicLabel(m.answer_ref) : '—' }}</td>
              <td class="px-4 py-3 capitalize text-gray-600">{{ m.device }}</td>
              <td class="px-4 py-3 text-right"><button type="button" class="text-xs font-semibold text-blue-800 hover:underline" @click="openTranscript(m.conversation_id)">Conversation</button></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="msgPagination.pages > 1" class="no-print flex items-center justify-between text-sm text-gray-600">
        <span>Page {{ msgPagination.page }} of {{ msgPagination.pages }} · {{ msgPagination.total }} messages</span>
        <div class="flex gap-2">
          <button type="button" class="rounded-md border border-gray-300 px-3 py-1.5 font-semibold disabled:opacity-40" :disabled="msgPagination.page <= 1" @click="fetchMessages(msgPagination.page - 1)">Previous</button>
          <button type="button" class="rounded-md border border-gray-300 px-3 py-1.5 font-semibold disabled:opacity-40" :disabled="msgPagination.page >= msgPagination.pages" @click="fetchMessages(msgPagination.page + 1)">Next</button>
        </div>
      </div>
    </template>

    <!-- ===================================================== TRANSCRIPT -->
    <div v-if="transcript || transcriptLoading" class="no-print fixed inset-0 z-40 flex items-center justify-center bg-black/40 p-4" role="dialog" aria-modal="true" aria-label="Conversation" @click.self="transcript = null" @keydown.esc="transcript = null">
      <div class="flex max-h-[85vh] w-full max-w-2xl flex-col rounded-xl bg-white shadow-xl">
        <div class="flex items-start justify-between gap-4 border-b border-gray-200 p-5">
          <div v-if="transcript">
            <h2 class="text-lg font-bold text-gray-900">Conversation #{{ shortId(transcript.conversation.visitor_uid) }}</h2>
            <p class="mt-0.5 text-xs text-gray-500">{{ formatDateTime(transcript.conversation.last_message_at) }} · {{ transcript.conversation.device }} · {{ transcript.conversation.page_path ?? 'page unknown' }}</p>
          </div>
          <p v-else class="text-sm text-gray-500">Loading…</p>
          <button type="button" class="rounded-md p-1.5 text-gray-500 hover:bg-gray-100" aria-label="Close" @click="transcript = null">✕</button>
        </div>
        <div v-if="transcript" class="flex-1 space-y-4 overflow-y-auto p-5">
          <div v-for="m in transcript.messages" :key="m.id" class="space-y-2">
            <div class="flex justify-end"><p class="max-w-[85%] break-words rounded-2xl rounded-tr-sm bg-blue-900 px-4 py-2 text-sm text-white">{{ m.question }}</p></div>
            <div class="flex items-start gap-2">
              <div class="max-w-[85%] rounded-2xl rounded-tl-sm bg-gray-100 px-4 py-2 text-sm text-gray-800">
                <p v-if="m.answer_preview" class="whitespace-pre-line break-words">{{ m.answer_preview }}</p>
                <p v-else class="italic text-gray-500">(reply not recorded)</p>
                <p class="mt-1.5 flex flex-wrap items-center gap-1.5 text-[11px]">
                  <span class="rounded-full px-2 py-0.5 font-semibold" :class="kindClass(m.answer_kind)">{{ kindLabel(m.answer_kind) }}</span>
                  <span v-if="m.answer_ref" class="text-gray-500">via {{ topicLabel(m.answer_ref) }}</span>
                  <span v-if="m.spelling_fixed" class="text-blue-700">spelling fixed</span>
                  <span class="text-gray-400">{{ formatDateTime(m.created_at) }}</span>
                </p>
              </div>
            </div>
          </div>
        </div>
        <div v-if="transcript" class="flex justify-end border-t border-gray-200 p-4">
          <button type="button" class="rounded-md border border-red-300 px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-50" @click="askDelete(transcript.conversation.id)">Delete this conversation</button>
        </div>
      </div>
    </div>

    <ConfirmDialog :open="confirmDelete.open" title="Delete conversation?" message="This permanently removes the conversation and all its messages." confirm-label="Delete" danger @confirm="doDelete" @cancel="confirmDelete = { open: false, id: null }" />
    <ConfirmDialog :open="confirmPurge" title="Delete old conversations?" :message="`Every conversation last active more than ${purgeDays} days ago will be permanently deleted.`" confirm-label="Delete" danger @confirm="doPurge" @cancel="confirmPurge = false" />
  </div>
</template>
