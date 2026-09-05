<template>
  <div class="max-w-[900px] md:max-w-[1100px] lg:max-w-[1300px] xl:max-w-[1500px] 2xl:max-w-[1700px] mx-auto py-12 px-4 sm:px-6 lg:px-8">

    <!-- Header -->
    <header class="mb-10 text-center">
      <h1
        class="text-2xl sm:text-3xl lg:text-4xl xl:text-5xl font-extrabold 
               bg-clip-text text-transparent
               bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500"
      >
        Fee Structures, Personal Needs, School Rules and Calendar
      </h1>

      <p class="mt-3 text-sm sm:text-base text-gray-500 max-w-2xl mx-auto">
        Browse the latest fee and Personal Needs documents. Click 
        <span class="font-semibold text-indigo-700">View PDF</span> to open.
      </p>
    </header>

    <!-- Empty State -->
    <div v-if="fees.length === 0" class="text-center text-gray-500 py-20">
      No fee structures available or Personal Needs
    </div>

    <!-- Cards Grid (KEY FIX) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6 xl:gap-8">

      <article
        v-for="fee in fees"
        :key="fee.id"
        class="relative overflow-hidden rounded-2xl transition hover:-translate-y-1"
      >
        <!-- Background -->
        <div
          class="absolute inset-0 rounded-2xl"
          :style="cardBgStyle"
        ></div>

        <!-- Content -->
        <div class="relative bg-white/70 backdrop-blur-md border border-white/30 
                    rounded-2xl p-5 sm:p-6 shadow-md 
                    flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

          <!-- Left -->
          <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <h2 class="text-lg sm:text-xl lg:text-2xl font-extrabold text-indigo-900 truncate">
                {{ fee.title }}
              </h2>

              <span class="px-3 py-1 rounded-full text-xs font-medium 
                           text-indigo-900 bg-indigo-200">
                {{ fee.id }}
              </span>
            </div>

            <p class="mt-2 text-xs sm:text-sm text-gray-600">
              Uploaded:
              <span class="font-medium text-indigo-800">
                {{ formatDate(fee.created_at) }}
              </span>
            </p>
          </div>

          <!-- Actions -->
          <div class="flex flex-row sm:flex-col gap-2 sm:gap-3 w-full sm:w-auto">

            <button
              @click="openPdf(fee.id)"
              class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg shadow-md
                     bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600
                     text-white text-sm font-semibold hover:scale-[1.02] active:scale-95 transition"
            >
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2a2 2 0 00-2 2v2H6a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H12z"/>
              </svg>
              View PDF
            </button>

            <a
              :href="route('fee-structures.pdf', fee.id)"
              target="_blank"
              class="flex-1 sm:flex-none inline-flex items-center justify-center px-3 py-2 text-sm rounded-lg border bg-white hover:bg-gray-50 transition"
            >
              Open
            </a>

          </div>
        </div>
      </article>
    </div>

    <!-- PDF VIEWER -->
    <section v-if="selectedPdf" class="mt-12">
      <div class="bg-white rounded-2xl shadow-2xl p-4 sm:p-6 border">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
          <h3 class="text-lg sm:text-xl font-bold text-indigo-900">
            PDF Preview
          </h3>

          <button
            @click="selectedPdf = null"
            class="text-red-600 font-semibold"
          >
            Close
          </button>
        </div>

        <!-- Responsive PDF -->
        <div class="w-full border rounded-lg overflow-hidden">

          <iframe
            :src="selectedPdf"
            class="w-full h-[500px] sm:h-[700px] lg:h-[900px] xl:h-[1000px]"
          ></iframe>

        </div>

      </div>
    </section>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import MainLayout from '@/Layouts/MainLayout.vue'

defineOptions({
  layout: MainLayout
})

defineProps({
  fees: {
    type: Array,
    default: () => []
  }
})

const selectedPdf = ref(null)

function openPdf(id) {
  selectedPdf.value = route('fee-structures.pdf', id)
}

function formatDate(dt) {
  if (!dt) return ''
  const d = new Date(dt)
  if (isNaN(d.getTime())) return dt
  return d.toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

const cardBgStyle = computed(() => {
  return `background: linear-gradient(135deg, rgba(99,102,241,0.06), rgba(168,85,247,0.04), rgba(236,72,153,0.03));`
})
</script>

<style scoped>
/* Optional polish */
article:hover {
  transform: translateY(-4px);
}
</style>