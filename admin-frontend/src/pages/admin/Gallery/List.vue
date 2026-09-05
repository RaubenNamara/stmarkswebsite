<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { api, apiErrorMessage } from '@/services/api'
import { useToast } from '@/composables/useToast'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import type { GalleryEvent } from '@/types'

const toast = useToast()
const events = ref<GalleryEvent[]>([])
const loading = ref(false)
const submitting = ref(false)
const eventConfirm = reactive({ open: false, id: null as number | null })
const imageConfirm = reactive({ open: false, id: null as number | null })

const title = ref('')
const files = ref<FileList | null>(null)
const addImagesTarget = ref<number | null>(null)
const addImagesFiles = ref<FileList | null>(null)
const renamingTarget = ref<number | null>(null)
const renameValue = ref('')

async function fetchAll() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/gallery')
    events.value = data.data?.events ?? []
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to load gallery'))
  } finally {
    loading.value = false
  }
}

async function submit() {
  const body = new FormData()
  body.append('title', title.value)
  if (files.value) {
    for (const f of Array.from(files.value)) body.append('images[]', f)
  }

  submitting.value = true
  try {
    await api.post('/admin/gallery', body)
    toast.success('Gallery event created')
    title.value = ''
    files.value = null
    await fetchAll()
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to create event'))
  } finally {
    submitting.value = false
  }
}

async function submitAddImages(eventId: number) {
  if (!addImagesFiles.value?.length) return
  const body = new FormData()
  for (const f of Array.from(addImagesFiles.value)) body.append('images[]', f)

  try {
    await api.post(`/admin/gallery/${eventId}/add-images`, body)
    toast.success('Images added')
    addImagesTarget.value = null
    addImagesFiles.value = null
    await fetchAll()
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to add images'))
  }
}

function startRename(event: GalleryEvent) {
  renamingTarget.value = event.id
  renameValue.value = event.title
}

async function submitRename(id: number) {
  if (!renameValue.value.trim()) return
  try {
    await api.post(`/admin/gallery/${id}`, { title: renameValue.value })
    toast.success('Event renamed')
    renamingTarget.value = null
    await fetchAll()
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to rename event'))
  }
}

function requestDeleteEvent(id: number) {
  eventConfirm.open = true
  eventConfirm.id = id
}

async function confirmDeleteEvent() {
  const id = eventConfirm.id
  eventConfirm.open = false
  if (id === null) return
  try {
    await api.delete(`/admin/gallery/${id}`)
    events.value = events.value.filter((e) => e.id !== id)
    toast.success('Gallery event deleted')
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to delete event'))
  }
}

function requestDeleteImage(id: number) {
  imageConfirm.open = true
  imageConfirm.id = id
}

async function confirmDeleteImage() {
  const id = imageConfirm.id
  imageConfirm.open = false
  if (id === null) return
  try {
    await api.delete(`/admin/gallery/image/${id}`)
    toast.success('Image deleted')
    await fetchAll()
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to delete image'))
  }
}

onMounted(fetchAll)
</script>

<template>
  <div class="p-8 space-y-8">
    <h1 class="text-3xl font-bold text-gray-800">Update Gallery</h1>

    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-bold mb-4">Create Gallery Event</h2>

      <form class="space-y-4" @submit.prevent="submit">
        <div>
          <label class="block text-sm font-medium mb-1">Event Title</label>
          <input v-model="title" type="text" required class="w-full border p-2 rounded" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Images</label>
          <input type="file" accept="image/*" multiple @change="(e) => (files = (e.target as HTMLInputElement).files)" />
        </div>

        <button type="submit" :disabled="submitting" class="bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded disabled:opacity-50">
          {{ submitting ? 'Saving...' : 'Create Event' }}
        </button>
      </form>
    </div>

    <div v-if="loading" class="text-center py-8 text-gray-500">Loading...</div>

    <div v-else class="space-y-6">
      <div v-for="event in events" :key="event.id" class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4 gap-3">
          <div v-if="renamingTarget === event.id" class="flex items-center gap-2 flex-1">
            <input v-model="renameValue" type="text" class="border p-2 rounded flex-1" @keyup.enter="submitRename(event.id)" />
            <button type="button" class="bg-blue-900 text-white px-3 py-1.5 rounded text-sm" @click="submitRename(event.id)">Save</button>
            <button type="button" class="text-sm text-gray-500" @click="renamingTarget = null">Cancel</button>
          </div>
          <h3 v-else class="text-lg font-bold">{{ event.title }}</h3>
          <div v-if="renamingTarget !== event.id" class="flex gap-2 shrink-0">
            <button type="button" class="border border-blue-900 text-blue-900 px-3 py-1.5 rounded text-sm" @click="startRename(event)">
              Rename
            </button>
            <button type="button" class="bg-red-600 text-white px-3 py-1.5 rounded text-sm" @click="requestDeleteEvent(event.id)">
              Delete Event
            </button>
          </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
          <div v-for="img in event.images" :key="img.id" class="relative group">
            <img :src="img.image_url ?? ''" class="h-24 w-full object-cover rounded" />
            <button
              type="button"
              class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-6 h-6 text-xs opacity-0 group-hover:opacity-100 transition"
              @click="requestDeleteImage(img.id)"
              aria-label="Delete image"
            >
              ✕
            </button>
          </div>
        </div>

        <div class="mt-4">
          <div v-if="addImagesTarget === event.id" class="flex items-center gap-3">
            <input type="file" accept="image/*" multiple @change="(e) => (addImagesFiles = (e.target as HTMLInputElement).files)" />
            <button type="button" class="bg-blue-900 text-white px-3 py-1.5 rounded text-sm" @click="submitAddImages(event.id)">Upload</button>
            <button type="button" class="text-sm text-gray-500" @click="addImagesTarget = null">Cancel</button>
          </div>
          <button v-else type="button" class="text-sm text-blue-700 hover:underline" @click="addImagesTarget = event.id">
            + Add more images
          </button>
        </div>
      </div>

      <div v-if="events.length === 0" class="text-center py-8 text-gray-500">No gallery events yet.</div>
    </div>

    <ConfirmDialog
      :open="eventConfirm.open"
      title="Delete gallery event"
      message="This will delete the event and all its images. This cannot be undone."
      confirm-label="Delete"
      danger
      @confirm="confirmDeleteEvent"
      @cancel="eventConfirm.open = false"
    />
    <ConfirmDialog
      :open="imageConfirm.open"
      title="Delete image"
      message="Are you sure you want to delete this image?"
      confirm-label="Delete"
      danger
      @confirm="confirmDeleteImage"
      @cancel="imageConfirm.open = false"
    />
  </div>
</template>
