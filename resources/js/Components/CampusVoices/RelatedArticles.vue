<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  articles: {
    type: Array,
    default: () => []
  }
})
</script>

<template>
  <div v-if="articles.length > 0" class="mb-12">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Articles</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div
        v-for="article in articles"
        :key="article.id"
        class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-shadow"
      >
        <div class="h-40 bg-gray-100">
          <img
            v-if="article.image_url"
            :src="article.image_url"
            class="w-full h-full object-cover"
          />
          <div v-else class="flex items-center justify-center h-full text-gray-400">
            No image
          </div>
        </div>
        <div class="p-4">
          <p class="text-sm text-gray-600 mb-1">{{ article.student_name }}</p>
          <h3 class="font-bold text-gray-900 mb-2 line-clamp-2">
            {{ article.title }}
          </h3>
          <p class="text-sm text-gray-500 mb-3">{{ article.published_at }}</p>
          <Link
            :href="route('campus-voices.show', article.slug)"
            class="text-blue-600 hover:text-blue-800 font-medium"
          >
            Read More →
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
