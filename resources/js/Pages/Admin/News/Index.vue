<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, usePage, router, useForm } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'

/*
|--------------------------------------------------------------------------
| LAYOUT FIX (prevents white screen)
|--------------------------------------------------------------------------
*/
defineOptions({ layout: AdminLayout })

const page = usePage()
const serverNews = ref(page.props.news || [])
const viewStats = ref({})

/*
|--------------------------------------------------------------------------
| LOAD VIEW STATS
|--------------------------------------------------------------------------
*/
async function loadViewStats() {
  for (const item of serverNews.value) {
    try {
      const response = await fetch(`/api/page-view/stats/news/${item.id}`)
      const data = await response.json()
      viewStats.value[item.id] = data
    } catch (error) {
      console.error('Failed to load view stats:', error)
    }
  }
}

onMounted(() => {
  loadViewStats()
})

/*
|--------------------------------------------------------------------------
| FORM
|--------------------------------------------------------------------------
*/
const form = useForm({
  title: '',
  slug: '',
  excerpt: '',
  content: '',
  image: null,
  is_published: true
})

/*
|--------------------------------------------------------------------------
| IMAGE PREVIEW
|--------------------------------------------------------------------------
*/
const preview = ref(null)

/*
|--------------------------------------------------------------------------
| 🔥 IMAGE COMPRESSION FUNCTION
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

    // 🔥 prevent crash
    img.onerror = () => resolve(file)

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
    form.image = null
    preview.value = null
    return
  }

  // 🔥 compress image
  const compressed = await compressImage(file)

  form.image = compressed
  preview.value = URL.createObjectURL(compressed)
}

/*
|--------------------------------------------------------------------------
| AUTO EXCERPT
|--------------------------------------------------------------------------
*/
function generateExcerpt() {
  if (!form.content) return ''
  return form.content.substring(0, 150) + '...'
}

/*
|--------------------------------------------------------------------------
| SUBMIT
|--------------------------------------------------------------------------
*/
function submit() {
  if (!form.excerpt) {
    form.excerpt = generateExcerpt()
  }

  form.post(route('admin.news.store'), {
    forceFormData: true,
    onSuccess: () => {
      form.reset()
      preview.value = null
      window.location.reload()
    }
  })
}

/*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
*/
function deleteNews(id) {
  if (!confirm('Delete this news item?')) return

  router.delete(route('admin.news.destroy', id), {
    preserveScroll: true,
    onSuccess: () => {
      serverNews.value = serverNews.value.filter(n => n.id !== id)
    }
  })
}

/*
|--------------------------------------------------------------------------
| GRID
|--------------------------------------------------------------------------
*/
const gridCols = computed(() =>
  'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6'
)

/*
|--------------------------------------------------------------------------
| DATE FORMAT
|--------------------------------------------------------------------------
*/
function formatDate(iso) {
  try {
    return new Date(iso).toLocaleString()
  } catch {
    return iso
  }
}
</script>

<template>
  <Head title="News Management" />

  <div class="p-8 space-y-8">

    <!-- ================= HEADER ================= -->
    <div class="flex items-center justify-between">
      <h1 class="text-3xl font-bold text-gray-800">News Management</h1>
      <a
        :href="route('admin.page-views.index')"
        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition"
      >
        View Page Statistics
      </a>
    </div>

    <!-- ================= CREATE ================= -->
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-bold mb-4">Create News</h2>

      <form @submit.prevent="submit" class="space-y-4">

        <!-- TITLE -->
        <div>
          <label class="block text-sm font-medium mb-1">Title</label>
          <input v-model="form.title" type="text"
            class="w-full border p-2 rounded"
            required />
          <p v-if="form.errors.title" class="text-red-600 text-sm">
            {{ form.errors.title }}
          </p>
        </div>

        <!-- CONTENT -->
        <div>
          <label class="block text-sm font-medium mb-1">Content</label>
          <textarea v-model="form.content" rows="6"
            class="w-full border p-2 rounded"
            required></textarea>
        </div>

        <!-- IMAGE -->
        <div>
          <label class="block text-sm font-medium mb-1">Image</label>
          <input type="file" accept="image/*" @change="handleFile" />

          <img v-if="preview"
            :src="preview"
            class="mt-3 h-40 w-full object-cover rounded" />
        </div>

        <!-- PUBLISH -->
        <label class="flex items-center gap-2">
          <input type="checkbox" v-model="form.is_published" />
          Publish now
        </label>

        <!-- BUTTON -->
        <button
          type="submit"
          :disabled="form.processing"
          class="bg-blue-900 text-white px-4 py-2 rounded"
        >
          {{ form.processing ? 'Uploading...' : 'Create News' }}
        </button>

      </form>
    </div>

    <!-- ================= LIST ================= -->
    <div>
      <h2 class="text-xl font-bold mb-4">All News</h2>

      <div :class="gridCols">

        <div
          v-for="item in serverNews"
          :key="item.id"
          class="bg-white rounded-lg shadow overflow-hidden flex flex-col"
        >

          <!-- IMAGE -->
          <div class="h-48 bg-gray-100">
            <img
              v-if="item.image_url"
              :src="item.image_url"
              class="w-full h-full object-cover"
            />
            <div v-else class="flex items-center justify-center h-full text-gray-400">
              No image
            </div>
          </div>

          <!-- CONTENT -->
          <div class="p-4 flex flex-col flex-1">

            <h3 class="font-semibold text-lg mb-2 truncate">
              {{ item.title }}
            </h3>

            <p class="text-sm text-gray-600 mb-4 line-clamp-3">
              {{ item.excerpt || 'No excerpt available' }}
            </p>

            <!-- VIEW STATS -->
            <div v-if="viewStats[item.id]" class="mb-4">
              <div class="flex items-center gap-2 text-sm">
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                  👁 {{ viewStats[item.id].total_views }} total views
                </span>
              </div>
              <div v-if="viewStats[item.id].daily_views.length > 0" class="mt-2 text-xs text-gray-500">
                Today: {{ viewStats[item.id].daily_views[0].views }} views
              </div>
            </div>

            <div class="mt-auto flex justify-between items-center">
              <span class="text-xs text-gray-500">
                {{ formatDate(item.created_at) }}
              </span>

              <button
                @click="deleteNews(item.id)"
                class="bg-red-600 text-white px-2 py-1 rounded text-sm"
              >
                Delete
              </button>
            </div>

          </div>

        </div>

      </div>

      <div v-if="serverNews.length === 0"
        class="text-center py-8 text-gray-500">
        No news yet.
      </div>

    </div>

  </div>
</template>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>