<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { api, apiErrorMessage } from '@/services/api'
import { useToast } from '@/composables/useToast'

interface DailyPoint {
  date: string
  total: number
}
interface TypeBreakdown {
  event_type: string
  total: number
}
interface Summary {
  total_events: number
  today_events: number
  failed_logins: number
  rate_limit_hits: number
  daily_totals: DailyPoint[]
  by_type: TypeBreakdown[]
}
interface SecurityEvent {
  id: number
  event_type: string
  ip_address: string | null
  user_agent: string | null
  identifier: string | null
  request_path: string | null
  details: string | null
  event_date: string
  created_at: string
}
interface Pagination {
  page: number
  limit: number
  total: number
  pages: number
}

const toast = useToast()
const loading = ref(false)
const loadingEvents = ref(false)
const summary = ref<Summary | null>(null)
const events = ref<SecurityEvent[]>([])
const pagination = ref<Pagination>({ page: 1, limit: 25, total: 0, pages: 1 })

function isoDaysAgo(n: number): string {
  const d = new Date()
  d.setDate(d.getDate() - n)
  return d.toISOString().slice(0, 10)
}

const from = ref(isoDaysAgo(29))
const to = ref(isoDaysAgo(0))
const eventType = ref('')

const EVENT_LABELS: Record<string, string> = {
  failed_login: 'Failed Login',
  rate_limit_exceeded: 'Rate Limit Exceeded',
}
function eventLabel(type: string): string {
  return EVENT_LABELS[type] ?? type.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase())
}
function eventBadgeClass(type: string): string {
  return type === 'failed_login' ? 'bg-red-50 text-red-700 ring-1 ring-red-600/20' : 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20'
}

const maxDaily = computed(() => Math.max(1, ...(summary.value?.daily_totals.map((d) => d.total) ?? [0])))
const maxByType = computed(() => Math.max(1, ...(summary.value?.by_type.map((t) => t.total) ?? [0])))
const periodTotal = computed(() => summary.value?.daily_totals.reduce((sum, d) => sum + d.total, 0) ?? 0)

function formatDate(date: string): string {
  return new Date(date + 'T00:00:00').toLocaleDateString(undefined, { month: 'short', day: 'numeric' })
}
function formatDateTime(dt: string): string {
  return new Date(dt.replace(' ', 'T')).toLocaleString(undefined, { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

async function fetchSummary() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/security-events/summary', { params: { from: from.value, to: to.value } })
    summary.value = data.data
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to load security summary'))
  } finally {
    loading.value = false
  }
}

async function fetchEvents(page = 1) {
  loadingEvents.value = true
  try {
    const { data } = await api.get('/admin/security-events', {
      params: { from: from.value, to: to.value, event_type: eventType.value || undefined, page, limit: pagination.value.limit },
    })
    events.value = data.data.data
    pagination.value = data.data.pagination
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to load security events'))
  } finally {
    loadingEvents.value = false
  }
}

function applyFilters() {
  fetchSummary()
  fetchEvents(1)
}

function printReport() {
  window.print()
}

onMounted(() => {
  fetchSummary()
  fetchEvents(1)
})
</script>

<template>
  <div class="p-6 sm:p-8 space-y-8 max-w-6xl mx-auto">
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Security Report</h1>
        <p class="mt-1 text-sm text-gray-500">Failed admin logins and rate-limit hits - people who tried to break in.</p>
      </div>
      <div class="no-print flex flex-wrap items-center gap-3">
        <label class="flex items-center gap-1.5 text-xs font-semibold text-gray-500">
          From
          <input v-model="from" type="date" class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-sm shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20" @change="applyFilters">
        </label>
        <label class="flex items-center gap-1.5 text-xs font-semibold text-gray-500">
          To
          <input v-model="to" type="date" class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-sm shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20" @change="applyFilters">
        </label>
        <select
          v-model="eventType"
          class="rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20"
          @change="applyFilters"
        >
          <option value="">All event types</option>
          <option value="failed_login">Failed Login</option>
          <option value="rate_limit_exceeded">Rate Limit Exceeded</option>
        </select>
        <button
          type="button"
          class="flex items-center gap-2 bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"
          @click="printReport"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.318 2.226c.075.526-.293 1.01-.824 1.06a80.196 80.196 0 01-11.508 0 1.014 1.014 0 01-.824-1.06L6.34 18m11.32 0h1.83a1.5 1.5 0 001.5-1.5v-4.5a1.5 1.5 0 00-1.5-1.5H4.5A1.5 1.5 0 003 12v4.5a1.5 1.5 0 001.5 1.5h1.83m11.32 0H6.34m0-6.5V4.653c0-.407.324-.74.73-.766a49.9 49.9 0 0110.855 0c.406.026.73.36.73.766V11.5" /></svg>
          Print Report
        </button>
      </div>
    </div>

    <div v-if="loading && !summary" class="flex items-center justify-center gap-2 py-16 text-gray-400">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" /></svg>
      <span class="text-sm">Loading security report...</span>
    </div>

    <template v-else-if="summary">
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-5">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Events in Range</p>
          <p class="mt-2 text-2xl font-bold text-gray-900">{{ periodTotal.toLocaleString() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-5">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Events Today</p>
          <p class="mt-2 text-2xl font-bold text-gray-900">{{ summary.today_events.toLocaleString() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-5">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Failed Logins</p>
          <p class="mt-2 text-2xl font-bold text-red-600">{{ summary.failed_logins.toLocaleString() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-5">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Rate Limit Hits</p>
          <p class="mt-2 text-2xl font-bold text-amber-600">{{ summary.rate_limit_hits.toLocaleString() }}</p>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6 sm:p-8">
        <h2 class="text-lg font-bold text-gray-900 mb-6">Daily Security Events</h2>

        <div v-if="periodTotal === 0" class="py-10 text-center text-sm text-gray-400">No security events recorded in this range.</div>
        <template v-else>
          <div class="flex items-end gap-1 h-48 overflow-x-auto pb-1">
            <div
              v-for="point in summary.daily_totals"
              :key="point.date"
              class="group relative flex-1 min-w-[6px] flex flex-col items-center justify-end h-full"
              :title="`${formatDate(point.date)}: ${point.total} events`"
            >
              <span class="mb-1 text-[10px] font-semibold text-gray-500 opacity-0 group-hover:opacity-100 transition">{{ point.total }}</span>
              <div
                class="w-full rounded-t-sm bg-red-700 group-hover:bg-red-600 transition"
                :style="{ height: `${(point.total / maxDaily) * 100}%`, minHeight: point.total > 0 ? '2px' : '0' }"
              ></div>
            </div>
          </div>
          <div class="mt-2 flex justify-between text-xs text-gray-400">
            <span>{{ formatDate(summary.daily_totals[0].date) }}</span>
            <span>{{ formatDate(summary.daily_totals[summary.daily_totals.length - 1].date) }}</span>
          </div>
        </template>
      </div>

      <div v-if="summary.by_type.length" class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6 sm:p-8">
        <h2 class="text-lg font-bold text-gray-900 mb-6">By Event Type</h2>
        <div class="space-y-4">
          <div v-for="type in summary.by_type" :key="type.event_type" class="flex items-center gap-4">
            <p class="w-44 shrink-0 text-sm font-medium text-gray-700 truncate">{{ eventLabel(type.event_type) }}</p>
            <div class="flex-1 h-3 rounded-full bg-gray-100 overflow-hidden">
              <div class="h-full rounded-full bg-red-700" :style="{ width: `${(type.total / maxByType) * 100}%` }"></div>
            </div>
            <p class="w-16 shrink-0 text-right text-sm font-semibold text-gray-900">{{ type.total.toLocaleString() }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6 sm:p-8">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-lg font-bold text-gray-900">Event Log</h2>
          <p class="no-print text-xs text-gray-400">{{ pagination.total }} total</p>
        </div>

        <div v-if="loadingEvents" class="py-10 text-center text-sm text-gray-400">Loading events...</div>
        <div v-else-if="!events.length" class="py-10 text-center text-sm text-gray-400">No events match these filters.</div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-200 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <th class="py-2 pr-4">When</th>
                <th class="py-2 pr-4">Type</th>
                <th class="py-2 pr-4">IP Address</th>
                <th class="py-2 pr-4">Identifier</th>
                <th class="py-2 pr-4">Path</th>
                <th class="py-2">Details</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="event in events" :key="event.id">
                <td class="py-2.5 pr-4 whitespace-nowrap text-gray-500">{{ formatDateTime(event.created_at) }}</td>
                <td class="py-2.5 pr-4">
                  <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="eventBadgeClass(event.event_type)">{{ eventLabel(event.event_type) }}</span>
                </td>
                <td class="py-2.5 pr-4 font-mono text-xs text-gray-700">{{ event.ip_address ?? '—' }}</td>
                <td class="py-2.5 pr-4 text-gray-700">{{ event.identifier ?? '—' }}</td>
                <td class="py-2.5 pr-4 font-mono text-xs text-gray-500">{{ event.request_path ?? '—' }}</td>
                <td class="py-2.5 text-gray-500">{{ event.details ?? '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="pagination.pages > 1" class="no-print mt-4 flex items-center justify-between">
          <button
            type="button"
            :disabled="pagination.page <= 1"
            class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:opacity-40 disabled:hover:bg-white"
            @click="fetchEvents(pagination.page - 1)"
          >
            Previous
          </button>
          <span class="text-xs text-gray-500">Page {{ pagination.page }} of {{ pagination.pages }}</span>
          <button
            type="button"
            :disabled="pagination.page >= pagination.pages"
            class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:opacity-40 disabled:hover:bg-white"
            @click="fetchEvents(pagination.page + 1)"
          >
            Next
          </button>
        </div>
      </div>

      <p class="text-xs text-gray-400">
        Report generated {{ new Date().toLocaleString() }} · Range: {{ formatDate(from) }} – {{ formatDate(to) }}
      </p>
    </template>
  </div>
</template>
