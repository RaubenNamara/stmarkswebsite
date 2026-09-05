<template>
  <div>
    <!-- Page Title -->
    <h1 class="text-2xl font-bold mb-6">Media Upload</h1>

    <!-- Success -->
    <div
      v-if="$page.props.flash?.success"
      class="mb-4 p-3 bg-green-100 text-green-700 rounded"
    >
      {{ $page.props.flash.success }}
    </div>

    <!-- ================= Upload Form ================= -->
    <div class="bg-white p-6 rounded-lg shadow max-w-xl mb-10">
      <form @submit.prevent="submit">
        <!-- TITLE -->
        <div class="mb-4">
          <label class="block mb-1 font-medium">Title</label>
          <input
            v-model="form.title"
            type="text"
            class="w-full border rounded px-3 py-2"
          />
        </div>

        <!-- TYPE -->
        <div class="mb-4">
          <label class="block mb-1 font-medium">Media Type</label>
          <select
            v-model="form.type"
            class="w-full border rounded px-3 py-2"
          >
            <option value="">Select Type</option>
            <option value="image">Image</option>
            <option value="video">Video File</option>
            <option value="link">YouTube Link</option>
          </select>
        </div>

        <!-- FILE -->
        <div v-if="form.type === 'image' || form.type === 'video'" class="mb-4">
          <label class="block mb-1 font-medium">Upload File</label>
          <input
            type="file"
            @change="handleFile"
            class="w-full border rounded px-3 py-2"
          />
        </div>

        <!-- YOUTUBE -->
        <div v-if="form.type === 'link'" class="mb-4">
          <label class="block mb-1 font-medium">YouTube URL</label>
          <input
            v-model="form.video_url"
            type="url"
            class="w-full border rounded px-3 py-2"
          />
        </div>

        <!-- PREVIEW -->
        <div v-if="previewUrl" class="mb-4">
          <img
            v-if="form.type === 'image'"
            :src="previewUrl"
            class="w-full h-48 object-cover rounded"
          />
          <video
            v-if="form.type === 'video'"
            :src="previewUrl"
            controls
            class="w-full h-48 object-cover rounded"
          ></video>
        </div>

        <!-- SUBMIT -->
        <button
          type="submit"
          class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
          :disabled="form.processing"
        >
          {{ form.processing ? 'Uploading...' : 'Upload Media' }}
        </button>
      </form>
    </div>

    <!-- ================= MEDIA CARDS ================= -->
    <h2 class="text-xl font-semibold mb-4">Uploaded Media</h2>

    <div
      v-if="mediaItems.length"
      class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6"
    >
      <div
        v-for="item in mediaItems"
        :key="item.id"
        class="bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden"
      >
        <!-- IMAGE -->
        <img
          v-if="item.type === 'image' && item.file_url"
          :src="item.file_url"
          class="w-full h-48 object-cover"
        />

        <!-- VIDEO -->
        <video
          v-else-if="item.type === 'video' && item.file_url"
          controls
          class="w-full h-48 object-cover"
        >
          <source :src="item.file_url" type="video/mp4" />
        </video>

        <!-- YOUTUBE -->
        <iframe
          v-else-if="item.type === 'link' && youtubeEmbed(item.video_url)"
          :src="youtubeEmbed(item.video_url)"
          class="w-full h-48"
          frameborder="0"
          allowfullscreen
        ></iframe>

        <!-- FALLBACK -->
        <div
          v-else
          class="w-full h-48 flex items-center justify-center text-gray-400 bg-gray-100"
        >
          No media
        </div>

        <!-- CONTENT -->
        <div class="p-4">
          <h3 class="font-semibold text-gray-800 truncate">
            {{ item.title || 'Untitled' }}
          </h3>

          <button
            type="button"
            @click="deleteMedia(item.id)"
            class="mt-3 w-full bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700"
          >
            Delete
          </button>
        </div>
      </div>
    </div>

    <div v-else class="text-gray-500">
      No media uploaded yet.
    </div>
  </div>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ref } from 'vue'

defineOptions({
  layout: AdminLayout
})

const props = defineProps({
  mediaItems: {
    type: Array,
    default: () => []
  }
})

const form = useForm({
  title: '',
  type: '',
  file: null,
  video_url: ''
})

const previewUrl = ref(null)

function handleFile(e) {
  const file = e.target.files[0]
  if (!file) return

  form.file = file
  previewUrl.value = URL.createObjectURL(file)
}

function submit() {
  form.post(route('admin.media.store'), {
    forceFormData: true,
    onSuccess: () => {
      form.reset()
      previewUrl.value = null
    }
  })
}

function deleteMedia(id) {
  if (!confirm('Are you sure you want to delete this media?')) return

  router.delete(route('admin.media.destroy', id), {
    preserveScroll: true,
    onSuccess: () => {
      // optional: you can also show a flash message from backend
    }
  })
}

/* FIXED YOUTUBE HANDLER */
function youtubeEmbed(url) {
  if (!url) return ''

  let id = null

  if (url.includes('watch?v=')) {
    id = url.split('watch?v=')[1].split('&')[0]
  }

  if (url.includes('youtu.be/')) {
    id = url.split('youtu.be/')[1].split('?')[0]
  }

  if (url.includes('/embed/')) {
    return url
  }

  return id ? `https://www.youtube.com/embed/${id}` : ''
}
</script>