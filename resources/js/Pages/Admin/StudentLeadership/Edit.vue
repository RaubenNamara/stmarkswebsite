<script setup>
import { computed, ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useForm, Link } from '@inertiajs/vue3'

defineOptions({
  layout: AdminLayout
})

const props = defineProps({
  leader: {
    type: Object,
    required: true
  }
})

const form = useForm({
  title: props.leader.title ?? '',
  content: props.leader.content ?? '',
  image_path: null,
  video_path: null,
  video_link: props.leader.video_link ?? ''
})

const imageFile = ref(null)
const videoFile = ref(null)

/**
 * PREVIEWS
 */
const imagePreview = computed(() => {
  return imageFile.value ? URL.createObjectURL(imageFile.value) : null
})

const videoPreview = computed(() => {
  return videoFile.value ? URL.createObjectURL(videoFile.value) : null
})

/**
 * EXISTING MEDIA
 */
function mediaUrl(path) {
  if (!path) return ''
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  if (path.startsWith('/')) return path
  return `/storage/${path}`
}

/**
 * IMAGE COMPRESSION
 */
function compressImage(file, quality = 0.7, maxWidth = 1200) {
  return new Promise((resolve) => {
    const reader = new FileReader()
    reader.readAsDataURL(file)

    reader.onload = (event) => {
      const img = new Image()
      img.src = event.target.result

      img.onload = () => {
        const canvas = document.createElement('canvas')

        let width = img.width
        let height = img.height

        if (width > maxWidth) {
          height = height * (maxWidth / width)
          width = maxWidth
        }

        canvas.width = width
        canvas.height = height

        const ctx = canvas.getContext('2d')
        ctx.drawImage(img, 0, 0, width, height)

        canvas.toBlob(
          (blob) => {
            const compressedFile = new File([blob], file.name, {
              type: 'image/jpeg',
              lastModified: Date.now()
            })
            resolve(compressedFile)
          },
          'image/jpeg',
          quality
        )
      }
    }
  })
}

/**
 * HANDLE IMAGE
 */
async function onImageChange(e) {
  const file = e.target.files?.[0]
  if (!file) return

  const compressed = await compressImage(file)

  imageFile.value = compressed
  form.image_path = compressed
}

/**
 * HANDLE VIDEO
 */
function onVideoChange(e) {
  const file = e.target.files?.[0]
  videoFile.value = file
  form.video_path = file
}

/**
 * SUBMIT (FIXED)
 */
function submit() {
  form
    .transform((data) => ({
      ...data,
      _method: 'put'
    }))
    .post(route('admin.student-leadership.update', props.leader.id), {
      forceFormData: true,
      preserveScroll: true
    })
}
</script>

<template>
  <div class="p-10">
    <div class="mb-8">
      <h2 class="text-3xl font-bold text-gray-800">
        Edit Student Leadership Item
      </h2>
      <p class="text-gray-500 mt-1">
        Update the photo first, then the article content below it.
      </p>
    </div>

    <form @submit.prevent="submit" class="bg-white p-6 rounded-2xl shadow-md border">
      <div class="grid md:grid-cols-2 gap-6">

        <!-- TITLE -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium mb-2">Title</label>
          <input
            v-model="form.title"
            type="text"
            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-900 focus:outline-none"
            required
          />
          <p v-if="form.errors.title" class="text-xs text-red-500 mt-1">
            {{ form.errors.title }}
          </p>
        </div>

        <!-- IMAGE -->
        <div>
          <label class="block text-sm font-medium mb-2">
            Replace Photo (Compressed)
          </label>
          <input type="file" @change="onImageChange" accept="image/*" />

          <p v-if="form.errors.image_path" class="text-xs text-red-500 mt-1">
            {{ form.errors.image_path }}
          </p>

          <div class="mt-4">
            <img
              v-if="imagePreview || mediaUrl(props.leader.image_path)"
              :src="imagePreview || mediaUrl(props.leader.image_path)"
              class="w-full h-56 object-cover rounded-xl border"
              alt="Current photo"
            />
          </div>
        </div>

        <!-- VIDEO -->
        <div>
          <label class="block text-sm font-medium mb-2">
            Replace Video File
          </label>
          <input type="file" @change="onVideoChange" accept="video/*" />

          <p v-if="form.errors.video_path" class="text-xs text-red-500 mt-1">
            {{ form.errors.video_path }}
          </p>

          <div class="mt-4">
            <video
              v-if="videoPreview || mediaUrl(props.leader.video_path)"
              controls
              class="w-full rounded-xl border bg-black"
            >
              <source :src="videoPreview || mediaUrl(props.leader.video_path)" />
            </video>
          </div>
        </div>

        <!-- CONTENT -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium mb-2">Content</label>
          <textarea
            v-model="form.content"
            rows="8"
            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-900 focus:outline-none"
          ></textarea>

          <p v-if="form.errors.content" class="text-xs text-red-500 mt-1">
            {{ form.errors.content }}
          </p>
        </div>

        <!-- VIDEO LINK -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium mb-2">Video Link</label>
          <input
            v-model="form.video_link"
            type="url"
            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-900 focus:outline-none"
            placeholder="https://www.youtube.com/watch?v=..."
          />

          <p v-if="form.errors.video_link" class="text-xs text-red-500 mt-1">
            {{ form.errors.video_link }}
          </p>
        </div>

      </div>

      <!-- BUTTONS -->
      <div class="mt-8 flex gap-3">
        <button
          type="submit"
          class="bg-blue-900 hover:bg-blue-800 text-white px-6 py-2 rounded-lg transition"
          :disabled="form.processing"
        >
          <span v-if="form.processing">Updating...</span>
          <span v-else>Update</span>
        </button>

        <Link
          :href="route('admin.student-leadership.index')"
          class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg transition"
        >
          Cancel
        </Link>
      </div>
    </form>
  </div>
</template>