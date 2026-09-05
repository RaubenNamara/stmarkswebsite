<template>
  <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">

    <!-- Page header -->
    <div class="flex items-center justify-between mb-8">
      <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Gallery Management</h1>
    </div>

    <!-- Flash -->
    <div v-if="flash?.success" class="mb-6">
      <div class="rounded-md bg-green-50 p-4">
        <p class="text-sm text-green-800">{{ flash.success }}</p>
      </div>
    </div>

    <div v-if="flash?.error" class="mb-6">
      <div class="rounded-md bg-red-50 p-4">
        <p class="text-sm text-red-800">{{ flash.error }}</p>
      </div>
    </div>

    <!-- Create Event Card -->
    <section class="bg-white p-6 rounded-2xl shadow mb-10">
      <h2 class="text-lg font-semibold text-gray-800 mb-4">Create new gallery event</h2>

      <form @submit.prevent="submit" class="space-y-4">

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Event title</label>
          <input
            v-model="form.title"
            type="text"
            placeholder="e.g. Sports Day 2026"
            required
            class="w-full rounded-lg border-gray-300 shadow-sm px-4 py-2 focus:ring-2 focus:ring-indigo-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea
            v-model="form.description"
            rows="4"
            placeholder="Optional event description..."
            class="w-full rounded-lg border-gray-300 shadow-sm px-4 py-2 focus:ring-2 focus:ring-indigo-500"
          ></textarea>
        </div>

        <!-- Drag & Drop area -->
        <div
          class="mt-2 border-2 border-dashed rounded-lg p-6 text-center cursor-pointer hover:border-indigo-400 transition"
          :class="dragActive ? 'bg-indigo-50 border-indigo-400' : 'bg-white'"
          @dragover.prevent="onDragOver"
          @dragenter.prevent="onDragEnter"
          @dragleave.prevent="onDragLeave"
          @drop.prevent="onDrop"
          @click="fileInput?.click()"
        >
          <input
            ref="fileInput"
            type="file"
            multiple
            accept="image/*"
            class="hidden"
            @change="handleFiles"
          />
          <p class="text-sm text-gray-600">
            Drag & drop images here, or <span class="text-indigo-600 underline">browse</span> to choose files.
            <br />
            <span class="text-xs text-gray-400">JPG, PNG, WebP. Images are compressed before upload.</span>
          </p>
        </div>

        <!-- Previews -->
        <div v-if="previewImages.length" class="grid grid-cols-3 sm:grid-cols-6 gap-3 mt-4">
          <div v-for="(p, idx) in previewImages" :key="idx" class="relative group">
            <img :src="p.url" class="w-full h-28 object-cover rounded-md border" />
            <button
              type="button"
              @click="removePreview(idx)"
              class="absolute top-1 right-1 bg-white/80 rounded-full p-1 opacity-0 group-hover:opacity-100 transition"
              title="Remove"
            >
              ✕
            </button>
          </div>
        </div>

        <!-- Validation errors -->
        <div v-if="hasErrors" class="text-sm text-red-600">
          <ul>
            <li v-for="(err, key) in form.errors" :key="key">{{ err }}</li>
          </ul>
        </div>

        <div class="flex items-center gap-3">
          <button
            type="submit"
            :disabled="form.processing || compressing"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white disabled:opacity-60"
          >
            <span v-if="compressing">Compressing...</span>
            <span v-else-if="form.processing">Uploading...</span>
            <span v-else>Upload Event & Images</span>
          </button>

          <button type="button" @click="resetForm" class="px-3 py-2 rounded-lg border text-gray-700">
            Reset
          </button>

          <div class="ml-auto text-sm text-gray-500">
            {{ previewImages.length }} image(s) ready
          </div>
        </div>
      </form>
    </section>

    <!-- Events list -->
    <section>
      <h3 class="text-xl font-semibold text-gray-800 mb-4">Events</h3>

      <div v-if="events.length" class="space-y-8">
        <article v-for="event in events" :key="event.id" class="bg-white p-5 rounded-2xl shadow">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 sm:gap-0">
            <div>
              <h4 class="text-lg font-bold text-gray-900">{{ event.title }}</h4>
              <p v-if="event.description" class="text-sm text-gray-600 mt-1">{{ event.description }}</p>
            </div>

            <div class="flex items-center gap-3">
              <!-- Add more images inline -->
              <label class="inline-flex items-center gap-2 cursor-pointer px-3 py-2 border rounded-lg text-indigo-600 hover:bg-indigo-50">
                + Add more
                <input
                  type="file"
                  accept="image/*"
                  multiple
                  class="hidden"
                  @change="onAddMore($event, event.id)"
                />
              </label>

              <button
                @click="deleteEvent(event.id)"
                class="px-3 py-2 rounded-lg border text-red-600 hover:bg-red-50"
              >
                Delete event
              </button>
            </div>
          </div>

          <!-- images grid -->
          <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            <div v-for="img in event.images" :key="img.id" class="rounded overflow-hidden bg-gray-50 relative">
              <img :src="imageUrl(img.image_path)" class="w-full h-48 object-cover" :alt="event.title" loading="lazy" />

              <!-- single-image delete button overlay -->
              <button
                @click="confirmDeleteImage(img.id)"
                class="absolute top-2 right-2 bg-red-600 text-white rounded-full p-2 shadow hover:bg-red-700 transition"
                title="Delete image"
              >
                🗑
              </button>
            </div>
          </div>
        </article>
      </div>

      <div v-else class="text-gray-600">No events yet.</div>
    </section>

  </div>
</template>

<script setup>
import { ref, computed, onBeforeUnmount } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  events: {
    type: Array,
    default: () => []
  }
})

const page = usePage()
const flash = computed(() => page.props.value?.flash ?? {})

const form = useForm({
  title: '',
  description: '',
  images: []
})

const fileInput = ref(null)
const dragActive = ref(false)
const previewImages = ref([]) // { file, url }
const compressing = ref(false)

const hasErrors = computed(() => Object.keys(form.errors || {}).length > 0)

function imageUrl(path) {
  if (!path) return ''
  return `/storage/${path}`
}

function getCompressedFileName(originalName) {
  const baseName = originalName.replace(/\.[^.]+$/, '')
  return `${baseName}.jpg`
}

function compressImage(file, maxWidth = 1600, quality = 0.75) {
  return new Promise((resolve, reject) => {
    if (!file || !file.type.startsWith('image/')) {
      reject(new Error('Invalid image file'))
      return
    }

    const img = new Image()
    const reader = new FileReader()

    reader.onerror = () => reject(new Error('Failed to read file'))
    reader.onload = (e) => {
      img.src = e.target.result
    }

    img.onerror = () => reject(new Error('Failed to load image'))

    img.onload = () => {
      const canvas = document.createElement('canvas')
      let width = img.width
      let height = img.height

      if (width > maxWidth) {
        height = Math.round(height * (maxWidth / width))
        width = maxWidth
      }

      canvas.width = width
      canvas.height = height

      const ctx = canvas.getContext('2d')
      if (!ctx) {
        reject(new Error('Canvas context not available'))
        return
      }

      ctx.drawImage(img, 0, 0, width, height)

      canvas.toBlob(
        (blob) => {
          if (!blob) {
            reject(new Error('Compression failed'))
            return
          }

          const compressedFile = new File(
            [blob],
            getCompressedFileName(file.name),
            {
              type: 'image/jpeg',
              lastModified: Date.now()
            }
          )

          resolve(compressedFile)
        },
        'image/jpeg',
        quality
      )
    }

    reader.readAsDataURL(file)
  })
}

/* FILE HANDLING FOR CREATE */
async function handleFiles(e) {
  const files = Array.from(e.target.files || [])
  await addPreviews(files)
  if (fileInput.value) fileInput.value.value = null
}

function onDragOver() {
  dragActive.value = true
}

function onDragEnter() {
  dragActive.value = true
}

function onDragLeave() {
  dragActive.value = false
}

async function onDrop(e) {
  dragActive.value = false
  const items = e.dataTransfer?.files ? Array.from(e.dataTransfer.files) : []
  await addPreviews(items)
}

async function addPreviews(files) {
  const accepted = files.filter(f => f.type.startsWith('image/'))
  if (!accepted.length) return

  compressing.value = true

  try {
    const compressedFiles = await Promise.all(
      accepted.map(file => compressImage(file, 1600, 0.75))
    )

    const mapped = compressedFiles.map(f => ({
      file: f,
      url: URL.createObjectURL(f)
    }))

    previewImages.value.push(...mapped)
    form.images = previewImages.value.map(p => p.file)
  } catch (error) {
    console.error(error)
    alert('One or more images could not be compressed.')
  } finally {
    compressing.value = false
  }
}

function removePreview(index) {
  const p = previewImages.value[index]
  if (p?.url) URL.revokeObjectURL(p.url)
  previewImages.value.splice(index, 1)
  form.images = previewImages.value.map(p => p.file)
}

function resetForm() {
  form.reset('title', 'description', 'images')
  form.clearErrors()

  previewImages.value.forEach(p => {
    if (p.url) URL.revokeObjectURL(p.url)
  })

  previewImages.value = []
  if (fileInput.value) fileInput.value.value = null
}

/* SUBMIT create event */
async function submit() {
  if (!form.title) return

  if (!form.images || form.images.length === 0) {
    form.setErrors({ images: 'Please choose at least one image.' })
    return
  }

  form.post(route('admin.gallery.store'), {
    forceFormData: true,
    onSuccess: () => {
      resetForm()
    },
    onError: () => {
      // keep previews so admin can fix
    }
  })
}

/* ADD MORE IMAGES to existing event */
async function onAddMore(e, eventId) {
  const files = Array.from(e.target.files || []).filter(f => f.type.startsWith('image/'))
  if (!files.length) return

  const addForm = useForm({
    images: []
  })

  try {
    compressing.value = true
    const compressed = await Promise.all(files.map(f => compressImage(f, 1600, 0.75)))
    addForm.images = compressed

    addForm.post(route('admin.gallery.add-images', eventId), {
      forceFormData: true,
      onSuccess: () => {
        window.location.reload()
      },
      onError: () => {
        alert('Failed to upload images')
      }
    })
  } catch (error) {
    console.error(error)
    alert('Failed to compress one or more images.')
  } finally {
    compressing.value = false
    e.target.value = null
  }
}

/* DELETE single image */
function confirmDeleteImage(imageId) {
  if (!confirm('Delete this image? This cannot be undone.')) return
  deleteImage(imageId)
}

function deleteImage(imageId) {
  const del = useForm()
  del.delete(route('admin.gallery.image.destroy', imageId), {
    onSuccess: () => {
      window.location.reload()
    },
    onError: () => {
      alert('Failed to delete image')
    }
  })
}

/* DELETE event (all images) */
function deleteEvent(id) {
  if (!confirm('Delete this event and all images? This action cannot be undone.')) return

  form.delete(route('admin.gallery.destroy', id), {
    onSuccess: () => {
      window.location.reload()
    },
    onError: () => {
      alert('Failed to delete event')
    }
  })
}

/* cleanup created object URLs on unmount */
onBeforeUnmount(() => {
  previewImages.value.forEach(p => {
    if (p.url) URL.revokeObjectURL(p.url)
  })
})
</script>

<style scoped>
[draggable] {
  cursor: grab;
}
</style>