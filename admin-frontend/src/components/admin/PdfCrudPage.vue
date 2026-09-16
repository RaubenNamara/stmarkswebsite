<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { api, apiErrorMessage } from '@/services/api'
import { useToast } from '@/composables/useToast'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import type { PdfItem } from '@/types'

const props = withDefaults(
  defineProps<{
    pageTitle: string
    endpoint: string
    listKey: string
    createLabel?: string
    /** Performances has no update route (index/store/destroy only) - FeeStructures does. */
    supportsEdit?: boolean
  }>(),
  { supportsEdit: true },
)

const toast = useToast()
const items = ref<PdfItem[]>([])
const loading = ref(false)
const submitting = ref(false)
const confirmState = reactive({ open: false, id: null as number | null })
const editingId = ref<number | null>(null)
const formCard = ref<HTMLElement | null>(null)

const title = ref('')
const file = ref<File | null>(null)

async function fetchAll() {
  loading.value = true
  try {
    const { data } = await api.get(props.endpoint)
    items.value = data.data?.[props.listKey] ?? []
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to load'))
  } finally {
    loading.value = false
  }
}

function resetForm() {
  title.value = ''
  file.value = null
  editingId.value = null
}

function startEdit(item: PdfItem) {
  editingId.value = item.id
  title.value = item.title
  file.value = null
  formCard.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

async function submit() {
  if (!file.value && editingId.value === null) {
    toast.error('Please choose a PDF file')
    return
  }
  const body = new FormData()
  body.append('title', title.value)
  if (file.value) body.append('file', file.value)

  submitting.value = true
  try {
    if (editingId.value !== null) {
      await api.post(`${props.endpoint}/${editingId.value}`, body)
      toast.success('Updated successfully')
    } else {
      await api.post(props.endpoint, body)
      toast.success('Created successfully')
    }
    resetForm()
    await fetchAll()
  } catch (e) {
    toast.error(apiErrorMessage(e, editingId.value !== null ? 'Failed to update' : 'Failed to create'))
  } finally {
    submitting.value = false
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
    await api.delete(`${props.endpoint}/${id}`)
    items.value = items.value.filter((i) => i.id !== id)
    if (editingId.value === id) resetForm()
    toast.success('Deleted successfully')
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to delete'))
  }
}

onMounted(fetchAll)
</script>

<template>
  <div class="p-6 sm:p-8 space-y-8 max-w-4xl mx-auto">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">{{ pageTitle }}</h1>
      <p class="mt-1 text-sm text-gray-500">{{ items.length }} {{ items.length === 1 ? 'file' : 'files' }} published</p>
    </div>

    <div ref="formCard" class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6 sm:p-8">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-bold text-gray-900">{{ editingId !== null ? 'Edit File' : (createLabel ?? 'Upload PDF') }}</h2>
        <button v-if="editingId !== null" type="button" class="text-sm font-medium text-gray-500 hover:text-gray-700 hover:underline" @click="resetForm">
          Cancel edit
        </button>
      </div>

      <form class="space-y-5" @submit.prevent="submit">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Title</label>
          <input
            v-model="title"
            type="text"
            required
            class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20"
          />
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">PDF File</label>
          <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-dashed border-gray-300 px-3.5 py-3 text-sm text-gray-500 transition hover:border-blue-500/50 hover:bg-blue-50/50">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l-3.75 3.75M12 9.75l3.75 3.75M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" /></svg>
            <span class="truncate">{{ file ? file.name : 'Choose a PDF file' }}</span>
            <input type="file" accept="application/pdf" class="hidden" @change="(e) => (file = (e.target as HTMLInputElement).files?.[0] ?? null)" />
          </label>
          <p v-if="editingId !== null" class="text-xs text-gray-400 mt-1.5">Leave blank to keep the current file.</p>
        </div>

        <div class="flex gap-3 pt-2">
          <button type="submit" :disabled="submitting" class="bg-blue-900 hover:bg-blue-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition disabled:opacity-50">
            {{ submitting ? 'Saving...' : editingId !== null ? 'Update' : 'Upload' }}
          </button>
          <button v-if="editingId !== null" type="button" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition" @click="resetForm">Cancel</button>
        </div>
      </form>
    </div>

    <div>
      <h2 class="text-lg font-bold text-gray-900 mb-4">All Files</h2>

      <div v-if="loading" class="flex items-center justify-center gap-2 py-12 text-gray-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" /></svg>
        <span class="text-sm">Loading...</span>
      </div>

      <div v-else-if="items.length" class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 divide-y divide-gray-100">
        <div v-for="item in items" :key="item.id" class="flex items-center gap-4 p-4 sm:p-5">
          <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
          </div>
          <div class="min-w-0 flex-1">
            <p class="font-semibold text-sm text-gray-900 truncate">{{ item.title }}</p>
            <a v-if="item.file_url" :href="item.file_url" target="_blank" rel="noopener" class="text-sm text-blue-700 hover:underline">View PDF</a>
          </div>
          <div class="flex shrink-0 gap-2">
            <button v-if="supportsEdit" type="button" class="border border-blue-900 text-blue-900 px-2.5 py-1 rounded-md text-xs font-semibold hover:bg-blue-50 transition" @click="startEdit(item)">
              Edit
            </button>
            <button type="button" class="bg-red-600 hover:bg-red-700 text-white px-2.5 py-1 rounded-md text-xs font-semibold transition" @click="requestDelete(item.id)">Delete</button>
          </div>
        </div>
      </div>

      <div v-else class="flex flex-col items-center gap-2 rounded-xl border-2 border-dashed border-gray-200 py-16 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
        <p class="text-sm text-gray-400">Nothing here yet.</p>
      </div>
    </div>

    <ConfirmDialog
      :open="confirmState.open"
      title="Delete file"
      message="Are you sure you want to delete this file? This cannot be undone."
      confirm-label="Delete"
      danger
      @confirm="confirmDelete"
      @cancel="confirmState.open = false"
    />
  </div>
</template>
