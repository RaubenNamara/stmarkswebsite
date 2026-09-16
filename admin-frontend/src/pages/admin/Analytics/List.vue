<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { api, apiErrorMessage } from '@/services/api'
import { useToast } from '@/composables/useToast'

interface DailyPoint {
  date: string
  views: number
}
interface PageBreakdown {
  page_type: string
  views: number
}
interface Summary {
  total_views: number
  today_views: number
  from: string
  to: string
  daily_totals: DailyPoint[]
  by_page: PageBreakdown[]
}

const toast = useToast()
const loading = ref(false)
const summary = ref<Summary | null>(null)

function isoDaysAgo(n: number): string {
  const d = new Date()
  d.setDate(d.getDate() - n)
  return d.toISOString().slice(0, 10)
}

const from = ref(isoDaysAgo(29))
const to = ref(isoDaysAgo(0))

const PAGE_LABELS: Record<string, string> = {
  home: 'Home',
  news: 'Latest News',
  academics: 'Academics',
  admissions: 'Admissions',
  staff: 'Staff',
  contact: 'Contact',
  gallery: 'Gallery',
  clubs: 'Clubs',
  'campus-voices': 'Campus Voices',
  'fee-structures': 'Fee Structures',
  performance: 'Performance & Circulars',
  'board-members': 'Board Members',
  empowerment: 'Empowerment Programmes',
  explore: 'Explore',
}

function pageLabel(type: string): string {
  return PAGE_LABELS[type] ?? type.replace(/-/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase())
}

const maxDaily = computed(() => Math.max(1, ...(summary.value?.daily_totals.map((d) => d.views) ?? [0])))
const maxByPage = computed(() => Math.max(1, ...(summary.value?.by_page.map((p) => p.views) ?? [0])))
const periodTotal = computed(() => summary.value?.daily_totals.reduce((sum, d) => sum + d.views, 0) ?? 0)

function formatDate(date: string): string {
  return new Date(date + 'T00:00:00').toLocaleDateString(undefined, { month: 'short', day: 'numeric' })
}

async function fetchSummary() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/analytics', { params: { from: from.value, to: to.value } })
    summary.value = data.data
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to load analytics'))
  } finally {
    loading.value = false
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
        <h1 class="text-2xl font-bold text-gray-900">Website Analytics</h1>
        <p class="mt-1 text-sm text-gray-500">How visitors are using the public website, starting with the Home page.</p>
      </div>
      <div class="no-print flex flex-wrap items-center gap-3">
        <label class="flex items-center gap-1.5 text-xs font-semibold text-gray-500">
          From
          <input v-model="from" type="date" class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-sm shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20" @change="fetchSummary">
        </label>
        <label class="flex items-center gap-1.5 text-xs font-semibold text-gray-500">
          To
          <input v-model="to" type="date" class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-sm shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20" @change="fetchSummary">
        </label>
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
      <span class="text-sm">Loading analytics...</span>
    </div>

    <template v-else-if="summary">
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-5">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Views (all time)</p>
          <p class="mt-2 text-2xl font-bold text-gray-900">{{ summary.total_views.toLocaleString() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-5">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Views Today</p>
          <p class="mt-2 text-2xl font-bold text-gray-900">{{ summary.today_views.toLocaleString() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-5">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Views in Range</p>
          <p class="mt-2 text-2xl font-bold text-gray-900">{{ periodTotal.toLocaleString() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-5">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Most Visited Page</p>
          <p class="mt-2 text-2xl font-bold text-gray-900 truncate">{{ summary.by_page[0] ? pageLabel(summary.by_page[0].page_type) : '—' }}</p>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6 sm:p-8">
        <h2 class="text-lg font-bold text-gray-900 mb-6">Daily Visits</h2>

        <div v-if="periodTotal === 0" class="py-10 text-center text-sm text-gray-400">No visits recorded in this range yet.</div>
        <div v-else class="flex items-end gap-1 h-48 overflow-x-auto pb-1">
          <div
            v-for="point in summary.daily_totals"
            :key="point.date"
            class="group relative flex-1 min-w-[6px] flex flex-col items-center justify-end h-full"
            :title="`${formatDate(point.date)}: ${point.views} views`"
          >
            <span class="mb-1 text-[10px] font-semibold text-gray-500 opacity-0 group-hover:opacity-100 transition">{{ point.views }}</span>
            <div
              class="w-full rounded-t-sm bg-blue-800 group-hover:bg-blue-600 transition"
              :style="{ height: `${(point.views / maxDaily) * 100}%`, minHeight: point.views > 0 ? '2px' : '0' }"
            ></div>
          </div>
        </div>
        <div v-if="periodTotal > 0" class="mt-2 flex justify-between text-xs text-gray-400">
          <span>{{ formatDate(summary.daily_totals[0].date) }}</span>
          <span>{{ formatDate(summary.daily_totals[summary.daily_totals.length - 1].date) }}</span>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6 sm:p-8">
        <h2 class="text-lg font-bold text-gray-900 mb-6">Visits by Page</h2>

        <div v-if="!summary.by_page.length" class="py-10 text-center text-sm text-gray-400">No page visits recorded in this range yet.</div>
        <div v-else class="space-y-4">
          <div v-for="page in summary.by_page" :key="page.page_type" class="flex items-center gap-4">
            <p class="w-40 shrink-0 text-sm font-medium text-gray-700 truncate">{{ pageLabel(page.page_type) }}</p>
            <div class="flex-1 h-3 rounded-full bg-gray-100 overflow-hidden">
              <div class="h-full rounded-full bg-blue-800" :style="{ width: `${(page.views / maxByPage) * 100}%` }"></div>
            </div>
            <p class="w-16 shrink-0 text-right text-sm font-semibold text-gray-900">{{ page.views.toLocaleString() }}</p>
          </div>
        </div>
      </div>

      <p class="text-xs text-gray-400">
        Report generated {{ new Date().toLocaleString() }} · Range: {{ formatDate(summary.from) }} – {{ formatDate(summary.to) }}
      </p>
    </template>
  </div>
</template>
