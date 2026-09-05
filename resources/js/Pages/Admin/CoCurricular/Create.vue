<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({ layout: AdminLayout })

const form = useForm({
  title: '',
  content: '',
  image: null,
  video: ''
})

// Handle file upload
function handleFile(e) {
  form.image = e.target.files?.[0] ?? null
}

// Submit form
function submit() {
  form.post('/admin/co-curricular', {
    forceFormData: true,
    preserveScroll: true
  })
}
</script>

<template>
  <div class="p-8 max-w-4xl">

    <!-- HEADER -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800">
        Create Co-Curricular Content
      </h1>
      <p class="text-gray-500 text-sm">
        Add a new co-curricular activity or event.
      </p>
    </div>

    <!-- FORM -->
    <form
      @submit.prevent="submit"
      enctype="multipart/form-data"
      class="space-y-6 bg-white p-6 rounded-xl shadow"
    >

      <!-- TITLE -->
      <div>
        <label class="block mb-1 font-semibold">Title</label>
        <input
          v-model="form.title"
          type="text"
          class="w-full border rounded p-3 focus:ring-2 focus:ring-blue-900 focus:outline-none"
        />
        <p v-if="form.errors.title" class="text-red-500 text-sm mt-1">
          {{ form.errors.title }}
        </p>
      </div>

      <!-- CONTENT -->
      <div>
        <label class="block mb-1 font-semibold">Content</label>
        <textarea
          v-model="form.content"
          rows="6"
          class="w-full border rounded p-3 focus:ring-2 focus:ring-blue-900 focus:outline-none"
        ></textarea>
        <p v-if="form.errors.content" class="text-red-500 text-sm mt-1">
          {{ form.errors.content }}
        </p>
      </div>

      <!-- IMAGE -->
      <div>
        <label class="block mb-1 font-semibold">Upload Image</label>
        <input
          type="file"
          @change="handleFile"
          class="w-full border rounded p-2"
        />
        <p v-if="form.errors.image" class="text-red-500 text-sm mt-1">
          {{ form.errors.image }}
        </p>
      </div>

      <!-- VIDEO -->
      <div>
        <label class="block mb-1 font-semibold">
          Video Link (YouTube or MP4 URL)
        </label>
        <input
          v-model="form.video"
          type="text"
          placeholder="https://youtube.com/..."
          class="w-full border rounded p-3 focus:ring-2 focus:ring-blue-900 focus:outline-none"
        />
        <p v-if="form.errors.video" class="text-red-500 text-sm mt-1">
          {{ form.errors.video }}
        </p>
      </div>

      <!-- BUTTONS -->
      <div class="flex items-center gap-4">

        <button
          type="submit"
          class="bg-blue-900 text-white px-6 py-3 rounded-lg hover:bg-blue-800 transition"
          :disabled="form.processing"
        >
          <span v-if="form.processing">Saving...</span>
          <span v-else>Save</span>
        </button>

        <Link
          href="/admin/co-curricular"
          class="text-gray-600 hover:underline"
        >
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