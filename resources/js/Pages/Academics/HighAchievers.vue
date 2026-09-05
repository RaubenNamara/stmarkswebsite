<script setup>
import MainLayout from '@/Layouts/MainLayout.vue'

defineOptions({
  layout: MainLayout
})

const props = defineProps({
  achievers: {
    type: Array,
    default: () => []
  }
})
</script>

<template>
  <div class="min-h-screen bg-gradient-to-b from-slate-50 via-white to-indigo-50/40">

    <!-- CONTAINER -->
    <div class="mx-auto max-w-[1400px] 2xl:max-w-[1600px] px-4 py-16 sm:px-6 lg:px-8">

      <!-- HERO SECTION -->
      <section class="mb-16 text-center">

        <div class="mb-5 inline-flex items-center rounded-full border border-indigo-200 bg-white px-4 py-2 text-sm font-medium text-indigo-700 shadow-sm">
          Celebrating Academic Excellence
        </div>

        <h1 class="bg-gradient-to-r from-indigo-700 via-purple-700 to-pink-600 bg-clip-text text-4xl font-extrabold tracking-tight text-transparent sm:text-5xl lg:text-6xl">
          Wall of Fame
        </h1>

        <p class="mx-auto mt-4 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg">
          A special collection of our outstanding students who have achieved remarkable academic success.
        </p>

      </section>

      <!-- GRID -->
      <div
        v-if="achievers.length"
        class="grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6"
      >

        <!-- CARD -->
        <article
          v-for="student in achievers"
          :key="student.id"
          class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-indigo-200/40 flex flex-col"
        >

          <!-- IMAGE -->
          <div class="relative aspect-[3/4] overflow-hidden bg-gradient-to-b from-slate-100 to-slate-200">

            <img
              v-if="student.photo"
              :src="'/storage/high_achievers/' + student.photo"
              :alt="student.name"
              class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
            />

            <!-- OVERLAY -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>

            <!-- BADGE -->
            <div class="absolute top-4 left-4">
              <span class="bg-white/90 text-indigo-700 text-xs font-semibold px-3 py-1 rounded-full shadow">
                {{ student.exam }}
              </span>
            </div>

            <!-- YEAR -->
            <div class="absolute bottom-4 left-4 right-4">
              <div class="bg-white/85 backdrop-blur-sm rounded-xl p-2 text-center shadow">
                <p class="text-xs text-gray-500">Year</p>
                <p class="text-sm font-bold text-gray-900">{{ student.year }}</p>
              </div>
            </div>

          </div>

          <!-- CONTENT -->
          <div class="p-5 text-center flex flex-col flex-grow justify-between">

            <div>
              <h3 class="text-base md:text-lg font-bold text-gray-900">
                {{ student.name }}
              </h3>

              <p class="mt-1 text-sm font-semibold text-indigo-700">
                {{ student.division }}
              </p>
            </div>

          </div>

        </article>

      </div>

      <!-- EMPTY STATE -->
      <div
        v-else
        class="mx-auto mt-12 max-w-xl rounded-3xl border border-dashed border-gray-300 bg-white px-6 py-20 text-center shadow-sm"
      >
        <div class="text-5xl mb-4">🎓</div>

        <h3 class="text-2xl font-bold text-gray-900">
          No high achievers available yet
        </h3>

        <p class="mt-3 text-sm text-gray-500">
          Students will appear here once added.
        </p>
      </div>

    </div>
  </div>
</template>

<style scoped>
</style>