<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({
  layout: AdminLayout
})

const props = defineProps({
  clubs: {
    type: Array,
    default: () => []
  }
})

const query = ref('')

/* ✅ COMPUTED FILTER */
const filtered = computed(() => {
  const q = (query.value || '').toLowerCase().trim()

  if (!q) return props.clubs

  return props.clubs.filter(c =>
    (c.title || '').toLowerCase().includes(q) ||
    (c.slug || '').toLowerCase().includes(q)
  )
})

/* ✅ DELETE */
function confirmDelete(slug) {

  if (!confirm('Delete this club? This will remove all images.')) return

  router.delete(route('admin.clubs.destroy', slug), {
    preserveScroll: true
  })
}
</script>


<template>

<div class="p-6 max-w-7xl mx-auto">

<!-- HEADER -->
<div class="flex items-center justify-between mb-6">

  <div>
    <h1 class="text-2xl font-bold">Clubs</h1>
    <p class="text-sm text-gray-500">Manage your school clubs</p>
  </div>

  <div class="flex items-center gap-3">

    <input
      v-model="query"
      placeholder="Search clubs..."
      class="px-3 py-2 border rounded-md text-sm"
    />

    <Link
      :href="route('admin.clubs.create')"
      class="bg-blue-600 text-white px-3 py-2 rounded-md"
    >
      + New Club
    </Link>

  </div>

</div>


<!-- CLUB LIST -->
<div v-if="filtered.length" class="space-y-6">

  <div
    v-for="club in filtered"
    :key="club.id"
    class="bg-white rounded-lg shadow overflow-hidden flex flex-col"
  >

    <!-- IMAGES -->
    <div
      v-if="club.images && club.images.length"
      class="grid grid-cols-3 gap-2 p-3"
    >

      <img
        v-for="img in club.images"
        :key="img.id"
        :src="img.url || `/storage/${img.image_path}`"
        class="w-full h-28 object-cover"
      />

    </div>

    <div
      v-else
      class="h-28 bg-gray-100 flex items-center justify-center text-gray-400"
    >
      No image
    </div>


    <!-- CONTENT -->
    <div class="p-4 flex-1 flex flex-col">

      <div class="mb-2">

        <h2 class="text-lg font-semibold text-gray-800 truncate">
          {{ club.title }}
        </h2>

        <p class="text-xs text-gray-500 mt-1">
          Slug: {{ club.slug }}
        </p>

        <p class="text-xs text-gray-500 mt-1">
          {{ club.images?.length || 0 }} images
        </p>

      </div>


      <!-- ACTIONS -->
      <div class="mt-auto flex gap-2">

        <Link
          :href="route('clubs.show', club.slug)"
          class="flex-1 text-center px-3 py-2 border rounded text-sm"
        >
          View
        </Link>

        <Link
          :href="route('admin.clubs.edit', club.slug)"
          class="flex-1 text-center px-3 py-2 border rounded text-sm"
        >
          Edit
        </Link>

        <button
          @click="confirmDelete(club.slug)"
          class="px-3 py-2 bg-red-600 text-white rounded text-sm"
        >
          Delete
        </button>

      </div>

    </div>

  </div>

</div>


<!-- EMPTY -->
<div
  v-else
  class="text-center py-14 text-gray-600 border rounded bg-gray-50"
>
  No clubs found. Create one to get started.
</div>

</div>

</template>


<style scoped>
input::placeholder {
  color: #9CA3AF;
}
</style>