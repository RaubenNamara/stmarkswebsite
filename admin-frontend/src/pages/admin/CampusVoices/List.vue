<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { api, apiErrorMessage } from '@/services/api'
import { useToast } from '@/composables/useToast'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import RichTextEditor from '@/components/common/RichTextEditor.vue'
import type { ApiResponse, CampusVoiceItem } from '@/types'

const toast = useToast()
const items = ref<CampusVoiceItem[]>([])
const loading = ref(false)
const submitting = ref(false)
const confirmState = reactive({ open: false, id: null as number | null })
const editingId = ref<number | null>(null)
const editingImage = ref<string | null>(null)
const formCard = ref<HTMLElement | null>(null)

const form = reactive({ student_name: '', title: '', content: '', category: '', author: '' })
const imageFile = ref<File | null>(null)

async function fetchAll() {
  loading.value = true
  try {
    const { data } = await api.get<ApiResponse<{ data: CampusVoiceItem[] }>>('/admin/campus-voices', { params: { limit: 50 } })
    items.value = data.data?.data ?? []
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to load articles'))
  } finally {
    loading.value = false
  }
}

function resetForm() {
  form.student_name = ''
  form.title = ''
  form.content = ''
  form.category = ''
  form.author = ''
  imageFile.value = null
  editingId.value = null
  editingImage.value = null
}

function startEdit(item: CampusVoiceItem) {
  editingId.value = item.id
  form.student_name = item.student_name
  form.title = item.title
  form.content = item.content
  form.category = item.category ?? ''
  form.author = item.author ?? ''
  imageFile.value = null
  editingImage.value = item.featured_image_url
  formCard.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

async function submit() {
  const body = new FormData()
  for (const [key, value] of Object.entries(form)) body.append(key, value)
  if (imageFile.value) body.append('featured_image', imageFile.value)

  submitting.value = true
  try {
    if (editingId.value !== null) {
      await api.post(`/admin/campus-voices/${editingId.value}`, body)
      toast.success('Article updated')
    } else {
      await api.post('/admin/campus-voices', body)
      toast.success('Article created')
    }
    resetForm()
    await fetchAll()
  } catch (e) {
    toast.error(apiErrorMessage(e, editingId.value !== null ? 'Failed to update article' : 'Failed to create article'))
  } finally {
    submitting.value = false
  }
}

async function toggleFeatured(item: CampusVoiceItem) {
  try {
    await api.post(`/admin/campus-voices/${item.id}/toggle-featured`)
    item.featured = item.featured ? 0 : 1
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to update'))
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
    await api.delete(`/admin/campus-voices/${id}`)
    items.value = items.value.filter((i) => i.id !== id)
    if (editingId.value === id) resetForm()
    toast.success('Article deleted')
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to delete'))
  }
}

onMounted(fetchAll)
</script>

<template>
  <div class="p-8 space-y-8">
    <h1 class="text-3xl font-bold text-gray-800">Campus Voices</h1>

    <div ref="formCard" class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold">{{ editingId !== null ? 'Edit Article' : 'Add Article' }}</h2>
        <button v-if="editingId !== null" type="button" class="text-sm text-gray-500 hover:underline" @click="resetForm">
          Cancel edit
        </button>
      </div>

      <form class="space-y-4" @submit.prevent="submit">
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Student Name</label>
            <input v-model="form.student_name" type="text" required class="w-full border p-2 rounded" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Author (byline, optional)</label>
            <input v-model="form.author" type="text" class="w-full border p-2 rounded" />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Title</label>
          <input v-model="form.title" type="text" required class="w-full border p-2 rounded" />
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Category</label>
          <input v-model="form.category" type="text" placeholder="e.g. Opinion, Feature, Sports" class="w-full border p-2 rounded" />
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Content</label>
          <RichTextEditor v-model="form.content" />
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Featured Image</label>
          <img v-if="editingImage" :src="editingImage" class="h-20 mb-2 rounded object-cover" />
          <input type="file" accept="image/*" @change="(e) => (imageFile = (e.target as HTMLInputElement).files?.[0] ?? null)" />
          <p v-if="editingId !== null" class="text-xs text-gray-400 mt-1">Leave blank to keep the current image.</p>
        </div>

        <div class="flex gap-3">
          <button type="submit" :disabled="submitting" class="bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded disabled:opacity-50">
            {{ submitting ? 'Saving...' : editingId !== null ? 'Update Article' : 'Publish Article' }}
          </button>
          <button v-if="editingId !== null" type="button" class="px-4 py-2 border rounded" @click="resetForm">Cancel</button>
        </div>
      </form>
    </div>

    <div>
      <h2 class="text-xl font-bold mb-4">All Articles</h2>

      <div v-if="loading" class="text-center py-8 text-gray-500">Loading...</div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="item in items" :key="item.id" class="bg-white rounded-lg shadow overflow-hidden flex flex-col">
          <div class="h-40 bg-gray-100">
            <img v-if="item.featured_image_url" :src="item.featured_image_url" class="w-full h-full object-cover" />
            <div v-else class="flex items-center justify-center h-full text-gray-400 text-sm">No image</div>
          </div>
          <div class="p-4 flex flex-col flex-1">
            <div class="flex items-center gap-2 mb-1">
              <span v-if="item.featured" class="text-xs bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full">Featured</span>
              <span class="text-xs text-gray-500">{{ item.reading_time }} min read · {{ item.views }} views</span>
            </div>
            <h3 class="font-semibold text-lg mb-1 truncate">{{ item.title }}</h3>
            <p class="text-sm text-gray-500 mb-3">by {{ item.student_name }}</p>
            <div class="mt-auto flex justify-between items-center">
              <button type="button" class="text-sm text-amber-700 hover:underline" @click="toggleFeatured(item)">
                {{ item.featured ? 'Unfeature' : 'Feature' }}
              </button>
              <div class="flex gap-2">
                <button type="button" class="border border-blue-900 text-blue-900 px-2 py-1 rounded text-sm" @click="startEdit(item)">
                  Edit
                </button>
                <button type="button" class="bg-red-600 text-white px-2 py-1 rounded text-sm" @click="requestDelete(item.id)">Delete</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="!loading && items.length === 0" class="text-center py-8 text-gray-500">No articles yet.</div>
    </div>

    <ConfirmDialog
      :open="confirmState.open"
      title="Delete article"
      message="Are you sure you want to delete this article? This cannot be undone."
      confirm-label="Delete"
      danger
      @confirm="confirmDelete"
      @cancel="confirmState.open = false"
    />
  </div>
</template>
