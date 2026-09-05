<template>
  <div class="p-6 max-w-3xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold">Edit Club</h1>
        <p class="text-sm text-gray-500">
          Update club details and manage images
        </p>
      </div>

      <Link :href="route('admin.clubs.index')" class="px-3 py-2 border rounded">
        Back
      </Link>
    </div>

    <form @submit.prevent="submit" class="space-y-6 bg-white p-6 rounded-lg shadow">

      <!-- TITLE -->
      <div>
        <label class="block text-sm font-medium mb-1">Title</label>
        <input
          v-model="form.title"
          type="text"
          required
          class="w-full p-2 border rounded-md"
        />
      </div>

      <!-- CONTENT -->
      <div>
        <label class="block text-sm font-medium mb-1">Content</label>
        <textarea
          v-model="form.content"
          rows="6"
          class="w-full p-2 border rounded-md"
        ></textarea>
      </div>

      <!-- ADD NEW IMAGES -->
      <div>
        <label class="block text-sm font-medium mb-2">Add New Images</label>
        <input
          type="file"
          multiple
          accept="image/*"
          @change="onFilesChange"
        />
      </div>

      <!-- NEW IMAGE PREVIEW -->
      <div v-if="previews.length" class="grid grid-cols-3 gap-4">
        <div
          v-for="(preview, index) in previews"
          :key="index"
          class="border rounded overflow-hidden relative"
        >
          <img :src="preview" class="w-full h-32 object-cover" />

          <!-- Remove -->
          <button
            type="button"
            @click="removeImage(index)"
            class="absolute top-1 right-1 bg-red-600 text-white px-2 py-1 text-xs rounded"
          >
            X
          </button>

          <input
            v-model="captions[index]"
            placeholder="Caption (optional)"
            class="w-full p-2 border-t"
          />
        </div>
      </div>

      <!-- EXISTING IMAGES -->
      <div v-if="existingImages.length">
        <h3 class="font-semibold mb-3">Existing Images</h3>

        <div class="grid grid-cols-3 gap-4">
          <div
            v-for="image in existingImages"
            :key="image.id"
            class="relative border rounded overflow-hidden"
          >
            <img
              :src="`/storage/${image.image_path}`"
              class="w-full h-32 object-cover"
            />

            <div class="p-2 bg-white">
              <input
                v-model="image.caption"
                class="w-full p-1 border"
              />
            </div>

            <button
              type="button"
              @click="deleteExistingImage(image.id)"
              class="absolute top-2 right-2 bg-red-600 text-white px-2 py-1 text-xs rounded"
            >
              Delete
            </button>
          </div>
        </div>
      </div>

      <!-- ACTIONS -->
      <div class="flex items-center gap-3 pt-4">
        <button
          type="submit"
          :disabled="form.processing"
          class="bg-blue-600 text-white px-4 py-2 rounded-md"
        >
          Save Changes
        </button>

        <Link :href="route('admin.clubs.index')" class="px-4 py-2 border rounded-md">
          Cancel
        </Link>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, Link, router } from '@inertiajs/vue3'

/*
|--------------------------------------------------------------------------
| SAFE PROPS (prevents white screen)
|--------------------------------------------------------------------------
*/
const props = defineProps({
  club: {
    type: Object,
    default: () => ({})
  }
})

/*
|--------------------------------------------------------------------------
| FORM
|--------------------------------------------------------------------------
*/
const form = useForm({
  title: props.club.title ?? '',
  content: props.club.content ?? '',
  images: [],
  captions: [],
  existing_images: []
})

/*
|--------------------------------------------------------------------------
| STATE
|--------------------------------------------------------------------------
*/
const previews = ref([])
const captions = ref([])

const existingImages = ref(
  (props.club.images || []).map(img => ({
    id: img.id,
    image_path: img.image_path,
    caption: img.caption || ''
  }))
)

/*
|--------------------------------------------------------------------------
| IMAGE COMPRESSION (SAFE)
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
      const canvas = document.createElement('canvas')

      const MAX_WIDTH = 1200
      const scale = MAX_WIDTH / img.width

      canvas.width = MAX_WIDTH
      canvas.height = img.height * scale

      const ctx = canvas.getContext('2d')
      ctx.drawImage(img, 0, 0, canvas.width, canvas.height)

      canvas.toBlob((blob) => {
        resolve(new File([blob], file.name, {
          type: 'image/jpeg',
          lastModified: Date.now()
        }))
      }, 'image/jpeg', 0.7)
    }

    // 🔥 prevents crash
    img.onerror = () => {
      resolve(file)
    }

    reader.readAsDataURL(file)
  })
}

/*
|--------------------------------------------------------------------------
| HANDLE FILE CHANGE
|--------------------------------------------------------------------------
*/
async function onFilesChange(event) {
  const files = Array.from(event.target.files || [])

  previews.value = []
  captions.value = []
  form.images = []

  for (const file of files) {
    const compressed = await compressImage(file)

    form.images.push(compressed)

    const reader = new FileReader()
    reader.onload = (e) => {
      previews.value.push(e.target.result)
      captions.value.push('')
    }
    reader.readAsDataURL(compressed)
  }
}

/*
|--------------------------------------------------------------------------
| REMOVE NEW IMAGE
|--------------------------------------------------------------------------
*/
function removeImage(index) {
  previews.value.splice(index, 1)
  captions.value.splice(index, 1)
  form.images.splice(index, 1)
}

/*
|--------------------------------------------------------------------------
| DELETE EXISTING IMAGE
|--------------------------------------------------------------------------
*/
function deleteExistingImage(id) {
  if (!confirm('Delete this image?')) return

  router.delete(route('admin.clubs.images.destroy', id), {
    preserveScroll: true,
    onSuccess: () => {
      existingImages.value =
        existingImages.value.filter(i => i.id !== id)
    }
  })
}

/*
|--------------------------------------------------------------------------
| SUBMIT
|--------------------------------------------------------------------------
*/
function submit() {
  form.captions = captions.value

  form.existing_images = existingImages.value.map(img => ({
    id: img.id,
    caption: img.caption
  }))

  form.transform((data) => ({
    ...data,
    _method: 'PUT'
  })).post(route('admin.clubs.update', props.club.slug), {
    forceFormData: true,
    preserveScroll: true
  })
}
</script>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue'
export default {
  layout: AdminLayout
}
</script>

<style scoped>
</style>