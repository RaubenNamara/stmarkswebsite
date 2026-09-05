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
  <div class="bg-white rounded-lg border-2 border-gray-200 overflow-hidden hover:border-gray-400 hover:shadow-lg transition-all duration-300 flex flex-col">
    <!-- Image -->
    <div class="h-48 xl:h-56 bg-gray-50 relative overflow-hidden flex items-center justify-center border-b border-gray-200">
      <img
        v-if="article.image_url"
        :src="article.image_url"
        class="w-full h-full object-contain hover:scale-105 transition-transform duration-300"
        loading="lazy"
      />
      <div v-else class="flex items-center justify-center h-full text-gray-400">
        No image
      </div>
    </div>

    <!-- Content -->
    <div class="p-5 xl:p-6 flex flex-col flex-1">
      <!-- Category Badge -->
      <div class="mb-3">
        <span v-if="article.category" class="bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-sm font-medium border border-blue-200">
          {{ article.category }}
        </span>
      </div>

      <!-- Student Name -->
      <p class="text-sm text-gray-600 mb-2">
        <span class="font-semibold">{{ article.student_name }}</span>
      </p>

      <!-- Title -->
      <h3 class="text-xl xl:text-2xl font-bold text-gray-900 mb-3 line-clamp-2">
        {{ article.title }}
      </h3>

      <!-- Summary -->
      <p class="text-gray-600 xl:text-lg mb-4 line-clamp-3 flex-1">
        {{ article.summary || 'No summary available.' }}
      </p>

      <!-- Meta -->
      <div class="flex items-center justify-between text-sm text-gray-500 mb-4 pt-3 border-t border-gray-100">
        <span>{{ article.published_at }}</span>
        <span class="flex items-center gap-1">
          👁 {{ article.views }}
        </span>
      </div>

      <!-- Read More Button -->
      <Link
        :href="route('campus-voices.show', article.slug)"
        class="inline-block bg-blue-900 text-white px-4 py-2 rounded-lg text-center font-medium hover:bg-blue-800 transition transform hover:scale-105 border border-blue-900"
      >
        Read More
      </Link>
    </div>
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-clamp: 2;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-clamp: 3;
}
</style>
