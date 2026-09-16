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
  <div class="p-6 sm:p-8 space-y-8 max-w-6xl 2xl:max-w-7xl mx-auto">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Campus Voices</h1>
      <p class="mt-1 text-sm text-gray-500">{{ items.length }} {{ items.length === 1 ? 'article' : 'articles' }} published</p>
    </div>

    <div ref="formCard" class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6 sm:p-8">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-bold text-gray-900">{{ editingId !== null ? 'Edit Article' : 'Add Article' }}</h2>
        <button v-if="editingId !== null" type="button" class="text-sm font-medium text-gray-500 hover:text-gray-700 hover:underline" @click="resetForm">
          Cancel edit
        </button>
      </div>

      <form class="space-y-5" @submit.prevent="submit">
        <div class="grid sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Student Name</label>
            <input v-model="form.student_name" type="text" required class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Author (byline, optional)</label>
            <input v-model="form.author" type="text" class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20" />
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Title</label>
          <input v-model="form.title" type="text" required class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20" />
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Category</label>
          <input v-model="form.category" type="text" placeholder="e.g. Opinion, Feature, Sports" class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20" />
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Content</label>
          <RichTextEditor v-model="form.content" />
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Featured Image</label>
          <img v-if="editingImage" :src="editingImage" class="h-24 w-full max-w-xs mb-2 rounded-lg object-cover ring-1 ring-gray-200" />
          <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-dashed border-gray-300 px-3.5 py-3 text-sm text-gray-500 transition hover:border-blue-500/50 hover:bg-blue-50/50">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 8a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2v-6a2 2 0 00-2-2M4 8V6a2 2 0 012-2h4l2 2h6a2 2 0 012 2v2" /></svg>
            <span class="truncate">{{ imageFile ? imageFile.name : 'Choose an image' }}</span>
            <input type="file" accept="image/*" class="hidden" @change="(e) => (imageFile = (e.target as HTMLInputElement).files?.[0] ?? null)" />
          </label>
          <p v-if="editingId !== null" class="text-xs text-gray-400 mt-1.5">Leave blank to keep the current image.</p>
        </div>

        <div class="flex gap-3 pt-2">
          <button type="submit" :disabled="submitting" class="bg-blue-900 hover:bg-blue-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition disabled:opacity-50">
            {{ submitting ? 'Saving...' : editingId !== null ? 'Update Article' : 'Publish Article' }}
          </button>
          <button v-if="editingId !== null" type="button" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition" @click="resetForm">Cancel</button>
        </div>
      </form>
    </div>

    <div>
      <h2 class="text-lg font-bold text-gray-900 mb-4">All Articles</h2>

      <div v-if="loading" class="flex items-center justify-center gap-2 py-16 text-gray-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" /></svg>
        <span class="text-sm">Loading...</span>
      </div>

      <div v-else-if="items.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        <div v-for="item in items" :key="item.id" class="group bg-white rounded-xl shadow-sm ring-1 ring-gray-200 overflow-hidden flex flex-col transition hover:shadow-md hover:-translate-y-0.5">
          <div class="relative aspect-video bg-gray-100">
            <img v-if="item.featured_image_url" :src="item.featured_image_url" class="w-full h-full object-cover" />
            <div v-else class="flex h-full flex-col items-center justify-center gap-1.5 text-gray-300">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 8a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2v-6a2 2 0 00-2-2M4 8V6a2 2 0 012-2h4l2 2h6a2 2 0 012 2v2" /></svg>
              <span class="text-xs font-medium">No image</span>
            </div>
            <span v-if="item.featured" class="absolute left-2.5 top-2.5 inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800 shadow-sm">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.363 1.118l1.287 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.447a1 1 0 00-1.176 0l-3.367 2.447c-.783.57-1.838-.196-1.539-1.118l1.287-3.957a1 1 0 00-.363-1.118L2.813 9.385c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.285-3.958z" /></svg>
              Featured
            </span>
          </div>

          <div class="p-4 flex flex-col flex-1">
            <span v-if="item.category" class="mb-1.5 w-fit rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700">{{ item.category }}</span>
            <h3 class="font-semibold text-sm text-gray-900 line-clamp-2">{{ item.title }}</h3>
            <p class="mt-1 text-xs text-gray-500">by {{ item.student_name }}</p>
            <p class="mt-1.5 flex items-center gap-1.5 text-xs text-gray-400">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              {{ item.reading_time }} min read
              <span class="text-gray-300">&middot;</span>
              {{ item.views }} views
            </p>

            <div class="mt-3 flex items-center justify-between gap-2 pt-3 border-t border-gray-100">
              <button type="button" class="text-xs font-semibold text-amber-700 hover:underline" @click="toggleFeatured(item)">
                {{ item.featured ? 'Unfeature' : 'Feature' }}
              </button>
              <div class="flex gap-2">
                <button type="button" class="border border-blue-900 text-blue-900 px-2.5 py-1 rounded-md text-xs font-semibold hover:bg-blue-50 transition" @click="startEdit(item)">
                  Edit
                </button>
                <button type="button" class="bg-red-600 hover:bg-red-700 text-white px-2.5 py-1 rounded-md text-xs font-semibold transition" @click="requestDelete(item.id)">Delete</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="flex flex-col items-center gap-2 rounded-xl border-2 border-dashed border-gray-200 py-16 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 01-.923 1.785A5.969 5.969 0 006 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337z" /></svg>
        <p class="text-sm text-gray-400">No articles yet.</p>
      </div>
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
