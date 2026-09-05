<script setup>
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  items: {
    type: Array,
    default: () => []
  }
})

function deleteItem(id) {
  if (!confirm('Are you sure you want to delete this item?')) return

  router.delete(`/admin/co-curricular/${id}`, {
    preserveScroll: true
  })
}

function goToCreate() {
  router.get('/admin/co-curricular/create')
}

function goToEdit(id) {
  router.get(`/admin/co-curricular/${id}/edit`)
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString()
}
</script>

<template>
  <div class="p-8">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">
          Manage Co-Curricular
        </h1>
        <p class="text-gray-500 text-sm">
          Create and manage co-curricular activities.
        </p>
      </div>

      <!-- ADD BUTTON -->
      <button
        @click="goToCreate"
        class="bg-blue-900 text-white px-5 py-2 rounded-lg shadow hover:bg-blue-800 flex items-center gap-2"
      >
        <span>➕</span>
        <span>Add New</span>
      </button>
    </div>

    <!-- TABLE -->
    <div class="bg-white rounded-xl shadow overflow-hidden">

      <table class="w-full text-left">

        <!-- HEAD -->
        <thead class="bg-gray-100 text-gray-700 text-sm">
          <tr>
            <th class="p-4">Title</th>
            <th class="p-4">Created</th>
            <th class="p-4 text-right">Actions</th>
          </tr>
        </thead>

        <!-- BODY -->
        <tbody>

          <tr
            v-for="item in props.items"
            :key="item.id"
            class="border-t hover:bg-gray-50 transition"
          >
            <!-- TITLE -->
            <td class="p-4 font-medium text-gray-800">
              {{ item.title }}
            </td>

            <!-- DATE -->
            <td class="p-4 text-gray-600 text-sm">
              {{ formatDate(item.created_at) }}
            </td>

            <!-- ACTIONS -->
            <td class="p-4 text-right">
              <div class="flex justify-end gap-2">

                <!-- EDIT -->
                <button
                  @click="goToEdit(item.id)"
                  class="px-3 py-1 rounded-md border border-blue-800 text-blue-800 text-sm hover:bg-blue-50"
                >
                  Edit
                </button>

                <!-- DELETE -->
                <button
                  @click="deleteItem(item.id)"
                  class="px-3 py-1 rounded-md bg-red-600 text-white text-sm hover:bg-red-700"
                >
                  Delete
                </button>

              </div>
            </td>
          </tr>

          <!-- EMPTY STATE -->
          <tr v-if="props.items.length === 0">
            <td colspan="3" class="p-6 text-center text-gray-500">
              No co-curricular content found.
            </td>
          </tr>

        </tbody>

      </table>

    </div>

  </div>
</template>

<style scoped>
button[disabled] {
  cursor: not-allowed;
  opacity: 0.6;
}
</style>