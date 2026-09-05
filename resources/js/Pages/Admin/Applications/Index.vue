<template>
  <div class="min-h-screen bg-gray-100">
    <Head title="Job Applications" />

    <div class="max-w-7xl mx-auto py-12 px-6">

      <!-- Page Title -->
      <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-blue-900">
          Job Applications
        </h1>

        <span class="text-sm bg-blue-100 text-blue-900 px-4 py-2 rounded-lg font-semibold">
          {{ applications.length }} Total
        </span>
      </div>

      <!-- Table Card -->
      <div class="bg-white shadow-xl rounded-2xl overflow-hidden">

        <!-- Empty State -->
        <div v-if="applications.length === 0"
             class="p-12 text-center text-gray-500 text-lg">
          No applications submitted yet.
        </div>

        <!-- Table -->
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">

            <!-- Header -->
            <thead class="bg-blue-900 text-white">
              <tr>
                <th class="px-6 py-4 text-left text-sm font-semibold">Name</th>
                <th class="px-6 py-4 text-left text-sm font-semibold">Contact</th>
                <th class="px-6 py-4 text-left text-sm font-semibold">Email</th>
                <th class="px-6 py-4 text-left text-sm font-semibold">Position</th>
                <th class="px-6 py-4 text-left text-sm font-semibold">Date</th>
                <th class="px-6 py-4 text-center text-sm font-semibold">CV</th>
                <th class="px-6 py-4 text-center text-sm font-semibold">Action</th>
              </tr>
            </thead>

            <!-- Body -->
            <tbody class="divide-y divide-gray-200">
              <tr
                v-for="app in applications"
                :key="app.id"
                class="hover:bg-blue-50 transition"
              >
                <td class="px-6 py-4 font-semibold text-gray-800">
                  {{ app.full_name }}
                </td>

                <td class="px-6 py-4 text-gray-600">
                  {{ app.contact }}
                </td>

                <td class="px-6 py-4 text-gray-600">
                  {{ app.email }}
                </td>

                <td class="px-6 py-4 text-gray-700">
                  {{ app.position }}
                </td>

                <td class="px-6 py-4 text-gray-500">
                  {{ app.created_at }}
                </td>

                <!-- View CV -->
                <td class="px-6 py-4 text-center">
                  <a
                    :href="app.file_url"
                    target="_blank"
                    class="px-4 py-2 bg-yellow-400 text-blue-950 font-semibold rounded-lg shadow hover:shadow-lg transition"
                  >
                    View CV
                  </a>
                </td>

                <!-- Delete Button -->
                <td class="px-6 py-4 text-center">
                  <button
                    @click="deleteApplication(app.id)"
                    class="px-4 py-2 bg-red-600 text-white font-semibold rounded-lg shadow hover:bg-red-700 transition"
                  >
                    Delete
                  </button>
                </td>

              </tr>
            </tbody>

          </table>
        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
  applications: Array
})

defineOptions({
  layout: AdminLayout
})

function deleteApplication(id) {
  if (!confirm('Are you sure you want to delete this application?')) return

  router.delete(`/admin/applications/${id}`, {
    preserveScroll: true
  })
}
</script>

<style scoped>
table {
  border-collapse: separate;
  border-spacing: 0;
}
</style>