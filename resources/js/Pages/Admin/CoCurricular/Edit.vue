<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  item: {
    type: Object,
    required: true
  }
})

const form = useForm({
  title: props.item.title || '',
  content: props.item.content || '',
  image: null,
  video: props.item.video || ''
})

function handleFile(e) {
  form.image = e.target.files?.[0] ?? null
}

function submit() {
  form
    .transform((data) => ({
      _method: 'put',
      title: data.title,
      content: data.content,
      video: data.video,
      image: data.image
    }))
    .post(`/admin/co-curricular/${props.item.id}`, {
      forceFormData: true,
      preserveScroll: true
    })
}
</script>

<template>
  <div class="p-8 max-w-4xl">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Edit Co-Curricular</h1>
      <p class="text-gray-500 text-sm">Update co-curricular activity details.</p>
    </div>

    <form
      @submit.prevent="submit"
      enctype="multipart/form-data"
      class="space-y-6 bg-white p-6 rounded-xl shadow"
    >
      <div>
        <label class="block mb-1 font-semibold">Title</label>
        <input
          v-model="form.title"
          name="title"
          type="text"
          class="w-full border rounded p-3 focus:ring-2 focus:ring-blue-900 focus:outline-none"
        />
        <p v-if="form.errors.title" class="text-red-500 text-sm mt-1">
          {{ form.errors.title }}
        </p>
      </div>

      <div>
        <label class="block mb-1 font-semibold">Content</label>
        <textarea
          v-model="form.content"
          name="content"
          rows="6"
          class="w-full border rounded p-3 focus:ring-2 focus:ring-blue-900 focus:outline-none"
        ></textarea>
        <p v-if="form.errors.content" class="text-red-500 text-sm mt-1">
          {{ form.errors.content }}
        </p>
      </div>

      <div v-if="props.item.image" class="mb-4">
        <p class="text-sm font-medium mb-2">Current Image</p>
        <img
          :src="`/storage/${props.item.image}`"
          class="rounded-lg max-w-xs border"
          alt="current image"
        />
      </div>

      <div>
        <label class="block mb-1 font-semibold">Replace Image</label>
        <input
          type="file"
          name="image"
          @change="handleFile"
          class="w-full border rounded p-2"
        />
        <p v-if="form.errors.image" class="text-red-500 text-sm mt-1">
          {{ form.errors.image }}
        </p>
      </div>

      <div>
        <label class="block mb-1 font-semibold">Video Link</label>
        <input
          v-model="form.video"
          name="video"
          type="text"
          class="w-full border rounded p-3 focus:ring-2 focus:ring-blue-900 focus:outline-none"
        />
        <p v-if="form.errors.video" class="text-red-500 text-sm mt-1">
          {{ form.errors.video }}
        </p>
      </div>

      <div class="flex items-center gap-4">
        <button
          type="submit"
          class="bg-blue-900 text-white px-6 py-3 rounded-lg hover:bg-blue-800 transition"
          :disabled="form.processing"
        >
          <span v-if="form.processing">Updating...</span>
          <span v-else>Update</span>
        </button>

        <Link href="/admin/co-curricular" class="text-gray-600 hover:underline">
          Cancel
        </Link>
      </div>
    </form>
  </div>
</template>

<style scoped>
button[disabled] {
  cursor: not-allowed;
  opacity: 0.6;
}
</style>
