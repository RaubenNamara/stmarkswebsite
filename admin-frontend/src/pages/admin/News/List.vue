<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { api, apiErrorMessage } from '@/services/api'
import { useToast } from '@/composables/useToast'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import RichTextEditor from '@/components/common/RichTextEditor.vue'
import type { ApiResponse, NewsItem } from '@/types'

const toast = useToast()
const items = ref<NewsItem[]>([])
const loading = ref(false)
const submitting = ref(false)
const editingId = ref<number | null>(null)
const formCard = ref<HTMLElement | null>(null)

const form = reactive({
  title: '',
  content: '',
  is_published: true,
})
const file = ref<File | null>(null)
const preview = ref<string | null>(null)

const confirmState = reactive({ open: false, id: null as number | null })

async function fetchNews() {
  loading.value = true
  try {
    const { data } = await api.get<ApiResponse<{ data: NewsItem[] }>>('/admin/news', { params: { limit: 50 } })
    items.value = data.data?.data ?? []
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to load news'))
  } finally {
    loading.value = false
  }
}

function handleFile(e: Event) {
  const input = e.target as HTMLInputElement
  const selected = input.files?.[0] ?? null
  file.value = selected
  preview.value = selected ? URL.createObjectURL(selected) : null
}

function resetForm() {
  form.title = ''
  form.content = ''
  form.is_published = true
  file.value = null
  preview.value = null
  editingId.value = null
}

function startEdit(item: NewsItem) {
  editingId.value = item.id
  form.title = item.title
  form.content = item.content
  form.is_published = !!item.is_published
  file.value = null
  preview.value = item.image_url ?? null
  formCard.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

async function submit() {
  const body = new FormData()
  body.append('title', form.title)
  body.append('content', form.content)
  body.append('is_published', form.is_published ? '1' : '0')
  if (file.value) body.append('image', file.value)

  submitting.value = true
  try {
    if (editingId.value !== null) {
      await api.post(`/admin/news/${editingId.value}`, body)
      toast.success('News item updated')
    } else {
      await api.post('/admin/news', body)
      toast.success('News item created')
    }
    resetForm()
    await fetchNews()
  } catch (e) {
    toast.error(apiErrorMessage(e, editingId.value !== null ? 'Failed to update news item' : 'Failed to create news item'))
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
    await api.delete(`/admin/news/${id}`)
    items.value = items.value.filter((n) => n.id !== id)
    if (editingId.value === id) resetForm()
    toast.success('News item deleted')
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to delete news item'))
  }
}

function formatDate(iso: string): string {
  try {
    return new Date(iso).toLocaleString()
  } catch {
    return iso
  }
}

onMounted(fetchNews)
</script>

<template>
  <div class="p-8 space-y-8">
    <div class="flex items-center justify-between">
      <h1 class="text-3xl font-bold text-gray-800">News Management</h1>
    </div>

    <div ref="formCard" class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold">{{ editingId !== null ? 'Edit News' : 'Create News' }}</h2>
        <button v-if="editingId !== null" type="button" class="text-sm text-gray-500 hover:underline" @click="resetForm">
          Cancel edit
        </button>
      </div>

      <form class="space-y-4" @submit.prevent="submit">
        <div>
          <label class="block text-sm font-medium mb-1">Title</label>
          <input v-model="form.title" type="text" required class="w-full border p-2 rounded" />
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Content</label>
          <RichTextEditor v-model="form.content" />
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Image</label>
          <input type="file" accept="image/*" @change="handleFile" />
          <img v-if="preview" :src="preview" class="mt-3 h-40 w-full object-cover rounded" />
          <p v-if="editingId !== null" class="text-xs text-gray-400 mt-1">Leave blank to keep the current image.</p>
        </div>

        <label class="flex items-center gap-2">
          <input v-model="form.is_published" type="checkbox" />
          Publish now
        </label>

        <div class="flex gap-3">
          <button type="submit" :disabled="submitting" class="bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded disabled:opacity-50">
            {{ submitting ? 'Saving...' : editingId !== null ? 'Update News' : 'Create News' }}
          </button>
          <button v-if="editingId !== null" type="button" class="px-4 py-2 border rounded" @click="resetForm">Cancel</button>
        </div>
      </form>
    </div>

    <div>
      <h2 class="text-xl font-bold mb-4">All News</h2>

      <div v-if="loading" class="text-center py-8 text-gray-500">Loading...</div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="item in items" :key="item.id" class="bg-white rounded-lg shadow overflow-hidden flex flex-col">
          <div class="h-48 bg-gray-100">
            <img v-if="item.image_url" :src="item.image_url" class="w-full h-full object-cover" />
            <div v-else class="flex items-center justify-center h-full text-gray-400">No image</div>
          </div>

          <div class="p-4 flex flex-col flex-1">
            <h3 class="font-semibold text-lg mb-2 truncate">{{ item.title }}</h3>
            <p class="text-sm text-gray-600 mb-4 line-clamp-3">{{ item.excerpt || 'No excerpt available' }}</p>

            <div class="mt-auto flex justify-between items-center">
              <span class="text-xs text-gray-500">{{ formatDate(item.created_at) }}</span>
              <div class="flex gap-2">
                <button type="button" class="border border-blue-900 text-blue-900 px-2 py-1 rounded text-sm" @click="startEdit(item)">
                  Edit
                </button>
                <button type="button" class="bg-red-600 text-white px-2 py-1 rounded text-sm" @click="requestDelete(item.id)">
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="!loading && items.length === 0" class="text-center py-8 text-gray-500">No news yet.</div>
    </div>

    <ConfirmDialog
      :open="confirmState.open"
      title="Delete news item"
      message="Are you sure you want to delete this news item? This cannot be undone."
      confirm-label="Delete"
      danger
      @confirm="confirmDelete"
      @cancel="confirmState.open = false"
    />
  </div>
</template>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
