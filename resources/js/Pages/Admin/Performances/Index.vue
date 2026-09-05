<script setup>
import { router } from '@inertiajs/vue3'
import { ref, onBeforeUnmount } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  performances: {
    type: Array,
    default: () => []
  }
})

const title = ref('')
const pdf = ref(null)
const selectedPerformance = ref(null)

// ==========================
// HANDLE FILE INPUT
// ==========================
function handlePdfChange(e) {
  pdf.value = e.target.files[0]
}

// ==========================
// SUBMIT (SINGLE REQUEST ✅)
// ==========================
function submit() {
  if (!title.value || !pdf.value) {
    alert('Please provide title and PDF')
    return
  }

  const formData = new FormData()
  formData.append('title', title.value)
  formData.append('pdf', pdf.value)

  router.post('/admin/performances', formData, {
    forceFormData: true,
    preserveScroll: true,

    onSuccess: () => {
      title.value = ''
      pdf.value = null

      const fileInput = document.querySelector('input[type="file"]')
      if (fileInput) fileInput.value = ''
    },

    onError: (errors) => {
      console.error(errors)
      alert('Upload failed')
    }
  })
}

// ==========================
// DELETE
// ==========================
function deletePerformance(id) {
  if (!confirm('Delete this file?')) return

  router.delete(`/admin/performances/${id}`, {
    preserveScroll: true
  })
}

// ==========================
// VIEW PDF
// ==========================
function viewPerformance(p) {
  selectedPerformance.value = p
  document.body.style.overflow = 'hidden'
}

// ==========================
// CLOSE VIEWER
// ==========================
function closeViewer() {
  selectedPerformance.value = null
  document.body.style.overflow = ''
}

onBeforeUnmount(() => {
  document.body.style.overflow = ''
})
</script>

<template>
  <div class="max-w-6xl mx-auto px-8 py-14">
    
    <!-- TITLE -->
    <h1 class="text-3xl font-bold mb-6">
      Performance Reports and Circulars
    </h1>

    <!-- UPLOAD FORM -->
    <div class="bg-white p-6 rounded shadow mb-10">
      
      <input
        v-model="title"
        placeholder="Enter report title"
        class="border p-3 w-full mb-4 rounded"
      />

      <input
        type="file"
        accept="application/pdf"
        @change="handlePdfChange"
        class="mb-4"
      />

      <button
        @click="submit"
        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700"
      >
        Upload
      </button>
    </div>

    <!-- LIST -->
    <div v-if="performances.length">
      <div
        v-for="p in performances"
        :key="p.id"
        class="border p-4 mb-4 flex justify-between items-center rounded"
      >
        <span class="font-medium">{{ p.title }}</span>

        <div class="space-x-3">
          
          <button
            @click="viewPerformance(p)"
            class="text-green-600 hover:underline"
          >
            View
          </button>

          <a
            :href="p.pdf_url"
            target="_blank"
            rel="noopener noreferrer"
            class="text-blue-600 hover:underline"
          >
            Open
          </a>

          <button
            @click="deletePerformance(p.id)"
            class="text-red-600 hover:underline"
          >
            Delete
          </button>

        </div>
      </div>
    </div>

    <!-- EMPTY STATE -->
    <div v-else class="text-gray-500 text-center mt-10">
      No performance or Ciculars reports uploaded yet.
    </div>

    <!-- MODAL VIEWER -->
    <div
      v-if="selectedPerformance"
      class="fixed inset-0 bg-black/70 flex items-center justify-center z-50"
    >
      <div class="bg-white w-[90%] h-[90%] rounded shadow flex flex-col overflow-hidden">

        <!-- HEADER -->
        <div class="flex justify-between items-center p-4 border-b bg-gray-100">
          <h2 class="font-semibold text-lg">
            {{ selectedPerformance.title }}
          </h2>

          <button
            @click="closeViewer"
            class="text-gray-600 hover:text-black"
          >
            Close
          </button>
        </div>

        <!-- PDF VIEW -->
        <div class="flex-1">
          <embed
            :src="selectedPerformance.pdf_url"
            type="application/pdf"
            class="w-full h-full"
          />
        </div>

      </div>
    </div>

  </div>
</template>

<style scoped>
button {
  transition: 0.2s;
}
</style>