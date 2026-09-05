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
  <div class="p-8 space-y-8">
    <h1 class="text-3xl font-bold text-gray-800">{{ pageTitle }}</h1>

    <div ref="formCard" class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold">{{ editingId !== null ? 'Edit File' : (createLabel ?? 'Upload PDF') }}</h2>
        <button v-if="editingId !== null" type="button" class="text-sm text-gray-500 hover:underline" @click="resetForm">
          Cancel edit
        </button>
      </div>

      <form class="space-y-4" @submit.prevent="submit">
        <div>
          <label class="block text-sm font-medium mb-1">Title</label>
          <input v-model="title" type="text" required class="w-full border p-2 rounded" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">PDF File</label>
          <input type="file" accept="application/pdf" @change="(e) => (file = (e.target as HTMLInputElement).files?.[0] ?? null)" />
          <p v-if="editingId !== null" class="text-xs text-gray-400 mt-1">Leave blank to keep the current file.</p>
        </div>

        <div class="flex gap-3">
          <button type="submit" :disabled="submitting" class="bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded disabled:opacity-50">
            {{ submitting ? 'Saving...' : editingId !== null ? 'Update' : 'Upload' }}
          </button>
          <button v-if="editingId !== null" type="button" class="px-4 py-2 border rounded" @click="resetForm">Cancel</button>
        </div>
      </form>
    </div>

    <div>
      <h2 class="text-xl font-bold mb-4">All Files</h2>

      <div v-if="loading" class="text-center py-8 text-gray-500">Loading...</div>

      <div v-else class="bg-white rounded-lg shadow divide-y">
        <div v-for="item in items" :key="item.id" class="flex items-center justify-between p-4">
          <div>
            <p class="font-semibold">{{ item.title }}</p>
            <a v-if="item.file_url" :href="item.file_url" target="_blank" class="text-sm text-blue-700 hover:underline">View PDF</a>
          </div>
          <div class="flex gap-2">
            <button v-if="supportsEdit" type="button" class="border border-blue-900 text-blue-900 px-3 py-1.5 rounded text-sm" @click="startEdit(item)">
              Edit
            </button>
            <button type="button" class="bg-red-600 text-white px-3 py-1.5 rounded text-sm" @click="requestDelete(item.id)">Delete</button>
          </div>
        </div>
      </div>

      <div v-if="!loading && items.length === 0" class="text-center py-8 text-gray-500">Nothing here yet.</div>
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
