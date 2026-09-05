<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { api, apiErrorMessage } from '@/services/api'
import { useToast } from '@/composables/useToast'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'

interface RatingBreakdown {
  value: string
  count: number
  percentage: number
}
interface CategoryStat {
  column: string
  label: string
  average: number | null
  breakdown: RatingBreakdown[]
}
interface Stats {
  total_responses: number
  categories: CategoryStat[]
  activities_interest: Record<string, number>
  overall_experience_breakdown: RatingBreakdown[]
  future_participation_breakdown: RatingBreakdown[]
}
interface FeedbackRow {
  id: number
  overall_experience: string
  best_part: string | null
  created_at: string
}

const toast = useToast()
const rows = ref<FeedbackRow[]>([])
const stats = ref<Stats | null>(null)
const loading = ref(false)
const confirmState = reactive({ open: false, id: null as number | null })

async function fetchAll() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/smosa-feedback', { params: { limit: 50 } })
    rows.value = data.data?.data ?? []
    stats.value = data.data?.stats ?? null
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to load feedback'))
  } finally {
    loading.value = false
  }
}

function requestDelete(id: number) {
  confirmState.open = true
  confirmState.id = id
}

async function confirmDelete() {
  const id = confirmState.id
  confirmState.open = false
  if (id === null) return
  try {
    await api.delete(`/admin/smosa-feedback/${id}`)
    rows.value = rows.value.filter((r) => r.id !== id)
    if (stats.value) stats.value.total_responses--
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to delete'))
  }
}

onMounted(fetchAll)
</script>

<template>
  <div class="p-8 space-y-8">
    <h1 class="text-3xl font-bold text-gray-800">SMOSA Feedback</h1>

    <div v-if="loading" class="text-center py-8 text-gray-500">Loading...</div>

    <template v-else-if="stats">
      <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm text-gray-500">Total Responses</p>
        <p class="text-3xl font-bold">{{ stats.total_responses }}</p>
      </div>

      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="cat in stats.categories" :key="cat.column" class="bg-white rounded-lg shadow p-5">
          <p class="font-semibold mb-1">{{ cat.label }}</p>
          <p class="text-2xl font-bold text-blue-900 mb-3">{{ cat.average ?? '—' }}<span class="text-sm text-gray-400"> / 5</span></p>
          <div v-for="b in cat.breakdown" :key="b.value" class="flex items-center gap-2 text-xs mb-1">
            <span class="w-20 truncate text-gray-500">{{ b.value }}</span>
            <div class="flex-1 bg-gray-100 rounded-full h-2">
              <div class="bg-blue-800 h-2 rounded-full" :style="{ width: b.percentage + '%' }"></div>
            </div>
            <span class="w-10 text-right text-gray-500">{{ b.percentage }}%</span>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <p class="font-semibold mb-3">Activities of Interest</p>
        <div class="flex flex-wrap gap-2">
          <span
            v-for="(count, activity) in stats.activities_interest"
            :key="activity"
            class="text-xs bg-blue-100 text-blue-800 px-3 py-1 rounded-full"
          >
            {{ activity }} ({{ count }})
          </span>
        </div>
      </div>
    </template>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-gray-600">
          <tr>
            <th class="p-3">Overall Experience</th>
            <th class="p-3">Best Part</th>
            <th class="p-3">Date</th>
            <th class="p-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="row in rows" :key="row.id">
            <td class="p-3">{{ row.overall_experience }}</td>
            <td class="p-3 max-w-sm truncate">{{ row.best_part }}</td>
            <td class="p-3 text-gray-500">{{ new Date(row.created_at).toLocaleDateString() }}</td>
            <td class="p-3 text-right">
              <button type="button" class="bg-red-600 text-white px-3 py-1.5 rounded text-sm" @click="requestDelete(row.id)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="rows.length === 0" class="text-center py-8 text-gray-500">No feedback yet.</div>
    </div>

    <ConfirmDialog
      :open="confirmState.open"
      title="Delete feedback"
      message="Are you sure you want to delete this feedback? This cannot be undone."
      confirm-label="Delete"
      danger
      @confirm="confirmDelete"
      @cancel="confirmState.open = false"
    />
  </div>
</template>
