<script setup>
import { computed, ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useForm, Link } from '@inertiajs/vue3'

defineOptions({
  layout: AdminLayout
})

const form = useForm({
  title: '',
  content: '',
  image_path: null,
  video_path: null,
  video_link: ''
})

const imageFile = ref(null)
const videoFile = ref(null)

const imagePreview = computed(() => {
  return imageFile.value ? URL.createObjectURL(imageFile.value) : null
})

const videoPreview = computed(() => {
  return videoFile.value ? URL.createObjectURL(videoFile.value) : null
})

/**
 * COMPRESS IMAGE FUNCTION
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

        // Resize if too large
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
          quality // compression level (0.1 - 1)
        )
      }
    }
  })
}

/**
 * HANDLE IMAGE CHANGE WITH COMPRESSION
 */
async function onImageChange(e) {
  const file = e.target.files?.[0] ?? null
  if (!file) return

  // compress image before saving
  const compressed = await compressImage(file)

  imageFile.value = compressed
  form.image_path = compressed
}

/**
 * HANDLE VIDEO
 */
function onVideoChange(e) {
  const file = e.target.files?.[0] ?? null
  videoFile.value = file
  form.video_path = file
}

/**
 * SUBMIT
 */
function submit() {
  form.post(route('admin.student-leadership.store'), {
    forceFormData: true,
    preserveScroll: true
  })
}
</script>

<template>
  <div class="p-10">
    <div class="mb-8">
      <h2 class="text-3xl font-bold text-gray-800">Add Student Leadership Item</h2>
      <p class="text-gray-500 mt-1">
        Upload the photo first, then write the article content below it.
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
          <label class="block text-sm font-medium mb-2">Photo (Compressed)</label>
          <input type="file" @change="onImageChange" accept="image/*" />

          <p v-if="form.errors.image_path" class="text-xs text-red-500 mt-1">
            {{ form.errors.image_path }}
          </p>

          <div v-if="imagePreview" class="mt-4">
            <img
              :src="imagePreview"
              class="w-full h-56 object-cover rounded-xl border"
              alt="Photo preview"
            />
          </div>
        </div>

        <!-- VIDEO -->
        <div>
          <label class="block text-sm font-medium mb-2">Video File</label>
          <input type="file" @change="onVideoChange" accept="video/*" />

          <p v-if="form.errors.video_path" class="text-xs text-red-500 mt-1">
            {{ form.errors.video_path }}
          </p>

          <div v-if="videoPreview" class="mt-4">
            <video controls class="w-full rounded-xl border bg-black">
              <source :src="videoPreview" />
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
            placeholder="Write the article content here..."
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
          <span v-if="form.processing">Saving...</span>
          <span v-else>Save</span>
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