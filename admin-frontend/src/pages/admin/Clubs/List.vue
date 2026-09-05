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
  <div class="p-8 space-y-8">
    <h1 class="text-3xl font-bold text-gray-800">Manage Clubs</h1>

    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-bold mb-4">Create Club</h2>

      <form class="space-y-4" @submit.prevent="submit">
        <div>
          <label class="block text-sm font-medium mb-1">Title</label>
          <input v-model="title" type="text" required class="w-full border p-2 rounded" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Content</label>
          <textarea v-model="content" rows="4" class="w-full border p-2 rounded"></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Images</label>
          <input type="file" accept="image/*" multiple @change="(e) => (files = (e.target as HTMLInputElement).files)" />
        </div>

        <button type="submit" :disabled="submitting" class="bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded disabled:opacity-50">
          {{ submitting ? 'Saving...' : 'Create Club' }}
        </button>
      </form>
    </div>

    <div v-if="loading" class="text-center py-8 text-gray-500">Loading...</div>

    <div v-else class="space-y-6">
      <div v-for="club in clubs" :key="club.id" class="bg-white rounded-lg shadow p-6">
        <div v-if="editingTarget === club.id" class="space-y-3 mb-4">
          <input v-model="editForm.title" type="text" class="w-full border p-2 rounded font-bold" />
          <textarea v-model="editForm.content" rows="3" class="w-full border p-2 rounded"></textarea>
          <div class="flex gap-2">
            <button type="button" :disabled="editSubmitting" class="bg-blue-900 text-white px-3 py-1.5 rounded text-sm disabled:opacity-50" @click="submitEdit(club.id)">
              Save
            </button>
            <button type="button" class="text-sm text-gray-500" @click="editingTarget = null">Cancel</button>
          </div>
        </div>
        <template v-else>
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-lg font-bold">{{ club.title }}</h3>
            <div class="flex gap-2">
              <button type="button" class="border border-blue-900 text-blue-900 px-3 py-1.5 rounded text-sm" @click="startEdit(club)">
                Edit
              </button>
              <button type="button" class="bg-red-600 text-white px-3 py-1.5 rounded text-sm" @click="requestDeleteClub(club.id)">
                Delete Club
              </button>
            </div>
          </div>
          <p class="text-sm text-gray-600 mb-4">{{ club.content }}</p>
        </template>

        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
          <div v-for="img in club.images" :key="img.id" class="relative group">
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
          <div v-if="addImagesTarget === club.id" class="flex items-center gap-3">
            <input type="file" accept="image/*" multiple @change="(e) => (addImagesFiles = (e.target as HTMLInputElement).files)" />
            <button type="button" class="bg-blue-900 text-white px-3 py-1.5 rounded text-sm" @click="submitAddImages(club)">Upload</button>
            <button type="button" class="text-sm text-gray-500" @click="addImagesTarget = null">Cancel</button>
          </div>
          <button v-else type="button" class="text-sm text-blue-700 hover:underline" @click="addImagesTarget = club.id">
            + Add more images
          </button>
        </div>
      </div>

      <div v-if="clubs.length === 0" class="text-center py-8 text-gray-500">No clubs yet.</div>
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
