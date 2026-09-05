<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { api, apiErrorMessage } from '@/services/api'
import { useToast } from '@/composables/useToast'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import type { ApiResponse, Slide } from '@/types'

const toast = useToast()
const slides = ref<Slide[]>([])
const loading = ref(false)
const submitting = ref(false)

const form = reactive({
  type: 'image' as 'image' | 'video',
  title: '',
  caption: '',
})
const file = ref<File | null>(null)
const previewUrl = ref<string | null>(null)

const confirmState = reactive({ open: false, id: null as number | null })

async function fetchSlides() {
  loading.value = true
  try {
    const { data } = await api.get<ApiResponse<{ slides: Slide[] }>>('/admin/slides')
    slides.value = data.data?.slides ?? []
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to load slides'))
  } finally {
    loading.value = false
  }
}

function handleFile(e: Event) {
  const input = e.target as HTMLInputElement
  const selected = input.files?.[0] ?? null
  file.value = selected
  previewUrl.value = selected ? URL.createObjectURL(selected) : null
}

function resetForm() {
  form.title = ''
  form.caption = ''
  file.value = null
  previewUrl.value = null
}

async function submit() {
  if (!file.value) {
    toast.error('Please choose a file to upload')
    return
  }

  const body = new FormData()
  body.append(form.type === 'video' ? 'video' : 'image', file.value)
  body.append('title', form.title)
  body.append('caption', form.caption)

  submitting.value = true
  try {
    const { data } = await api.post<ApiResponse<{ slides: Slide[] }>>('/admin/slides', body)
    slides.value = data.data?.slides ?? []
    resetForm()
    toast.success('Slide uploaded successfully')
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to upload slide'))
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
    await api.delete(`/admin/slides/${id}`)
    slides.value = slides.value.filter((s) => s.id !== id)
    toast.success('Slide deleted successfully')
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to delete slide'))
  }
}

onMounted(fetchSlides)
</script>

<template>
  <div class="py-12 bg-gray-100 min-h-screen">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-10">
      <section class="bg-white shadow-xl rounded-xl p-8">
        <h3 class="text-xl font-bold mb-6 border-b pb-3">Upload New Slide</h3>

        <div class="grid md:grid-cols-2 gap-8">
          <div>
            <label class="block font-semibold mb-2">Slide Type</label>
            <select v-model="form.type" class="border p-3 w-full rounded-lg mb-4">
              <option value="image">Image</option>
              <option value="video">Video</option>
            </select>

            <input
              type="file"
              class="border p-3 w-full rounded-lg"
              :accept="form.type === 'image' ? 'image/*' : 'video/mp4,video/mov,video/avi'"
              @change="handleFile"
            />

            <div v-if="previewUrl" class="mt-4">
              <img v-if="form.type === 'image'" :src="previewUrl" class="h-44 w-full object-cover rounded" />
              <video v-else :src="previewUrl" controls class="h-44 w-full object-cover rounded"></video>
            </div>
          </div>

          <div class="space-y-5">
            <input v-model="form.title" placeholder="Title" class="border p-3 w-full rounded" />
            <input v-model="form.caption" placeholder="Caption" class="border p-3 w-full rounded" />

            <div class="flex gap-3">
              <button
                type="button"
                :disabled="submitting"
                class="flex-1 bg-yellow-400 hover:bg-yellow-500 px-6 py-3 rounded font-bold disabled:opacity-50"
                @click="submit"
              >
                {{ submitting ? 'Uploading...' : 'Upload Slide' }}
              </button>
              <button type="button" class="px-4 py-3 border rounded" @click="resetForm">Reset</button>
            </div>
          </div>
        </div>
      </section>

      <section class="bg-white shadow-xl rounded-xl p-8">
        <h3 class="text-xl font-bold mb-6 border-b pb-3">Uploaded Slides</h3>

        <div v-if="loading" class="text-center py-10 text-gray-500">Loading...</div>
        <div v-else-if="!slides.length" class="text-center py-10 text-gray-500">No slides uploaded yet.</div>

        <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="slide in slides" :key="slide.id" class="bg-gray-50 rounded-xl shadow-md overflow-hidden">
            <img v-if="slide.type === 'image' && slide.image_url" :src="slide.image_url" class="h-44 w-full object-cover" />
            <video v-if="slide.type === 'video' && slide.video_url" :src="slide.video_url" controls class="h-44 w-full object-cover"></video>

            <div class="p-4">
              <h4 class="font-semibold">{{ slide.title }}</h4>
              <p class="text-sm text-gray-600">{{ slide.caption }}</p>

              <div class="mt-3 grid grid-cols-2 gap-3">
                <button type="button" class="bg-red-600 text-white px-3 py-2 rounded text-sm" @click="requestDelete(slide.id)">
                  Delete
                </button>
                <a :href="slide.type === 'image' ? slide.image_url! : slide.video_url!" target="_blank" class="border px-3 py-2 rounded text-sm text-center">
                  View
                </a>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <ConfirmDialog
      :open="confirmState.open"
      title="Delete slide"
      message="Are you sure you want to delete this slide? This cannot be undone."
      confirm-label="Delete"
      danger
      @confirm="confirmDelete"
      @cancel="confirmState.open = false"
    />
  </div>
</template>
