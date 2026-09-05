<script setup>
import { ref, computed } from 'vue'
import MainLayout from '@/Layouts/MainLayout.vue'

defineOptions({
  layout: MainLayout
})

const props = defineProps({
  performances: {
    type: Array,
    default: () => []
  }
})

const selectedPdf = ref(null)
const selectedTitle = ref('')

/* OPEN PDF */
function openPdf(performance) {
  selectedTitle.value = performance.title
  selectedPdf.value = performance.pdf_url
}

/* CLOSE PDF */
function closePdf() {
  selectedPdf.value = null
  selectedTitle.value = ''
}

/* FORMAT DATE */
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

/* CARD BACKGROUND */
const cardBgStyle = computed(() => {
  return `background: linear-gradient(
    135deg,
    rgba(99,102,241,0.06) 0%,
    rgba(168,85,247,0.04) 50%,
    rgba(236,72,153,0.03) 100%
  );`
})
</script>

<template>
  <div class="bg-gray-50 min-h-screen py-16">

    <!-- CONTAINER (FIXED FOR WIDE SCREENS) -->
    <div class="max-w-[1100px] xl:max-w-[1200px] mx-auto px-4 sm:px-6">

      <!-- HEADER -->
      <header class="mb-14 text-center">
        <h1
          class="text-4xl md:text-5xl font-extrabold bg-clip-text text-transparent
                 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500"
        >
          Academic Performance & Circulars
        </h1>

        <p class="mt-4 text-gray-500 text-lg max-w-2xl mx-auto">
          Browse academic performance documents and click
          <span class="font-semibold text-indigo-700">View PDF</span>
          to open in the viewer.
        </p>
      </header>

      <!-- EMPTY -->
      <div v-if="performances.length === 0" class="text-center text-gray-500 py-20">
        No performance results available.
      </div>

      <!-- LIST -->
      <div v-else class="space-y-8">

        <article
          v-for="performance in performances"
          :key="performance.id"
          class="relative overflow-hidden rounded-2xl p-1 transition-transform hover:-translate-y-1"
        >

          <!-- BACKGROUND -->
          <div
            class="absolute inset-0 pointer-events-none rounded-2xl"
            :style="cardBgStyle"
          ></div>

          <!-- CONTENT -->
          <div
            class="relative bg-white/80 backdrop-blur-md border border-white/50
                   rounded-2xl p-5 md:p-6 shadow-md flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
          >

            <!-- TEXT -->
            <div class="min-w-0">
              <h2 class="text-xl md:text-2xl lg:text-3xl font-bold text-indigo-900 truncate">
                {{ performance.title }}
              </h2>

              <p class="mt-2 text-sm text-gray-600">
                Uploaded:
                <span class="font-medium text-indigo-800">
                  {{ formatDate(performance.created_at) }}
                </span>
              </p>
            </div>

            <!-- BUTTON -->
            <button
              @click="openPdf(performance)"
              class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg shadow-md
                     bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600
                     text-white font-semibold hover:scale-[1.03] active:scale-95 transition"
            >
              View PDF
            </button>

          </div>

        </article>

      </div>

      <!-- PDF VIEWER -->
      <section v-if="selectedPdf" class="mt-16">

        <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">

          <!-- HEADER -->
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-5 border-b">

            <h3 class="text-lg md:text-xl font-bold text-indigo-900">
              {{ selectedTitle }}
            </h3>

            <button
              @click="closePdf"
              class="text-red-600 font-semibold hover:underline"
            >
              Close
            </button>

          </div>

          <!-- PDF FRAME -->
          <div class="w-full">
            <iframe
              :src="selectedPdf"
              class="w-full"
              style="height: 75vh;"
            ></iframe>
          </div>

        </div>

      </section>

    </div>
  </div>
</template>

<style scoped>
button {
  transition: 0.2s;
}
</style>