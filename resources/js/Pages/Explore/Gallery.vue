<template>
  <div class="max-w-[1000px] md:max-w-[1200px] lg:max-w-[1400px] xl:max-w-[1600px] 2xl:max-w-[1800px] mx-auto py-12 sm:py-16 px-4 sm:px-6 lg:px-8">

    <!-- Page Title -->
    <div class="text-center mb-10">
      <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900">
        School Gallery
      </h1>
      <p class="mt-3 text-sm sm:text-base text-gray-600">
        Explore our events and memorable moments.
      </p>
    </div>

    <!-- Events -->
    <div v-if="events.length" class="space-y-14 sm:space-y-16 lg:space-y-20">

      <section v-for="event in events" :key="event.id">

        <!-- Event Title -->
        <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold text-indigo-700 mb-6 border-l-4 border-indigo-600 pl-4">
          {{ event.title }}
        </h2>

        <!-- Images Grid -->
        <div class="grid 
                    grid-cols-2 
                    sm:grid-cols-3 
                    md:grid-cols-4 
                    lg:grid-cols-5 
                    xl:grid-cols-6 
                    gap-4 sm:gap-5">

          <div
            v-for="img in event.images"
            :key="img.id"
            class="group overflow-hidden shadow hover:shadow-lg transition cursor-pointer"
            @click="openModal(img.image_url)"
          >
            <img
              :src="img.image_url"
              class="w-full h-40 sm:h-44 md:h-48 lg:h-44 xl:h-48 2xl:h-52 object-cover 
                     group-hover:scale-110 transition duration-300"
              loading="lazy"
            />
          </div>

        </div>

      </section>

    </div>

    <!-- Empty State -->
    <div v-else class="text-center text-gray-500 py-20">
      No gallery events available yet.
    </div>

    <!-- Image Modal -->
    <div
      v-if="selectedImage"
      class="fixed inset-0 bg-black/90 flex items-center justify-center z-50 p-4"
      @click.self="selectedImage = null"
    >
      <img
        :src="selectedImage"
        class="max-h-[85vh] sm:max-h-[90vh] max-w-[95vw] sm:max-w-[90vw] shadow-2xl"
      />
    </div>

  </div>
</template>

<script setup>
defineProps({
  events: {
    type: Array,
    default: () => []
  }
})

import MainLayout from '@/Layouts/MainLayout.vue'

defineOptions({
  layout: MainLayout
})

import { ref } from 'vue'

const selectedImage = ref(null)

function openModal(url) {
  selectedImage.value = url
}
</script>