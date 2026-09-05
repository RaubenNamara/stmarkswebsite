<template>
  <div class="p-6 max-w-3xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold">Create Club</h1>
        <p class="text-sm text-gray-500">Add a new school club with images and captions</p>
      </div>

      <Link href="/admin/clubs" class="px-3 py-2 border rounded">Back</Link>
    </div>

    <form @submit.prevent="submit" class="space-y-4 bg-white p-6 rounded-lg shadow">
      
      <!-- Title -->
      <div>
        <label class="block text-sm font-medium mb-1">Title</label>
        <input v-model="form.title" type="text" required class="w-full p-2 border rounded-md" />
      </div>

      <!-- Content -->
      <div>
        <label class="block text-sm font-medium mb-1">Content</label>
        <textarea v-model="form.content" rows="6" class="w-full p-2 border rounded-md"></textarea>
      </div>

      <!-- Images -->
      <div>
        <label class="block text-sm font-medium mb-1">Images (multiple)</label>
        <input type="file" multiple accept="image/*" @change="onFilesChange" class="w-full" />
      </div>

      <!-- Preview -->
      <div v-if="previews.length" class="grid grid-cols-3 gap-4">
        <div v-for="(p, i) in previews" :key="i" class="border rounded overflow-hidden relative">
          
          <img :src="p.dataUrl" class="w-full h-32 object-cover" />

          <!-- Remove Button -->
          <button 
            type="button"
            @click="removeImage(i)"
            class="absolute top-1 right-1 bg-red-600 text-white text-xs px-2 py-1 rounded">
            X
          </button>

          <input 
            v-model="captions[i]" 
            placeholder="Caption (optional)" 
            class="w-full p-2 border-t" 
          />
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-3 pt-4">
        <button 
          type="submit" 
          :disabled="form.processing"
          class="bg-blue-600 text-white px-4 py-2 rounded-md disabled:opacity-50">
          Save
        </button>

        <Link href="/admin/clubs" class="px-4 py-2 border rounded-md">Cancel</Link>
      </div>

    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'

const form = useForm({
  title: '',
  content: '',
  images: [],
  captions: []
})

const previews = ref([])
const captions = ref([])

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
      const canvas = document.createElement('canvas')

      const MAX_WIDTH = 1200
      const scaleSize = MAX_WIDTH / img.width

      canvas.width = MAX_WIDTH
      canvas.height = img.height * scaleSize

      const ctx = canvas.getContext('2d')
      ctx.drawImage(img, 0, 0, canvas.width, canvas.height)

      canvas.toBlob((blob) => {
        resolve(new File([blob], file.name, {
          type: 'image/jpeg',
          lastModified: Date.now()
        }))
      }, 'image/jpeg', 0.7)
    }

    reader.readAsDataURL(file)
  })
}

/*
|--------------------------------------------------------------------------
| HANDLE FILE CHANGE
|--------------------------------------------------------------------------
*/
async function onFilesChange(e) {
  const files = Array.from(e.target.files || [])

  previews.value = []
  captions.value = []
  form.images = []

  for (const file of files) {

    // 🔥 Compress image
    const compressed = await compressImage(file)

    form.images.push(compressed)

    const reader = new FileReader()
    reader.onload = (ev) => {
      previews.value.push({ name: file.name, dataUrl: ev.target.result })
      captions.value.push('')
    }
    reader.readAsDataURL(compressed)
  }
}

/*
|--------------------------------------------------------------------------
| REMOVE IMAGE
|--------------------------------------------------------------------------
*/
function removeImage(index) {
  previews.value.splice(index, 1)
  captions.value.splice(index, 1)
  form.images.splice(index, 1)
}

/*
|--------------------------------------------------------------------------
| SUBMIT
|--------------------------------------------------------------------------
*/
function submit() {
  form.captions = captions.value
  form.post('/admin/clubs', { forceFormData: true })
}
</script>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue'
export default { layout: AdminLayout }
</script>

<style scoped>
</style>