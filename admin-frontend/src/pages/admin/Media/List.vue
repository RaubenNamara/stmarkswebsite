<script setup lang="ts">
import { onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import axios from 'axios'
import { api, apiErrorMessage } from '@/services/api'
import { useToast } from '@/composables/useToast'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import type { MediaItem } from '@/types'

// api.post's rejection only carries the top-level "Validation failed" message via
// apiErrorMessage() - the actual reason (e.g. "File exceeds the maximum allowed size of 20MB")
// lives one level deeper, in the per-field errors map MediaController::validationError() sends.
function uploadErrorMessage(e: unknown): string {
  if (axios.isAxiosError(e)) {
    const errors = e.response?.data?.errors as Record<string, string> | undefined
    if (errors) return Object.values(errors)[0] ?? apiErrorMessage(e)
  }
  return apiErrorMessage(e, 'failed')
}

// Shrinks a selected photo in the browser before it ever reaches the network - phone/camera
// photos routinely arrive at 3000-4000px+ and several MB, which is both slower to upload (real
// factor after hitting the live server's ~3MB PHP upload limit) and far larger than a homepage
// slideshow slide ever needs. 2000px on the longest side is still sharp on any screen this site
// displays a slide at (including 2xl layouts), and 0.9 JPEG quality is close to visually lossless
// for photos - this trades unnoticeable quality for a large, consistent size win. GIFs are left
// untouched (re-encoding through canvas would flatten any animation to a single frame), and PNGs
// are re-encoded as PNG (lossless - only the resize saves space) rather than converted to JPEG,
// so a transparent graphic doesn't gain a black/white background. If compression somehow produces
// a larger file than the original (already-small images, JPEG re-encode overhead), the original
// is kept instead.
async function compressImage(file: File, maxDimension = 2000, quality = 0.9): Promise<File> {
  if (file.type === 'image/gif') return file

  const objectUrl = URL.createObjectURL(file)
  try {
    const img = await new Promise<HTMLImageElement>((resolve, reject) => {
      const el = new Image()
      el.onload = () => resolve(el)
      el.onerror = () => reject(new Error('Could not read image'))
      el.src = objectUrl
    })

    const scale = Math.min(1, maxDimension / Math.max(img.naturalWidth, img.naturalHeight))
    const width = Math.round(img.naturalWidth * scale)
    const height = Math.round(img.naturalHeight * scale)

    const canvas = document.createElement('canvas')
    canvas.width = width
    canvas.height = height
    const ctx = canvas.getContext('2d')
    if (!ctx) return file
    ctx.drawImage(img, 0, 0, width, height)

    const outputType = file.type === 'image/png' ? 'image/png' : 'image/jpeg'
    const blob = await new Promise<Blob | null>((resolve) => canvas.toBlob(resolve, outputType, quality))
    if (!blob || blob.size >= file.size) return file

    const hasJpegExt = /\.jpe?g$/i.test(file.name)
    const name = outputType === 'image/jpeg' && !hasJpegExt ? file.name.replace(/\.[^.]+$/, '') + '.jpg' : file.name
    return new File([blob], name, { type: outputType })
  } catch {
    return file
  } finally {
    URL.revokeObjectURL(objectUrl)
  }
}

const toast = useToast()
const items = ref<MediaItem[]>([])
const loading = ref(false)
const submitting = ref(false)
const uploadProgress = reactive({ done: 0, total: 0 })
const confirmState = reactive({ open: false, id: null as number | null })

// Title/caption-only edit, inline per card - the old app had no update at all (create/delete
// only); this intentionally never touches the file/video, just the text overlay fields, so there's
// no file input here to re-validate or re-upload.
const editingId = ref<number | null>(null)
const editForm = reactive({ title: '', caption: '' })
const savingEdit = ref(false)

function startEdit(item: MediaItem) {
  editingId.value = item.id
  editForm.title = item.title ?? ''
  editForm.caption = item.caption ?? ''
}
function cancelEdit() {
  editingId.value = null
}
async function saveEdit(id: number) {
  savingEdit.value = true
  try {
    await api.post(`/admin/media/${id}`, { title: editForm.title, caption: editForm.caption })
    toast.success('Media item updated')
    editingId.value = null
    await fetchAll()
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to update media item'))
  } finally {
    savingEdit.value = false
  }
}

interface PendingImage {
  key: number
  file: File
  title: string
  caption: string
  previewUrl: string
}

let pendingKeySeq = 0
const pendingImages = ref<PendingImage[]>([])
const fileInput = ref<HTMLInputElement | null>(null)
const preparing = ref(false)

const videoUrl = ref('')
const videoUrlTitle = ref('')
const videoUrlCaption = ref('')
const submittingLink = ref(false)

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

async function onFilesChosen(e: Event) {
  const files = (e.target as HTMLInputElement).files
  if (!files) return
  const chosen = Array.from(files)
  if (fileInput.value) fileInput.value.value = ''

  preparing.value = true
  try {
    for (const original of chosen) {
      const file = await compressImage(original)
      pendingImages.value.push({
        key: pendingKeySeq++,
        file,
        title: '',
        caption: '',
        previewUrl: URL.createObjectURL(file),
      })
    }
  } finally {
    preparing.value = false
  }
}

function removePending(key: number) {
  const idx = pendingImages.value.findIndex((p) => p.key === key)
  if (idx === -1) return
  URL.revokeObjectURL(pendingImages.value[idx].previewUrl)
  pendingImages.value.splice(idx, 1)
}

function clearPending() {
  for (const p of pendingImages.value) URL.revokeObjectURL(p.previewUrl)
  pendingImages.value = []
}

onBeforeUnmount(clearPending)

async function uploadAll() {
  if (!pendingImages.value.length) return
  submitting.value = true
  uploadProgress.done = 0
  uploadProgress.total = pendingImages.value.length

  const failedKeys: number[] = []
  const failureMessages: string[] = []
  for (const p of pendingImages.value) {
    const body = new FormData()
    body.append('title', p.title)
    body.append('caption', p.caption)
    body.append('file', p.file)
    try {
      await api.post('/admin/media', body)
      uploadProgress.done++
    } catch (e) {
      failedKeys.push(p.key)
      failureMessages.push(`${p.file.name}: ${uploadErrorMessage(e)}`)
    }
  }

  submitting.value = false
  if (failedKeys.length) {
    toast.error(failureMessages.join('; '))
    pendingImages.value = pendingImages.value.filter((p) => failedKeys.includes(p.key))
  } else {
    toast.success(uploadProgress.total > 1 ? 'Media items created' : 'Media item created')
    clearPending()
  }
  await fetchAll()
}

async function submitVideoUrl() {
  if (!videoUrl.value.trim()) return
  const body = new FormData()
  body.append('title', videoUrlTitle.value)
  body.append('caption', videoUrlCaption.value)
  body.append('video_url', videoUrl.value)

  submittingLink.value = true
  try {
    await api.post('/admin/media', body)
    toast.success('Media item created')
    videoUrl.value = ''
    videoUrlTitle.value = ''
    videoUrlCaption.value = ''
    await fetchAll()
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to create media item'))
  } finally {
    submittingLink.value = false
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
      <h2 class="text-xl font-bold mb-1">Add Images</h2>
      <p class="text-sm text-gray-500 mb-4">
        Select one or more images at once. Give each a title and a short text - together they play back on the homepage as a slideshow.
        Large photos are automatically compressed for a faster upload, at essentially no visible loss in quality.
      </p>

      <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-dashed border-gray-300 px-4 py-4 text-sm text-gray-500 transition hover:border-blue-500/50 hover:bg-blue-50/50" :class="preparing ? 'pointer-events-none opacity-60' : ''">
        <svg v-if="!preparing" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 8a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2v-6a2 2 0 00-2-2M4 8V6a2 2 0 012-2h4l2 2h6a2 2 0 012 2v2" /></svg>
        <svg v-else class="h-5 w-5 shrink-0 animate-spin text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" /></svg>
        <span>{{ preparing ? 'Compressing image(s)...' : 'Choose image(s) to add…' }}</span>
        <input ref="fileInput" type="file" accept="image/*" multiple class="hidden" :disabled="preparing" @change="onFilesChosen" />
      </label>

      <div v-if="pendingImages.length" class="mt-5 space-y-4">
        <div v-for="p in pendingImages" :key="p.key" class="flex gap-4 rounded-lg border border-gray-200 p-3">
          <img :src="p.previewUrl" class="h-24 w-32 shrink-0 rounded-md object-cover ring-1 ring-gray-200" />
          <div class="flex-1 space-y-2">
            <input v-model="p.title" type="text" placeholder="Title" class="w-full border p-2 rounded text-sm" />
            <textarea v-model="p.caption" rows="2" placeholder="Text to show over this image" class="w-full border p-2 rounded text-sm"></textarea>
          </div>
          <button type="button" class="self-start text-gray-400 hover:text-red-600" aria-label="Remove" @click="removePending(p.key)">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>

        <div class="flex items-center gap-3">
          <button type="button" :disabled="submitting" class="bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded disabled:opacity-50" @click="uploadAll">
            {{ submitting ? `Uploading ${uploadProgress.done}/${uploadProgress.total}...` : `Upload ${pendingImages.length} image${pendingImages.length > 1 ? 's' : ''}` }}
          </button>
          <button type="button" :disabled="submitting" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 disabled:opacity-50" @click="clearPending">Clear</button>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-bold mb-4">Add External Video</h2>
      <form class="space-y-4" @submit.prevent="submitVideoUrl">
        <div>
          <label class="block text-sm font-medium mb-1">Title</label>
          <input v-model="videoUrlTitle" type="text" class="w-full border p-2 rounded" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Text</label>
          <textarea v-model="videoUrlCaption" rows="2" class="w-full border p-2 rounded"></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">External Video URL (e.g. YouTube link)</label>
          <input v-model="videoUrl" type="url" required placeholder="https://..." class="w-full border p-2 rounded" />
        </div>
        <button type="submit" :disabled="submittingLink" class="bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded disabled:opacity-50">
          {{ submittingLink ? 'Saving...' : 'Save' }}
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
            <template v-if="editingId === item.id">
              <input v-model="editForm.title" type="text" placeholder="Title" class="w-full border p-2 rounded text-sm mb-2" />
              <textarea v-model="editForm.caption" rows="2" placeholder="Text to show over this image" class="w-full border p-2 rounded text-sm mb-2"></textarea>
              <div class="mt-auto flex justify-end gap-2">
                <button type="button" :disabled="savingEdit" class="px-2 py-1 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50" @click="cancelEdit">Cancel</button>
                <button type="button" :disabled="savingEdit" class="bg-blue-900 hover:bg-blue-800 text-white px-2 py-1 rounded text-sm disabled:opacity-50" @click="saveEdit(item.id)">
                  {{ savingEdit ? 'Saving...' : 'Save' }}
                </button>
              </div>
            </template>
            <template v-else>
              <h3 class="font-semibold mb-1 truncate">{{ item.title || '(untitled)' }}</h3>
              <p v-if="item.caption" class="text-sm text-gray-500 mb-2 line-clamp-2">{{ item.caption }}</p>
              <div class="mt-auto flex justify-end gap-2">
                <button type="button" class="border border-blue-900 text-blue-900 px-2 py-1 rounded text-sm" @click="startEdit(item)">Edit</button>
                <button type="button" class="bg-red-600 text-white px-2 py-1 rounded text-sm" @click="requestDelete(item.id)">Delete</button>
              </div>
            </template>
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
