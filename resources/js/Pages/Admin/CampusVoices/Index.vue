<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, usePage, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

defineOptions({ layout: AdminLayout })

const page = usePage()
const campusVoices = ref(page.props.campusVoices || [])
const categories = ref(page.props.categories || [])
const filters = ref({
  search: page.props.filters?.search || '',
  category: page.props.filters?.category || '',
  status: page.props.filters?.status || '',
})

/*
|--------------------------------------------------------------------------
| FILTER
|--------------------------------------------------------------------------
*/
function applyFilters() {
  router.get(route('admin.campus-voices.index'), filters.value, {
    preserveState: true,
    preserveScroll: true,
  })
}

function resetFilters() {
  filters.value = {
    search: '',
    category: '',
    status: '',
  }
  applyFilters()
}

/*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
*/
function deleteCampusVoice(id) {
  if (!confirm('Delete this Campus Voice article?')) return

  router.delete(route('admin.campus-voices.destroy', id), {
    preserveScroll: true,
    onSuccess: () => {
      campusVoices.value = campusVoices.value.filter(cv => cv.id !== id)
    }
  })
}

/*
|--------------------------------------------------------------------------
| TOGGLE FEATURED
|--------------------------------------------------------------------------
*/
function toggleFeatured(id) {
  router.post(route('admin.campus-voices.toggle-featured', id), {}, {
    preserveScroll: true,
    onSuccess: () => {
      const item = campusVoices.value.find(cv => cv.id === id)
      if (item) {
        item.featured = !item.featured
      }
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

/*
|--------------------------------------------------------------------------
| STATUS BADGE
|--------------------------------------------------------------------------
*/
function statusBadge(status) {
  const badges = {
    published: 'bg-green-100 text-green-800',
    draft: 'bg-yellow-100 text-yellow-800',
  }
  return badges[status] || 'bg-gray-100 text-gray-800'
}
</script>

<template>
  <Head title="Campus Voices Management" />

  <div class="p-8 space-y-8">

    <!-- ================= HEADER ================= -->
    <div class="flex items-center justify-between">
      <h1 class="text-3xl font-bold text-gray-800">Campus Voices Management</h1>
      <a
        :href="route('admin.campus-voices.create')"
        class="bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded-lg font-medium transition"
      >
        Create New Article
      </a>
    </div>

    <!-- ================= FILTERS ================= -->
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-lg font-bold mb-4">Filters</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Search -->
        <div>
          <label class="block text-sm font-medium mb-1">Search by Student Name</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Search..."
            class="w-full border p-2 rounded"
            @keyup.enter="applyFilters"
          />
        </div>

        <!-- Category -->
        <div>
          <label class="block text-sm font-medium mb-1">Category</label>
          <select
            v-model="filters.category"
            class="w-full border p-2 rounded"
            @change="applyFilters"
          >
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat" :value="cat">
              {{ cat }}
            </option>
          </select>
        </div>

        <!-- Status -->
        <div>
          <label class="block text-sm font-medium mb-1">Status</label>
          <select
            v-model="filters.status"
            class="w-full border p-2 rounded"
            @change="applyFilters"
          >
            <option value="">All Status</option>
            <option value="published">Published</option>
            <option value="draft">Draft</option>
          </select>
        </div>
      </div>

      <div class="mt-4 flex gap-2">
        <button
          @click="applyFilters"
          class="bg-blue-600 text-white px-4 py-2 rounded"
        >
          Apply Filters
        </button>
        <button
          @click="resetFilters"
          class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
        >
          Reset
        </button>
      </div>
    </div>

    <!-- ================= LIST ================= -->
    <div>
      <h2 class="text-xl font-bold mb-4">All Articles</h2>

      <div :class="gridCols">
        <div
          v-for="item in campusVoices"
          :key="item.id"
          class="bg-white rounded-lg shadow overflow-hidden flex flex-col"
        >
          <!-- IMAGE -->
          <div class="h-48 bg-gray-100 relative">
            <img
              v-if="item.image_url"
              :src="item.image_url"
              class="w-full h-full object-cover"
            />
            <div v-else class="flex items-center justify-center h-full text-gray-400">
              No image
            </div>
            
            <!-- Featured Badge -->
            <div v-if="item.featured" class="absolute top-2 right-2 bg-yellow-400 text-yellow-900 px-2 py-1 rounded-full text-xs font-bold">
              ⭐ Featured
            </div>
          </div>

          <!-- CONTENT -->
          <div class="p-4 flex flex-col flex-1">
            <div class="mb-2">
              <span :class="['px-2 py-1 rounded-full text-xs font-medium', statusBadge(item.status)]">
                {{ item.status }}
              </span>
              <span v-if="item.category" class="ml-2 text-xs text-gray-500">
                {{ item.category }}
              </span>
            </div>

            <h3 class="font-semibold text-lg mb-1 truncate">
              {{ item.title }}
            </h3>

            <p class="text-sm text-gray-600 mb-2">
              <span class="font-medium">Student:</span> {{ item.student_name }}
            </p>

            <p class="text-sm text-gray-600 mb-4 line-clamp-3">
              {{ item.summary || 'No summary available' }}
            </p>

            <!-- VIEW STATS -->
            <div class="mb-4">
              <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-sm">
                👁 {{ item.views }} views
              </span>
            </div>

            <div class="mt-auto flex justify-between items-center">
              <span class="text-xs text-gray-500">
                {{ formatDate(item.created_at) }}
              </span>

              <div class="flex gap-2">
                <button
                  @click="toggleFeatured(item.id)"
                  :class="[
                    'px-2 py-1 rounded text-sm',
                    item.featured ? 'bg-yellow-500 text-white' : 'bg-gray-200 text-gray-700'
                  ]"
                >
                  {{ item.featured ? 'Unfeature' : 'Feature' }}
                </button>
                
                <a
                  :href="route('admin.campus-voices.edit', item.id)"
                  class="bg-blue-600 text-white px-2 py-1 rounded text-sm"
                >
                  Edit
                </a>
                
                <button
                  @click="deleteCampusVoice(item.id)"
                  class="bg-red-600 text-white px-2 py-1 rounded text-sm"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="campusVoices.length === 0"
        class="text-center py-8 text-gray-500">
        No Campus Voices articles yet.
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
