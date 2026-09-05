<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { api, apiErrorMessage } from '@/services/api'
import { useToast } from '@/composables/useToast'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import type { ApiResponse, JobApplicationItem } from '@/types'

const toast = useToast()
const items = ref<JobApplicationItem[]>([])
const loading = ref(false)
const confirmState = reactive({ open: false, id: null as number | null })

async function fetchAll() {
  loading.value = true
  try {
    const { data } = await api.get<ApiResponse<{ data: JobApplicationItem[] }>>('/admin/applications', { params: { limit: 50 } })
    items.value = data.data?.data ?? []
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to load applications'))
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
    await api.delete(`/admin/applications/${id}`)
    items.value = items.value.filter((i) => i.id !== id)
    toast.success('Application deleted')
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to delete'))
  }
}

onMounted(fetchAll)
</script>

<template>
  <div class="p-8 space-y-8">
    <h1 class="text-3xl font-bold text-gray-800">Job Applications</h1>

    <div v-if="loading" class="text-center py-8 text-gray-500">Loading...</div>

    <div v-else class="bg-white rounded-lg shadow overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-gray-600">
          <tr>
            <th class="p-3">Name</th>
            <th class="p-3">Position</th>
            <th class="p-3">Contact</th>
            <th class="p-3">Email</th>
            <th class="p-3">CV</th>
            <th class="p-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="item in items" :key="item.id">
            <td class="p-3 font-medium">{{ item.full_name }}</td>
            <td class="p-3">{{ item.position }}</td>
            <td class="p-3">{{ item.contact }}</td>
            <td class="p-3">{{ item.email }}</td>
            <td class="p-3">
              <a v-if="item.file_url" :href="item.file_url" target="_blank" class="text-blue-700 hover:underline">View CV</a>
            </td>
            <td class="p-3 text-right">
              <button type="button" class="bg-red-600 text-white px-3 py-1.5 rounded text-sm" @click="requestDelete(item.id)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="items.length === 0" class="text-center py-8 text-gray-500">No applications yet.</div>
    </div>

    <ConfirmDialog
      :open="confirmState.open"
      title="Delete application"
      message="Are you sure you want to delete this application? This cannot be undone."
      confirm-label="Delete"
      danger
      @confirm="confirmDelete"
      @cancel="confirmState.open = false"
    />
  </div>
</template>
