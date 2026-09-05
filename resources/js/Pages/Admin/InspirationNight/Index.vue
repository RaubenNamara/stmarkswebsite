<template>

<div>

  <!-- Page Header -->
  <div class="flex justify-between items-center mb-6">

    <h1 class="text-2xl font-bold text-gray-800">
      Inspiration Nights
    </h1>

    <Link
      :href="route('admin.inspiration.create')"
      class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow"
    >
      + Add Inspiration Night
    </Link>

  </div>


  <!-- Table -->
  <div class="bg-white rounded-xl shadow overflow-hidden">

    <table class="w-full text-sm text-left">

      <thead class="bg-gray-100 text-gray-600">

        <tr>
          <th class="p-3">Image</th>
          <th class="p-3">Title</th>
          <th class="p-3">Speaker</th>
          <th class="p-3">Date</th>
          <th class="p-3 text-right">Actions</th>
        </tr>

      </thead>


      <tbody>

        <tr
          v-for="talk in talks.data"
          :key="talk.id"
          class="border-t hover:bg-gray-50"
        >

          <!-- Image -->
          <td class="p-3">

            <img
              v-if="talk.image_url"
              :src="talk.image_url"
              class="w-20 h-12 object-cover rounded"
            />

            <span v-else class="text-gray-400 text-xs">
              No Image
            </span>

          </td>


          <!-- Title -->
          <td class="p-3 font-medium text-gray-800">
            {{ talk.title }}
          </td>


          <!-- Speaker -->
          <td class="p-3 text-gray-600">
            {{ talk.speaker || '—' }}
          </td>


          <!-- Date -->
          <td class="p-3 text-gray-600">
            {{ talk.date || '—' }}
          </td>


          <!-- Actions -->
          <td class="p-3 text-right space-x-3">

            <Link
              :href="route('admin.inspiration.edit', talk.id)"
              class="text-blue-600 hover:underline"
            >
              Edit
            </Link>

            <button
              @click="remove(talk.id)"
              class="text-red-600 hover:underline"
            >
              Delete
            </button>

          </td>

        </tr>

      </tbody>

    </table>

  </div>


  <!-- Pagination -->
  <div class="flex justify-between items-center mt-6">

    <button
      :disabled="!talks.prev_page_url"
      @click="changePage(talks.current_page - 1)"
      class="px-4 py-2 bg-gray-200 rounded disabled:opacity-40"
    >
      Previous
    </button>


    <span class="text-sm text-gray-600">
      Page {{ talks.current_page }} of {{ talks.last_page }}
    </span>


    <button
      :disabled="!talks.next_page_url"
      @click="changePage(talks.current_page + 1)"
      class="px-4 py-2 bg-gray-200 rounded disabled:opacity-40"
    >
      Next
    </button>

  </div>


</div>

</template>



<script setup>

import { router, Link, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({
  layout: AdminLayout
})


const props = defineProps({
  talks: Object
})


/*
|--------------------------------------------------------------------------
| Delete Inspiration Night
|--------------------------------------------------------------------------
*/
function remove(id) {

  if (!confirm('Delete this Inspiration Night?')) return

  router.delete(route('admin.inspiration.destroy', id))

}


/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/
function changePage(page) {

  router.get(route('admin.inspiration.index', { page }))

}

</script>