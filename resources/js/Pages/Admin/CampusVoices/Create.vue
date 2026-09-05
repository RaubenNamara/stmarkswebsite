<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import Editor from '@tinymce/tinymce-vue'

defineOptions({ layout: AdminLayout })

const form = useForm({
  student_name: '',
  title: '',
  author_bio: '',
  content: '',
  featured_image: null,
  category: '',
  featured: false,
  status: 'draft',
})

const preview = ref(null)

const tinyMceConfig = {
  height: 400,
  menubar: true,
  plugins: [
    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
    'insertdatetime', 'media', 'table', 'help', 'wordcount'
  ],
  toolbar: 'undo redo | blocks | ' +
    'bold italic forecolor | alignleft aligncenter ' +
    'alignright alignjustify | bullist numlist outdent indent | ' +
    'removeformat | help',
  content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
}

/*
|--------------------------------------------------------------------------
| IMAGE COMPRESSION FUNCTION
|--------------------------------------------------------------------------
*/
async function compressImage(file) {
  return new Promise((resolve) => {
    const img = new Image()
    const reader = new FileReader()

    reader.onload = (e) => {
      img.src = e.target.result
    }

    img.onload = () => {
      try {
        const canvas = document.createElement('canvas')

        const MAX_WIDTH = 1200
        const scale = img.width > MAX_WIDTH ? MAX_WIDTH / img.width : 1

        canvas.width = img.width * scale
        canvas.height = img.height * scale

        const ctx = canvas.getContext('2d')
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height)

        canvas.toBlob((blob) => {
          if (blob) {
            resolve(new File([blob], file.name, {
              type: 'image/jpeg',
              lastModified: Date.now()
            }))
          } else {
            resolve(file)
          }
        }, 'image/jpeg', 0.7)
      } catch (error) {
        console.error('Image compression error:', error)
        resolve(file)
      }
    }

    img.onerror = () => {
      console.error('Image load error')
      resolve(file)
    }

    reader.onerror = () => {
      console.error('File reader error')
      resolve(file)
    }

    reader.readAsDataURL(file)
  })
}

/*
|--------------------------------------------------------------------------
| HANDLE FILE (COMPRESSED)
|--------------------------------------------------------------------------
*/
async function handleFile(e) {
  const file = e.target.files?.[0]

  if (!file) {
    form.featured_image = null
    preview.value = null
    return
  }

  const compressed = await compressImage(file)

  form.featured_image = compressed
  preview.value = URL.createObjectURL(compressed)
}


/*
|--------------------------------------------------------------------------
| SUBMIT
|--------------------------------------------------------------------------
*/
function submit() {
  form.post(route('admin.campus-voices.store'), {
    onSuccess: () => {
      form.reset()
      preview.value = null
    }
  })
}
</script>

<template>
  <Head title="Create Campus Voice" />

  <div class="p-8 space-y-8">

    <!-- ================= HEADER ================= -->
    <div class="flex items-center justify-between">
      <h1 class="text-3xl font-bold text-gray-800">Create Campus Voice Article</h1>
      <a
        :href="route('admin.campus-voices.index')"
        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition"
      >
        Back to List
      </a>
    </div>

    <!-- ================= CREATE FORM ================= -->
    <div class="bg-white rounded-lg shadow p-6">
      <form @submit.prevent="submit" class="space-y-6">

        <!-- STUDENT NAME -->
        <div>
          <label class="block text-sm font-medium mb-1">Student Name *</label>
          <input
            v-model="form.student_name"
            type="text"
            class="w-full border p-2 rounded"
            required
          />
          <p v-if="form.errors.student_name" class="text-red-600 text-sm mt-1">
            {{ form.errors.student_name }}
          </p>
        </div>

        <!-- TITLE -->
        <div>
          <label class="block text-sm font-medium mb-1">Title *</label>
          <input
            v-model="form.title"
            type="text"
            class="w-full border p-2 rounded"
            required
          />
          <p v-if="form.errors.title" class="text-red-600 text-sm mt-1">
            {{ form.errors.title }}
          </p>
        </div>

        <!-- AUTHOR BIO -->
        <div>
          <label class="block text-sm font-medium mb-1">About the Author</label>
          <textarea
            v-model="form.author_bio"
            class="w-full border p-2 rounded"
            rows="3"
            placeholder="Brief bio about the student author..."
          />
          <p v-if="form.errors.author_bio" class="text-red-600 text-sm mt-1">
            {{ form.errors.author_bio }}
          </p>
        </div>


        <!-- CATEGORY -->
        <div>
          <label class="block text-sm font-medium mb-1">Category</label>
          <input
            v-model="form.category"
            type="text"
            class="w-full border p-2 rounded"
            placeholder="e.g., Achievement, Interview, Opinion"
          />
          <p v-if="form.errors.category" class="text-red-600 text-sm mt-1">
            {{ form.errors.category }}
          </p>
        </div>



        <!-- CONTENT -->
        <div>
          <label class="block text-sm font-medium mb-1">Content *</label>
          <Editor
            v-model="form.content"
            :init="tinyMceConfig"
            api-key="ve0oi3p5g5lensb034xuxzva1td9saq5mw68wpf2u0666ae0"
            :disabled="form.processing"
          />
          <p v-if="form.errors.content" class="text-red-600 text-sm mt-1">
            {{ form.errors.content }}
          </p>
        </div>

        <!-- FEATURED IMAGE -->
        <div>
          <label class="block text-sm font-medium mb-1">Featured Image</label>
          <input
            type="file"
            accept="image/*"
            @change="handleFile"
            class="w-full border p-2 rounded"
          />
          <p v-if="form.errors.featured_image" class="text-red-600 text-sm mt-1">
            {{ form.errors.featured_image }}
          </p>

          <img
            v-if="preview"
            :src="preview"
            class="mt-3 h-48 w-full object-cover rounded"
          />
        </div>

        <!-- FEATURED -->
        <div class="flex items-center gap-2">
          <input
            type="checkbox"
            v-model="form.featured"
            id="featured"
            class="w-4 h-4"
          />
          <label for="featured" class="text-sm font-medium">
            Mark as Featured Article
          </label>
        </div>

        <!-- STATUS -->
        <div>
          <label class="block text-sm font-medium mb-1">Status *</label>
          <select
            v-model="form.status"
            class="w-full border p-2 rounded"
            required
          >
            <option value="draft">Draft</option>
            <option value="published">Published</option>
          </select>
          <p v-if="form.errors.status" class="text-red-600 text-sm mt-1">
            {{ form.errors.status }}
          </p>
        </div>

        <!-- BUTTONS -->
        <div class="flex gap-4">
          <button
            type="submit"
            :disabled="form.processing"
            class="bg-blue-900 text-white px-6 py-2 rounded disabled:opacity-50"
          >
            {{ form.processing ? 'Creating...' : 'Create Article' }}
          </button>

          <a
            :href="route('admin.campus-voices.index')"
            class="bg-gray-200 text-gray-700 px-6 py-2 rounded"
          >
            Cancel
          </a>
        </div>

      </form>
    </div>

  </div>
</template>
