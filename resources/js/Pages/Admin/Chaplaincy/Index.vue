<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({
  layout: AdminLayout
})

const props = defineProps({
  items: {
    type: Array,
    default: () => []
  }
})

const title = ref('')
const content = ref('')
const image = ref(null)
const video = ref(null)
const video_link = ref('')

const imagePreview = ref(null)
const videoPreview = ref(null)

// ==========================
// FILE HANDLING
// ==========================
function onImageChange(e) {
  const file = e.target.files[0]
  image.value = file
  imagePreview.value = file ? URL.createObjectURL(file) : null
}

function onVideoChange(e) {
  const file = e.target.files[0]
  video.value = file
  videoPreview.value = file ? URL.createObjectURL(file) : null
}

// ==========================
// SUBMIT
// ==========================
function submit() {
  if (!title.value.trim()) {
    alert('Title is required')
    return
  }

  const formData = new FormData()
  formData.append('title', title.value)
  formData.append('content', content.value ?? '')

  if (image.value) formData.append('image', image.value)
  if (video.value) formData.append('video', video.value)
  if (video_link.value) formData.append('video_link', video_link.value)

  router.post('/admin/chaplaincy', formData, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      resetForm()
    }
  })
}

// ==========================
// DELETE
// ==========================
function deleteItem(id) {
  if (!confirm('Delete this chaplaincy post?')) return

  router.delete(`/admin/chaplaincy/${id}`, {
    preserveScroll: true
  })
}

// ==========================
// RESET FORM
// ==========================
function resetForm() {
  title.value = ''
  content.value = ''
  image.value = null
  video.value = null
  video_link.value = ''
  imagePreview.value = null
  videoPreview.value = null

  const imageInput = document.querySelector('input[type="file"][accept="image/*"]')
  const videoInput = document.querySelector('input[type="file"][accept="video/*"]')

  if (imageInput) imageInput.value = ''
  if (videoInput) videoInput.value = ''
}

// ==========================
// YOUTUBE EMBED
// ==========================
function embedUrl(link) {
  if (!link) return ''

  try {
    if (link.includes('youtube.com/watch')) {
      return link.replace('watch?v=', 'embed/')
    }

    if (link.includes('youtu.be/')) {
      const id = link.split('youtu.be/')[1].split(/[?&]/)[0]
      return `https://www.youtube.com/embed/${id}`
    }

    return link
  } catch {
    return link
  }
}
</script>

<template>
  <div class="p-8">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-3xl font-bold text-gray-800">
        Chaplaincy Management
      </h1>
    </div>

    <!-- FORM -->
    <div class="bg-white shadow rounded-xl p-6 mb-10">

      <h2 class="text-xl font-semibold mb-4">
        Create Chaplaincy Post
      </h2>

      <div class="grid md:grid-cols-2 gap-6">

        <input
          v-model="title"
          placeholder="Title"
          class="border p-3 rounded"
        />

        <input
          v-model="video_link"
          placeholder="YouTube / Video Link"
          class="border p-3 rounded"
        />

      </div>

      <textarea
        v-model="content"
        rows="4"
        placeholder="Content"
        class="border p-3 rounded w-full mt-4"
      ></textarea>

      <!-- FILE INPUTS -->
      <div class="grid md:grid-cols-2 gap-6 mt-6">

        <div>
          <input type="file" accept="image/*" @change="onImageChange" />

          <img
            v-if="imagePreview"
            :src="imagePreview"
            class="mt-3 w-48 h-32 object-cover rounded"
          />
        </div>

        <div>
          <input type="file" accept="video/*" @change="onVideoChange" />

          <video
            v-if="videoPreview"
            :src="videoPreview"
            controls
            class="mt-3 w-64 h-40 object-cover rounded"
          ></video>
        </div>

      </div>

      <button
        @click="submit"
        class="mt-6 bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700"
      >
        Create Post
      </button>

    </div>

    <!-- LIST -->
    <h2 class="text-2xl font-semibold mb-6">
      All Chaplaincy Items
    </h2>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">

      <div
        v-for="item in props.items"
        :key="item.id"
        class="bg-white shadow rounded-xl overflow-hidden flex flex-col hover:shadow-lg transition"
      >

        <!-- IMAGE -->
        <img
          v-if="item.image_url"
          :src="item.image_url"
          class="w-full h-48 object-cover"
        />

        <!-- VIDEO -->
        <video
          v-else-if="item.video_url"
          controls
          class="w-full h-48 object-cover"
        >
          <source :src="item.video_url" type="video/mp4" />
        </video>

        <!-- YOUTUBE -->
        <iframe
          v-else-if="item.video_link"
          :src="embedUrl(item.video_link)"
          class="w-full h-48"
          frameborder="0"
          allowfullscreen
        ></iframe>

        <!-- TEXT -->
        <div class="p-4 flex flex-col flex-grow">

          <h3 class="text-lg font-semibold mb-2">
            {{ item.title }}
          </h3>

          <p class="text-gray-600 text-sm flex-grow">
            {{ item.content ?? '' }}
          </p>

          <button
            @click="deleteItem(item.id)"
            class="mt-4 bg-red-600 text-white py-2 px-3 rounded hover:bg-red-700"
          >
            Delete
          </button>

        </div>

      </div>

    </div>

  </div>
</template>

<style scoped>
</style>