<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { api, apiErrorMessage } from '@/services/api'
import { useToast } from '@/composables/useToast'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import type { Club } from '@/types'

const toast = useToast()
const clubs = ref<Club[]>([])
const loading = ref(false)
const submitting = ref(false)
const clubConfirm = reactive({ open: false, id: null as number | null })
const imageConfirm = reactive({ open: false, id: null as number | null })

const title = ref('')
const content = ref('')
const files = ref<FileList | null>(null)
const addImagesTarget = ref<number | null>(null)
const addImagesFiles = ref<FileList | null>(null)
const editingTarget = ref<number | null>(null)
const editForm = reactive({ title: '', content: '' })
const editSubmitting = ref(false)

async function fetchAll() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/clubs')
    clubs.value = data.data?.clubs ?? []
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to load clubs'))
  } finally {
    loading.value = false
  }
}

async function submit() {
  const body = new FormData()
  body.append('title', title.value)
  body.append('content', content.value)
  if (files.value) {
    for (const f of Array.from(files.value)) body.append('images[]', f)
  }

  submitting.value = true
  try {
    await api.post('/admin/clubs', body)
    toast.success('Club created')
    title.value = ''
    content.value = ''
    files.value = null
    await fetchAll()
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to create club'))
  } finally {
    submitting.value = false
  }
}

async function submitAddImages(club: Club) {
  if (!addImagesFiles.value?.length) return
  const body = new FormData()
  body.append('title', club.title)
  body.append('content', club.content)
  for (const f of Array.from(addImagesFiles.value)) body.append('images[]', f)

  try {
    await api.post(`/admin/clubs/${club.id}`, body)
    toast.success('Images added')
    addImagesTarget.value = null
    addImagesFiles.value = null
    await fetchAll()
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to add images'))
  }
}

function startEdit(club: Club) {
  editingTarget.value = club.id
  editForm.title = club.title
  editForm.content = club.content
}

async function submitEdit(id: number) {
  editSubmitting.value = true
  try {
    await api.post(`/admin/clubs/${id}`, { title: editForm.title, content: editForm.content })
    toast.success('Club updated')
    editingTarget.value = null
    await fetchAll()
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to update club'))
  } finally {
    editSubmitting.value = false
  }
}

function requestDeleteClub(id: number) {
  clubConfirm.open = true
  clubConfirm.id = id
}

async function confirmDeleteClub() {
  const id = clubConfirm.id
  clubConfirm.open = false
  if (id === null) return
  try {
    await api.delete(`/admin/clubs/${id}`)
    clubs.value = clubs.value.filter((c) => c.id !== id)
    toast.success('Club deleted')
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to delete club'))
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
    await api.delete(`/admin/clubs/image/${id}`)
    toast.success('Image deleted')
    await fetchAll()
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to delete image'))
  }
}

onMounted(fetchAll)
</script>

<template>
  <div class="p-6 sm:p-8 space-y-8 max-w-5xl mx-auto">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Manage Clubs</h1>
      <p class="mt-1 text-sm text-gray-500">{{ clubs.length }} {{ clubs.length === 1 ? 'club' : 'clubs' }} published</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6 sm:p-8">
      <h2 class="text-lg font-bold text-gray-900 mb-6">Create Club</h2>

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
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Content</label>
          <textarea
            v-model="content"
            rows="4"
            class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20"
          ></textarea>
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Images</label>
          <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-dashed border-gray-300 px-3.5 py-3 text-sm text-gray-500 transition hover:border-blue-500/50 hover:bg-blue-50/50">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 8a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2v-6a2 2 0 00-2-2M4 8V6a2 2 0 012-2h4l2 2h6a2 2 0 012 2v2" /></svg>
            <span class="truncate">{{ files?.length ? `${files.length} image${files.length > 1 ? 's' : ''} selected` : 'Choose images' }}</span>
            <input type="file" accept="image/*" multiple class="hidden" @change="(e) => (files = (e.target as HTMLInputElement).files)" />
          </label>
        </div>

        <button type="submit" :disabled="submitting" class="bg-blue-900 hover:bg-blue-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition disabled:opacity-50">
          {{ submitting ? 'Saving...' : 'Create Club' }}
        </button>
      </form>
    </div>

    <div v-if="loading" class="flex items-center justify-center gap-2 py-12 text-gray-400">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" /></svg>
      <span class="text-sm">Loading...</span>
    </div>

    <div v-else-if="clubs.length" class="space-y-6">
      <div v-for="club in clubs" :key="club.id" class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6 sm:p-8">
        <div v-if="editingTarget === club.id" class="space-y-3 mb-5">
          <input
            v-model="editForm.title"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm font-bold shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20"
          />
          <textarea
            v-model="editForm.content"
            rows="3"
            class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20"
          ></textarea>
          <div class="flex gap-3">
            <button type="button" :disabled="editSubmitting" class="bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition disabled:opacity-50" @click="submitEdit(club.id)">
              Save
            </button>
            <button type="button" class="text-sm font-medium text-gray-500 hover:text-gray-700 hover:underline" @click="editingTarget = null">Cancel</button>
          </div>
        </div>
        <template v-else>
          <div class="flex items-start justify-between gap-4 mb-2">
            <h3 class="text-lg font-bold text-gray-900">{{ club.title }}</h3>
            <div class="flex shrink-0 gap-2">
              <button type="button" class="border border-blue-900 text-blue-900 px-2.5 py-1 rounded-md text-xs font-semibold hover:bg-blue-50 transition" @click="startEdit(club)">
                Edit
              </button>
              <button type="button" class="bg-red-600 hover:bg-red-700 text-white px-2.5 py-1 rounded-md text-xs font-semibold transition" @click="requestDeleteClub(club.id)">
                Delete Club
              </button>
            </div>
          </div>
          <p class="text-sm text-gray-600 mb-5 whitespace-pre-line">{{ club.content }}</p>
        </template>

        <div v-if="club.images.length" class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-3">
          <div v-for="img in club.images" :key="img.id" class="group relative aspect-square overflow-hidden rounded-lg ring-1 ring-gray-200">
            <img :src="img.image_url ?? ''" class="h-full w-full object-cover" />
            <button
              type="button"
              class="absolute top-1.5 right-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-white text-xs opacity-0 shadow transition group-hover:opacity-100 hover:bg-red-700"
              @click="requestDeleteImage(img.id)"
              aria-label="Delete image"
            >
              ✕
            </button>
          </div>
        </div>
        <p v-else class="text-xs text-gray-400">No images yet.</p>

        <div class="mt-5">
          <div v-if="addImagesTarget === club.id" class="flex flex-wrap items-center gap-3">
            <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-dashed border-gray-300 px-3.5 py-2.5 text-sm text-gray-500 transition hover:border-blue-500/50 hover:bg-blue-50/50">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 8a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2v-6a2 2 0 00-2-2M4 8V6a2 2 0 012-2h4l2 2h6a2 2 0 012 2v2" /></svg>
              <span class="truncate">{{ addImagesFiles?.length ? `${addImagesFiles.length} selected` : 'Choose images' }}</span>
              <input type="file" accept="image/*" multiple class="hidden" @change="(e) => (addImagesFiles = (e.target as HTMLInputElement).files)" />
            </label>
            <button type="button" class="bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition" @click="submitAddImages(club)">Upload</button>
            <button type="button" class="text-sm font-medium text-gray-500 hover:text-gray-700 hover:underline" @click="addImagesTarget = null">Cancel</button>
          </div>
          <button v-else type="button" class="text-sm font-semibold text-blue-700 hover:underline" @click="addImagesTarget = club.id">
            + Add more images
          </button>
        </div>
      </div>
    </div>

    <div v-else class="flex flex-col items-center gap-2 rounded-xl border-2 border-dashed border-gray-200 py-16 text-center">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" /></svg>
      <p class="text-sm text-gray-400">No clubs yet.</p>
    </div>

    <ConfirmDialog
      :open="clubConfirm.open"
      title="Delete club"
      message="This will delete the club and all its images. This cannot be undone."
      confirm-label="Delete"
      danger
      @confirm="confirmDeleteClub"
      @cancel="clubConfirm.open = false"
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
