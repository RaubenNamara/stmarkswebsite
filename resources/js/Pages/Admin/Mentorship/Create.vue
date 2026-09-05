<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'

defineOptions({ layout: AdminLayout })

const form = useForm({
  title: '',
  caption: '',
  description: '',
  image: null,
  video: null,
  video_link: ''
})

const imagePreview = ref(null)
const videoPreview = ref(null)

function onImageChange(e) {
  const file = e.target.files[0]
  form.image = file ?? null
  imagePreview.value = file ? URL.createObjectURL(file) : null
}

function onVideoChange(e) {
  const file = e.target.files[0]
  form.video = file ?? null
  videoPreview.value = file ? URL.createObjectURL(file) : null
}

function submit() {
  form.post('/admin/mentorship/store', {
    onFinish: () => {
      // revoke object URLs to avoid memory leaks
      if (imagePreview.value) URL.revokeObjectURL(imagePreview.value)
      if (videoPreview.value) URL.revokeObjectURL(videoPreview.value)
    }
  })
}
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-semibold">Create Mentorship</h1>
      <Link href="/admin/mentorship" class="text-sm text-gray-600 hover:underline">
        ← Back
      </Link>
    </div>

    <form @submit.prevent="submit" class="space-y-6 bg-white p-6 rounded shadow">
      <div>
        <label class="block text-sm font-medium mb-1">Title</label>
        <input v-model="form.title" type="text" class="w-full border rounded px-3 py-2" />
        <p v-if="form.errors.title" class="text-red-600 text-sm mt-1">{{ form.errors.title }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Caption</label>
        <input v-model="form.caption" type="text" class="w-full border rounded px-3 py-2" />
        <p v-if="form.errors.caption" class="text-red-600 text-sm mt-1">{{ form.errors.caption }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Description</label>
        <textarea v-model="form.description" rows="5" class="w-full border rounded px-3 py-2"></textarea>
        <p v-if="form.errors.description" class="text-red-600 text-sm mt-1">{{ form.errors.description }}</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Image (jpg, png)</label>
          <input type="file" accept="image/*" @change="onImageChange" />
          <p v-if="form.errors.image" class="text-red-600 text-sm mt-1">{{ form.errors.image }}</p>
          <div v-if="imagePreview" class="mt-3">
            <img :src="imagePreview" class="w-48 h-32 object-cover rounded" alt="preview" />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Video (mp4)</label>
          <input type="file" accept="video/*" @change="onVideoChange" />
          <p v-if="form.errors.video" class="text-red-600 text-sm mt-1">{{ form.errors.video }}</p>
          <div v-if="videoPreview" class="mt-3">
            <video controls class="w-64 h-36 rounded">
              <source :src="videoPreview" />
            </video>
          </div>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">YouTube / External Video Link</label>
        <input v-model="form.video_link" type="url" class="w-full border rounded px-3 py-2" />
        <p v-if="form.errors.video_link" class="text-red-600 text-sm mt-1">{{ form.errors.video_link }}</p>
      </div>

      <div class="flex items-center gap-3">
        <button
          type="submit"
          :disabled="form.processing"
          class="bg-green-600 text-white px-4 py-2 rounded shadow"
        >
          Save
        </button>

        <Link href="/admin/mentorship" class="text-sm text-gray-600 hover:underline">Cancel</Link>
      </div>
    </form>
  </div>
</template>