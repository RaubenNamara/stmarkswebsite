<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { api, apiErrorMessage } from '@/services/api'
import { useToast } from '@/composables/useToast'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import type { ApiResponse, ContactItem } from '@/types'

const toast = useToast()
const items = ref<ContactItem[]>([])
const loading = ref(false)
const confirmState = reactive({ open: false, id: null as number | null })

async function fetchAll() {
  loading.value = true
  try {
    const { data } = await api.get<ApiResponse<{ data: ContactItem[] }>>('/admin/contacts', { params: { limit: 50 } })
    items.value = data.data?.data ?? []
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to load contacts'))
  } finally {
    loading.value = false
  }
}

async function markRead(item: ContactItem) {
  try {
    await api.post(`/admin/contacts/${item.id}/mark-read`)
    item.is_read = 1
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to mark as read'))
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
    await api.delete(`/admin/contacts/${id}`)
    items.value = items.value.filter((i) => i.id !== id)
    toast.success('Contact deleted')
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to delete'))
  }
}

onMounted(fetchAll)
</script>

<template>
  <div class="p-8 space-y-8">
    <h1 class="text-3xl font-bold text-gray-800">Contact Messages</h1>

    <div v-if="loading" class="text-center py-8 text-gray-500">Loading...</div>

    <div v-else class="space-y-4">
      <div
        v-for="item in items"
        :key="item.id"
        class="bg-white rounded-lg shadow p-5"
        :class="{ 'border-l-4 border-blue-600': !item.is_read }"
      >
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="font-semibold">{{ item.name }} <span class="text-gray-400 font-normal text-sm">&lt;{{ item.email }}&gt;</span></p>
            <p v-if="item.telephone" class="text-sm text-gray-500">{{ item.telephone }}</p>
          </div>
          <span v-if="!item.is_read" class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">Unread</span>
        </div>
        <p class="mt-3 text-gray-700 whitespace-pre-line">{{ item.message }}</p>
        <div class="mt-4 flex gap-3">
          <button v-if="!item.is_read" type="button" class="text-sm text-blue-700 hover:underline" @click="markRead(item)">
            Mark as read
          </button>
          <button type="button" class="text-sm text-red-600 hover:underline" @click="requestDelete(item.id)">Delete</button>
        </div>
      </div>

      <div v-if="items.length === 0" class="text-center py-8 text-gray-500">No messages yet.</div>
    </div>

    <ConfirmDialog
      :open="confirmState.open"
      title="Delete message"
      message="Are you sure you want to delete this message? This cannot be undone."
      confirm-label="Delete"
      danger
      @confirm="confirmDelete"
      @cancel="confirmState.open = false"
    />
  </div>
</template>
