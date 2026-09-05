<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  article: {
    type: Object,
    required: true
  }
})
</script>

<template>
  <div v-if="article" class="bg-gradient-to-br from-blue-50 to-white border-b-4 border-blue-900">
    <div class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 2xl:px-16 py-12 lg:py-16 xl:py-20">
      <div class="flex flex-col lg:flex-row items-center gap-6 lg:gap-8 xl:gap-10">
        
        <!-- Image -->
        <div class="w-full lg:w-1/2 flex items-center justify-center">
          <div class="relative w-full">
            <div class="bg-white rounded-2xl shadow-2xl border-2 border-gray-200 p-4">
              <img
                v-if="article.image_url"
                :src="article.image_url"
                class="w-full h-48 sm:h-64 lg:h-80 xl:h-96 2xl:h-[28rem] object-contain rounded-lg"
              />
              <div v-else class="w-full h-48 sm:h-64 lg:h-80 xl:h-96 2xl:h-[28rem] bg-gray-50 rounded-lg flex items-center justify-center border-2 border-gray-200">
                <span class="text-gray-400">No image</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div class="w-full lg:w-1/2 space-y-4 lg:space-y-5 xl:space-y-6 text-left">
          <h1 class="text-xl sm:text-2xl lg:text-3xl xl:text-4xl 2xl:text-5xl font-bold leading-tight text-gray-900">
            {{ article.title }}
          </h1>
          
          <p class="text-base sm:text-lg lg:text-xl xl:text-2xl text-gray-700">
            By <span class="font-semibold text-blue-900">{{ article.student_name }}</span>
            <span class="inline-block bg-gradient-to-r from-blue-900 to-blue-700 text-white px-5 py-2 rounded-full text-xs sm:text-sm font-bold shadow-md ml-4">
              ⭐ Featured Article
            </span>
          </p>
          
          <p class="text-sm sm:text-base lg:text-lg xl:text-xl text-gray-600 leading-relaxed text-justify line-clamp-4">
            {{ article.summary }}
          </p>
          
          <div class="flex flex-wrap items-center gap-3 lg:gap-4 text-xs sm:text-sm text-gray-600 pt-4 border-t-2 border-gray-200">
            <span v-if="article.category" class="bg-blue-100 text-blue-800 px-4 py-2 rounded-full font-medium border border-blue-300">
              {{ article.category }}
            </span>
            <span class="flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              {{ article.published_at }}
            </span>
            <span class="flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              {{ article.views }} views
            </span>
          </div>
          
          <Link
            :href="route('campus-voices.show', article.slug)"
            class="inline-block bg-gradient-to-r from-blue-900 to-blue-700 text-white px-8 sm:px-10 py-3 sm:py-4 rounded-xl text-sm sm:text-base font-semibold hover:from-blue-800 hover:to-blue-600 transition transform hover:scale-105 shadow-lg border-2 border-blue-900"
          >
            Read More
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-clamp: 3;
}
</style>
