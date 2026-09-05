<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

defineOptions({
  layout: AdminLayout
})

const props = defineProps({
  pageTitle: String,
  total: Number,
  overallExperience: Array,
  eventAreas: Array,
  futureParticipation: Array,
  activitiesInterest: Array,
  entries: Array,
  overallScore: Number,
  areaScores: Array
})

const viewing = ref(null)

function openEntry(entry) {
  viewing.value = entry
}
function closeEntry() {
  viewing.value = null
}

function remove(id) {
  if (confirm('Delete this feedback entry?')) {
    router.delete(`/admin/smosa-feedback/${id}`)
  }
}

function formatDate(date) {
  if (!date) return ''
  return new Date(date).toLocaleString('en-GB', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const barColors = {
  Excellent: 'bg-green-600',
  'Very Good': 'bg-emerald-500',
  Good: 'bg-blue-600',
  Fair: 'bg-yellow-500',
  Poor: 'bg-red-600',
  Definitely: 'bg-green-600',
  Probably: 'bg-blue-600',
  'Not sure': 'bg-yellow-500',
  'Probably not': 'bg-red-600'
}
function barColor(label) {
  return barColors[label] || 'bg-indigo-600'
}

const donutHex = {
  Excellent: '#16a34a',
  'Very Good': '#10b981',
  Good: '#2563eb',
  Fair: '#eab308',
  Poor: '#dc2626',
  Definitely: '#16a34a',
  Probably: '#2563eb',
  'Not sure': '#eab308',
  'Probably not': '#dc2626'
}

const DONUT_RADIUS = 60
const DONUT_CIRC = 2 * Math.PI * DONUT_RADIUS

function buildDonut(breakdown) {
  let offset = 0
  return (breakdown || [])
    .filter((row) => row.percent > 0)
    .map((row) => {
      const dash = (row.percent / 100) * DONUT_CIRC
      const segment = {
        label: row.label,
        percent: row.percent,
        color: donutHex[row.label] || '#6366f1',
        dasharray: `${dash} ${DONUT_CIRC - dash}`,
        dashoffset: -offset
      }
      offset += dash
      return segment
    })
}

const overallDonut = computed(() => buildDonut(props.overallExperience))

function scoreColor(score) {
  if (score >= 80) return '#16a34a'
  if (score >= 60) return '#2563eb'
  if (score >= 40) return '#eab308'
  return '#dc2626'
}

function scoreLabel(score) {
  if (score >= 80) return 'Strong'
  if (score >= 60) return 'Good'
  if (score >= 40) return 'Needs Attention'
  return 'Weak'
}

const strongestArea = computed(() => (props.areaScores?.length ? props.areaScores[0] : null))
const weakestArea = computed(() =>
  props.areaScores?.length ? props.areaScores[props.areaScores.length - 1] : null
)
</script>

<template>
  <div class="space-y-8">

    <!-- HEADER -->
    <div class="flex items-center justify-between">
      <h1 class="text-3xl font-bold text-gray-800">{{ pageTitle }}</h1>
      <div class="text-sm text-gray-500">{{ total }} Responses</div>
    </div>

    <div v-if="total === 0" class="bg-white shadow rounded-xl p-10 text-center text-gray-500">
      No feedback submitted yet.
    </div>

    <template v-else>

      <!-- OVERALL EXPERIENCE -->
      <section class="bg-white shadow-lg rounded-xl border p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Overall Experience</h2>
        <div class="space-y-3">
          <div v-for="row in overallExperience" :key="row.label">
            <div class="flex justify-between text-sm mb-1">
              <span class="font-medium text-gray-700">{{ row.label }}</span>
              <span class="text-gray-500">{{ row.percent }}% ({{ row.count }})</span>
            </div>
            <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden">
              <div
                class="h-full rounded-full transition-all"
                :class="barColor(row.label)"
                :style="{ width: row.percent + '%' }"
              ></div>
            </div>
          </div>
        </div>
      </section>

      <!-- EVENT EXPERIENCE PER AREA -->
      <section class="bg-white shadow-lg rounded-xl border p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-1">Event Experience — Performance by Area</h2>
        <p class="text-sm text-gray-500 mb-5">Percentage breakdown of ratings for each area, so you can see which groups performed best.</p>

        <div class="space-y-6">
          <div v-for="area in eventAreas" :key="area.label">
            <p class="font-semibold text-gray-800 mb-2">{{ area.label }}</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
              <div v-for="row in area.breakdown" :key="row.label" class="bg-gray-50 rounded-lg p-3">
                <div class="flex justify-between text-xs mb-1">
                  <span class="font-medium text-gray-600">{{ row.label }}</span>
                  <span class="text-gray-500">{{ row.percent }}%</span>
                </div>
                <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                  <div
                    class="h-full rounded-full"
                    :class="barColor(row.label)"
                    :style="{ width: row.percent + '%' }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- FUTURE PARTICIPATION -->
      <section class="bg-white shadow-lg rounded-xl border p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Would Attend Another SMOSA Event?</h2>
        <div class="space-y-3">
          <div v-for="row in futureParticipation" :key="row.label">
            <div class="flex justify-between text-sm mb-1">
              <span class="font-medium text-gray-700">{{ row.label }}</span>
              <span class="text-gray-500">{{ row.percent }}% ({{ row.count }})</span>
            </div>
            <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden">
              <div
                class="h-full rounded-full"
                :class="barColor(row.label)"
                :style="{ width: row.percent + '%' }"
              ></div>
            </div>
          </div>
        </div>
      </section>

      <!-- ACTIVITIES INTEREST -->
      <section class="bg-white shadow-lg rounded-xl border p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-1">Activities Alumni Want More Of</h2>
        <p class="text-sm text-gray-500 mb-4">Percentage of respondents who selected each activity (multi-select).</p>
        <div class="space-y-3">
          <div v-for="row in activitiesInterest" :key="row.label">
            <div class="flex justify-between text-sm mb-1">
              <span class="font-medium text-gray-700">{{ row.label }}</span>
              <span class="text-gray-500">{{ row.percent }}% ({{ row.count }})</span>
            </div>
            <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden">
              <div class="h-full rounded-full bg-indigo-600" :style="{ width: row.percent + '%' }"></div>
            </div>
          </div>
        </div>
      </section>

      <!-- OPEN-ENDED RESPONSES -->
      <section class="bg-white shadow-lg rounded-xl border overflow-hidden">
        <div class="p-6 pb-0">
          <h2 class="text-lg font-bold text-gray-800">Individual Responses</h2>
          <p class="text-sm text-gray-500 mt-1 mb-4">Comments, suggestions and each respondent's overall rating.</p>
        </div>
        <table class="w-full text-sm">
          <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
            <tr>
              <th class="p-4 text-left">Overall</th>
              <th class="p-4 text-left">Best Part</th>
              <th class="p-4 text-left">Date</th>
              <th class="p-4 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="e in entries" :key="e.id" class="border-t hover:bg-gray-50 transition">
              <td class="p-4 font-semibold text-gray-800">{{ e.overall_experience }}</td>
              <td class="p-4 text-gray-700">
                <div class="line-clamp-2">{{ e.best_part || '—' }}</div>
              </td>
              <td class="p-4 text-gray-600">{{ formatDate(e.created_at) }}</td>
              <td class="p-4 flex gap-2">
                <button
                  @click="openEntry(e)"
                  class="px-3 py-1 text-xs bg-indigo-600 hover:bg-indigo-700 text-white rounded-md"
                >
                  👁 View
                </button>
                <button
                  @click="remove(e.id)"
                  class="px-3 py-1 text-xs bg-red-600 hover:bg-red-700 text-white rounded-md"
                >
                  🗑 Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </section>

      <!-- PERFORMANCE SUMMARY -->
      <section class="bg-white shadow-lg rounded-xl border p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-1">Performance Summary</h2>
        <p class="text-sm text-gray-500 mb-6">
          Overall satisfaction score and a ranked comparison of every area, from strongest to weakest.
        </p>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

          <!-- OVERALL SCORE DONUT -->
          <div class="lg:col-span-2 flex flex-col items-center justify-center">
            <div class="relative w-44 h-44">
              <svg viewBox="0 0 140 140" class="w-44 h-44 -rotate-90">
                <circle cx="70" cy="70" r="60" fill="none" stroke="#f1f5f9" stroke-width="16" />
                <circle
                  v-for="seg in overallDonut"
                  :key="seg.label"
                  cx="70" cy="70" r="60" fill="none"
                  :stroke="seg.color"
                  stroke-width="16"
                  :stroke-dasharray="seg.dasharray"
                  :stroke-dashoffset="seg.dashoffset"
                />
              </svg>
              <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="text-3xl font-extrabold" :style="{ color: scoreColor(overallScore) }">
                  {{ overallScore }}
                </span>
                <span class="text-xs text-gray-500">out of 100</span>
              </div>
            </div>
            <p class="mt-4 font-semibold" :style="{ color: scoreColor(overallScore) }">
              {{ scoreLabel(overallScore) }} Overall Satisfaction
            </p>

            <div class="flex flex-wrap justify-center gap-3 mt-4">
              <span v-for="seg in overallDonut" :key="seg.label" class="flex items-center gap-1.5 text-xs text-gray-600">
                <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: seg.color }"></span>
                {{ seg.label }} ({{ seg.percent }}%)
              </span>
            </div>
          </div>

          <!-- AREA PERFORMANCE BAR CHART -->
          <div class="lg:col-span-3">
            <p class="font-semibold text-gray-800 mb-4">Area Performance Ranking (0–100)</p>
            <div class="space-y-3">
              <div v-for="area in areaScores" :key="area.label" class="flex items-center gap-3">
                <span class="w-40 xl:w-48 text-sm text-gray-700 shrink-0 truncate" :title="area.label">
                  {{ area.label }}
                </span>
                <div class="flex-1 h-4 bg-gray-100 rounded-full overflow-hidden">
                  <div
                    class="h-full rounded-full transition-all"
                    :style="{ width: area.score + '%', backgroundColor: scoreColor(area.score) }"
                  ></div>
                </div>
                <span class="w-10 text-sm font-semibold text-right" :style="{ color: scoreColor(area.score) }">
                  {{ area.score }}
                </span>
              </div>
            </div>

            <div v-if="strongestArea && weakestArea" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
              <div class="rounded-lg border border-green-200 bg-green-50 p-4">
                <p class="text-xs font-semibold text-green-700 uppercase mb-1">Strongest Area</p>
                <p class="font-bold text-gray-800">{{ strongestArea.label }}</p>
                <p class="text-sm text-green-700">Score: {{ strongestArea.score }} / 100</p>
              </div>
              <div class="rounded-lg border border-red-200 bg-red-50 p-4">
                <p class="text-xs font-semibold text-red-700 uppercase mb-1">Needs Most Improvement</p>
                <p class="font-bold text-gray-800">{{ weakestArea.label }}</p>
                <p class="text-sm text-red-700">Score: {{ weakestArea.score }} / 100</p>
              </div>
            </div>
          </div>

        </div>
      </section>

    </template>

    <!-- VIEW MODAL -->
    <div v-if="viewing" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white w-full max-w-2xl rounded-xl shadow-xl p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-bold text-gray-800">Feedback — {{ viewing.overall_experience }}</h2>
          <button @click="closeEntry" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>

        <div class="space-y-4">
          <div>
            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Best / Most Memorable Part</p>
            <p class="text-gray-700 whitespace-pre-wrap">{{ viewing.best_part || '—' }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">What Could Improve</p>
            <p class="text-gray-700 whitespace-pre-wrap">{{ viewing.improvements || '—' }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Future Engagement Suggestions</p>
            <p class="text-gray-700 whitespace-pre-wrap">{{ viewing.future_suggestions || '—' }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Other Comments</p>
            <p class="text-gray-700 whitespace-pre-wrap">{{ viewing.other_comments || '—' }}</p>
          </div>
        </div>

        <div class="flex justify-end mt-6">
          <button @click="closeEntry" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md">
            Close
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
