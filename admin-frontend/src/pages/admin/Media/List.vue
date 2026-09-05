<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { api, apiErrorMessage } from '@/services/api'
import { useToast } from '@/composables/useToast'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import type { MediaItem } from '@/types'

const toast = useToast()
const items = ref<MediaItem[]>([])
const loading = ref(false)
const submitting = ref(false)
const confirmState = reactive({ open: false, id: null as number | null })

const title = ref('')
const videoUrl = ref('')
const file = ref<File | null>(null)

async function fetchAll() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/media')
    items.value = data.data?.media ?? []
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to load media'))
  } finally {
    loading.value = false
  }
}

async function submit() {
  const body = new FormData()
  body.append('title', title.value)
  if (file.value) {
    body.append('file', file.value)
  } else {
    body.append('video_url', videoUrl.value)
  }

  submitting.value = true
  try {
    await api.post('/admin/media', body)
    toast.success('Media item created')
    title.value = ''
    videoUrl.value = ''
    file.value = null
    await fetchAll()
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to create media item'))
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
    await api.delete(`/admin/media/${id}`)
    items.value = items.value.filter((i) => i.id !== id)
    toast.success('Media item deleted')
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to delete'))
  }
}

onMounted(fetchAll)
</script>

<template>
  <div class="p-8 space-y-8">
    <h1 class="text-3xl font-bold text-gray-800">Media Upload</h1>

    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-bold mb-4">Add Media Item</h2>

      <form class="space-y-4" @submit.prevent="submit">
        <div>
          <label class="block text-sm font-medium mb-1">Title</label>
          <input v-model="title" type="text" class="w-full border p-2 rounded" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Upload a file (image or video)</label>
          <input type="file" accept="image/*,video/*" @change="(e) => (file = (e.target as HTMLInputElement).files?.[0] ?? null)" />
        </div>
        <div class="text-center text-sm text-gray-400">— or —</div>
        <div>
          <label class="block text-sm font-medium mb-1">External Video URL (e.g. YouTube link)</label>
          <input v-model="videoUrl" type="url" :disabled="!!file" placeholder="https://..." class="w-full border p-2 rounded disabled:bg-gray-100" />
        </div>

        <button type="submit" :disabled="submitting" class="bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded disabled:opacity-50">
          {{ submitting ? 'Saving...' : 'Save' }}
        </button>
      </form>
    </div>

    <div>
      <h2 class="text-xl font-bold mb-4">All Media</h2>

      <div v-if="loading" class="text-center py-8 text-gray-500">Loading...</div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="item in items" :key="item.id" class="bg-white rounded-lg shadow overflow-hidden flex flex-col">
          <div class="h-40 bg-gray-100 flex items-center justify-center">
            <img v-if="item.type === 'image' && item.file_url" :src="item.file_url" class="w-full h-full object-cover" />
            <video v-else-if="item.type === 'video' && item.file_url" :src="item.file_url" controls class="w-full h-full object-cover"></video>
            <a v-else-if="item.type === 'link' && item.video_url" :href="item.video_url" target="_blank" class="text-blue-700 text-sm underline p-4">
              {{ item.video_url }}
            </a>
          </div>
          <div class="p-4 flex flex-col flex-1">
            <h3 class="font-semibold mb-2 truncate">{{ item.title || '(untitled)' }}</h3>
            <div class="mt-auto flex justify-end">
              <button type="button" class="bg-red-600 text-white px-2 py-1 rounded text-sm" @click="requestDelete(item.id)">Delete</button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="!loading && items.length === 0" class="text-center py-8 text-gray-500">No media yet.</div>
    </div>

    <ConfirmDialog
      :open="confirmState.open"
      title="Delete media item"
      message="Are you sure you want to delete this? This cannot be undone."
      confirm-label="Delete"
      danger
      @confirm="confirmDelete"
      @cancel="confirmState.open = false"
    />
  </div>
</template>
