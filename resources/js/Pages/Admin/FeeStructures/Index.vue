<template>
  <div class="min-h-screen bg-gray-100">
    <div class="max-w-6xl mx-auto py-12 px-6">

      <h1 class="text-3xl font-bold text-blue-900 mb-8">
        Fee Structure Management
      </h1>

      <!-- Upload Form -->
      <div class="bg-white shadow-xl rounded-2xl p-8 mb-10">
        <h2 class="text-xl font-semibold text-blue-900 mb-6">
          Upload Fee Structure (PDF)
        </h2>

        <form @submit.prevent="submit" class="space-y-6">

          <div>
            <label class="block text-sm font-semibold text-blue-900 mb-2">
              Title
            </label>
            <input
              v-model="form.title"
              type="text"
              required
              class="input"
              placeholder="e.g S1 Term 1 2025"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-blue-900 mb-2">
              Upload PDF (Max 10MB)
            </label>
            <input
              type="file"
              accept=".pdf"
              @change="handleFile"
              required
              class="input"
            />
          </div>

          <button
            type="submit"
            class="bg-yellow-400 text-blue-900 px-6 py-3 rounded-lg font-semibold shadow hover:shadow-lg"
          >
            Upload Fee Structure
          </button>

        </form>
      </div>

      <!-- Table -->
      <div class="bg-white shadow-xl rounded-2xl overflow-hidden">

        <div v-if="fees.length === 0"
             class="p-8 text-center text-gray-500">
          No fee structures uploaded yet.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">

            <thead class="bg-blue-900 text-white">
              <tr>
                <th class="th">Title</th>
                <th class="th">Uploaded</th>
                <th class="th text-center">View</th>
                <th class="th text-center">Delete</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
              <tr v-for="fee in fees" :key="fee.id"
                  class="hover:bg-blue-50 transition">

                <td class="td font-semibold">
                  {{ fee.title }}
                </td>

                <td class="td">
                  {{ fee.created_at }}
                </td>

                <td class="td text-center">
                  <a
                    :href="route('fee-structures.pdf', fee.id)"
                    target="_blank"
                    class="bg-blue-900 text-white px-4 py-2 rounded-lg"
                  >
                    View PDF
                  </a>
                </td>

                <td class="td text-center">
                  <button
                    @click="deleteFee(fee.id)"
                    class="bg-red-600 text-white px-4 py-2 rounded-lg"
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
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({ fees: Array })
defineOptions({ layout: AdminLayout })

const form = useForm({
  title: '',
  pdf: null   // ✅ FIXED FIELD NAME
})

function handleFile(e) {
  form.pdf = e.target.files[0]  // ✅ FIXED
}

function submit() {
  form.post(route('admin.fee-structures.store'), {
    forceFormData: true,   // ✅ REQUIRED
    onSuccess: () => form.reset()
  })
}

function deleteFee(id) {
  if (confirm('Delete this fee structure?')) {
    form.delete(route('admin.fee-structures.destroy', id))
  }
}
</script>

<style scoped>
.input {
  @apply w-full border border-gray-300 rounded-lg px-4 py-2;
}
.th {
  @apply px-6 py-4 text-left text-sm font-semibold;
}
.td {
  @apply px-6 py-4 text-sm text-gray-700;
}
</style>