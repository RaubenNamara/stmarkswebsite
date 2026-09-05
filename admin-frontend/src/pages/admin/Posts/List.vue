<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { api, apiErrorMessage } from '@/services/api'
import { useToast } from '@/composables/useToast'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import RichTextEditor from '@/components/common/RichTextEditor.vue'
import type { Post } from '@/types'

const toast = useToast()
const items = ref<Post[]>([])
const loading = ref(false)
const submitting = ref(false)
const confirmState = reactive({ open: false, id: null as number | null })
const editingId = ref<number | null>(null)
const formCard = ref<HTMLElement | null>(null)

const form = reactive({ title: '', content: '', is_published: true })

async function fetchAll() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/posts')
    items.value = data.data?.posts ?? []
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to load posts'))
  } finally {
    loading.value = false
  }
}

function resetForm() {
  form.title = ''
  form.content = ''
  form.is_published = true
  editingId.value = null
}

function startEdit(item: Post) {
  editingId.value = item.id
  form.title = item.title
  form.content = item.content
  form.is_published = !!item.is_published
  formCard.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

async function submit() {
  const payload = { title: form.title, content: form.content, is_published: form.is_published ? 1 : 0 }
  submitting.value = true
  try {
    if (editingId.value !== null) {
      await api.post(`/admin/posts/${editingId.value}`, payload)
      toast.success('Post updated')
    } else {
      await api.post('/admin/posts', payload)
      toast.success('Post created')
    }
    resetForm()
    await fetchAll()
  } catch (e) {
    toast.error(apiErrorMessage(e, editingId.value !== null ? 'Failed to update post' : 'Failed to create post'))
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
    await api.delete(`/admin/posts/${id}`)
    items.value = items.value.filter((i) => i.id !== id)
    if (editingId.value === id) resetForm()
    toast.success('Post deleted')
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to delete'))
  }
}

onMounted(fetchAll)
</script>

<template>
  <div class="p-8 space-y-8">
    <h1 class="text-3xl font-bold text-gray-800">Posts</h1>

    <div ref="formCard" class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold">{{ editingId !== null ? 'Edit Post' : 'Create Post' }}</h2>
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
        <label class="flex items-center gap-2">
          <input v-model="form.is_published" type="checkbox" />
          Publish now
        </label>

        <div class="flex gap-3">
          <button type="submit" :disabled="submitting" class="bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded disabled:opacity-50">
            {{ submitting ? 'Saving...' : editingId !== null ? 'Update Post' : 'Create Post' }}
          </button>
          <button v-if="editingId !== null" type="button" class="px-4 py-2 border rounded" @click="resetForm">Cancel</button>
        </div>
      </form>
    </div>

    <div>
      <h2 class="text-xl font-bold mb-4">All Posts</h2>

      <div v-if="loading" class="text-center py-8 text-gray-500">Loading...</div>

      <div v-else class="bg-white rounded-lg shadow divide-y">
        <div v-for="item in items" :key="item.id" class="flex items-center justify-between p-4">
          <div>
            <p class="font-semibold">{{ item.title }}</p>
            <span class="text-xs" :class="item.is_published ? 'text-green-600' : 'text-gray-400'">
              {{ item.is_published ? 'Published' : 'Draft' }}
            </span>
          </div>
          <div class="flex gap-2">
            <button type="button" class="border border-blue-900 text-blue-900 px-3 py-1.5 rounded text-sm" @click="startEdit(item)">Edit</button>
            <button type="button" class="bg-red-600 text-white px-3 py-1.5 rounded text-sm" @click="requestDelete(item.id)">Delete</button>
          </div>
        </div>
      </div>

      <div v-if="!loading && items.length === 0" class="text-center py-8 text-gray-500">No posts yet.</div>
    </div>

    <ConfirmDialog
      :open="confirmState.open"
      title="Delete post"
      message="Are you sure you want to delete this post? This cannot be undone."
      confirm-label="Delete"
      danger
      @confirm="confirmDelete"
      @cancel="confirmState.open = false"
    />
  </div>
</template>
