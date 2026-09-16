<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { api, apiErrorMessage } from '@/services/api'
import { useToast } from '@/composables/useToast'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import type { ApiResponse, ContactItem } from '@/types'

const toast = useToast()
const items = ref<ContactItem[]>([])
const loading = ref(false)
const confirmState = reactive({ open: false, id: null as number | null })

const unreadCount = computed(() => items.value.filter((i) => !i.is_read).length)

function initials(name: string): string {
  return name.trim().charAt(0).toUpperCase() || '?'
}

function formatDate(value: string): string {
  return new Date(value).toLocaleString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

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
  <div class="p-6 sm:p-8 space-y-6 max-w-4xl 2xl:max-w-7xl mx-auto">
    <div class="flex items-center gap-3">
      <h1 class="text-2xl font-bold text-gray-900">Contact Messages</h1>
      <span v-if="unreadCount" class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-bold text-blue-800">{{ unreadCount }} unread</span>
    </div>

    <div v-if="loading" class="flex items-center justify-center gap-2 py-16 text-gray-400">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" /></svg>
      <span class="text-sm">Loading...</span>
    </div>

    <div v-else-if="items.length" class="space-y-3 2xl:columns-2 2xl:gap-4 2xl:space-y-0 2xl:[&>*]:mb-4 2xl:[&>*]:break-inside-avoid">
      <div
        v-for="item in items"
        :key="item.id"
        class="flex gap-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition"
        :class="item.is_read ? '' : 'ring-blue-200 bg-blue-50/30'"
      >
        <span
          class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-sm font-bold"
          :class="item.is_read ? 'bg-gray-100 text-gray-500' : 'bg-blue-900 text-white'"
        >
          {{ initials(item.name) }}
        </span>

        <div class="min-w-0 flex-1">
          <div class="flex flex-wrap items-start justify-between gap-2">
            <div class="min-w-0">
              <p class="font-semibold text-gray-900 truncate">
                {{ item.name }}
                <span class="font-normal text-gray-400 text-sm">&lt;{{ item.email }}&gt;</span>
              </p>
              <p v-if="item.telephone" class="text-sm text-gray-500">{{ item.telephone }}</p>
            </div>
            <span v-if="!item.is_read" class="shrink-0 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-bold text-blue-800">Unread</span>
          </div>

          <p class="mt-3 text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ item.message }}</p>

          <div class="mt-4 flex flex-wrap items-center gap-4">
            <span class="text-xs text-gray-400">{{ formatDate(item.created_at) }}</span>
            <a :href="`mailto:${item.email}`" class="text-sm font-semibold text-blue-700 hover:underline">Reply</a>
            <button v-if="!item.is_read" type="button" class="text-sm font-semibold text-gray-600 hover:underline" @click="markRead(item)">
              Mark as read
            </button>
            <button type="button" class="text-sm font-semibold text-red-600 hover:underline" @click="requestDelete(item.id)">Delete</button>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="flex flex-col items-center gap-2 rounded-xl border-2 border-dashed border-gray-200 py-16 text-center">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
      <p class="text-sm text-gray-400">No messages yet.</p>
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
