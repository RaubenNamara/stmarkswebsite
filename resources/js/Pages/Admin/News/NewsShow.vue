<template>
  <div class="max-w-5xl mx-auto px-6 py-12">

    <!-- HEADER -->
    <div class="flex items-center justify-between mb-8">
      <h1 class="text-3xl font-bold">News Details</h1>

      <Link
        href="/admin/news"
        class="text-indigo-600 hover:underline"
      >
        ← Back to News
      </Link>
    </div>

    <!-- CARD -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">

      <!-- ✅ IMAGE FIX -->
      <div v-if="news.image_url" class="h-80 overflow-hidden">
        <img
          :src="news.image_url"
          class="w-full h-full object-cover"
        />
      </div>

      <div class="p-8">

        <!-- TITLE -->
        <h2 class="text-2xl font-extrabold mb-4">
          {{ news.title }}
        </h2>

        <!-- DATE -->
        <p class="text-gray-500 mb-6">
          {{ formatDate(news.created_at) }}
        </p>

        <!-- EXCERPT -->
        <p
          v-if="news.excerpt"
          class="italic text-gray-600 mb-6 border-l-4 border-indigo-500 pl-4"
        >
          {{ news.excerpt }}
        </p>

        <!-- CONTENT -->
        <div class="prose max-w-none text-gray-700 whitespace-pre-line">
          {{ news.content }}
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  news: Object
})

/* DATE FORMAT */
function formatDate(date) {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-GB', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}
</script>