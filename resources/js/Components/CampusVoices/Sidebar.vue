<script setup>
const props = defineProps({
  categories: {
    type: Array,
    default: () => []
  },
  recentArticles: {
    type: Array,
    default: () => []
  },
  mostViewed: {
    type: Array,
    default: () => []
  },
  featuredArticles: {
    type: Array,
    default: () => []
  },
  searchQuery: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:searchQuery', 'clearSearch'])

function handleSearch(e) {
  emit('update:searchQuery', e.target.value)
}

function handleClearSearch() {
  emit('clearSearch')
}
</script>

<template>
  <div class="space-y-8">
    
    <!-- Search by Student Name -->
    <div class="bg-white rounded-lg border-2 border-gray-200 p-6">
      <h3 class="text-lg font-bold text-gray-900 mb-4">Search by Student Name</h3>
      <div class="relative">
        <input
          :value="searchQuery"
          @input="handleSearch"
          type="text"
          placeholder="Search student name..."
          class="w-full pl-10 pr-10 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        />
        <svg
          class="absolute left-3 top-3.5 h-5 w-5 text-gray-400"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
          />
        </svg>
        <button
          v-if="searchQuery"
          @click="handleClearSearch"
          class="absolute right-3 top-3.5 text-gray-400 hover:text-gray-600"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Categories -->
    <div v-if="categories.length > 0" class="bg-white rounded-lg border-2 border-gray-200 p-6">
      <h3 class="text-lg font-bold text-gray-900 mb-4">Categories</h3>
      <ul class="space-y-2">
        <li v-for="category in categories" :key="category">
          <a
            :href="route('explore.campus-voices', { category })"
            class="text-gray-700 hover:text-blue-600 transition block py-1"
          >
            {{ category }}
          </a>
        </li>
      </ul>
    </div>

    <!-- Recently Published -->
    <div v-if="recentArticles.length > 0" class="bg-white rounded-lg border-2 border-gray-200 p-6">
      <h3 class="text-lg font-bold text-gray-900 mb-4">Recently Published</h3>
      <ul class="space-y-3">
        <li v-for="article in recentArticles" :key="article.id">
          <a
            :href="route('campus-voices.show', article.slug)"
            class="block group"
          >
            <p class="text-sm text-gray-600 mb-1">{{ article.student_name }}</p>
            <h4 class="font-medium text-gray-900 group-hover:text-blue-600 transition line-clamp-2">
              {{ article.title }}
            </h4>
            <p class="text-xs text-gray-500 mt-1">{{ article.published_at }}</p>
          </a>
        </li>
      </ul>
    </div>

    <!-- Most Viewed -->
    <div v-if="mostViewed.length > 0" class="bg-white rounded-lg border-2 border-gray-200 p-6">
      <h3 class="text-lg font-bold text-gray-900 mb-4">Most Viewed</h3>
      <ul class="space-y-3">
        <li v-for="article in mostViewed" :key="article.id">
          <a
            :href="route('campus-voices.show', article.slug)"
            class="block group"
          >
            <p class="text-sm text-gray-600 mb-1">{{ article.student_name }}</p>
            <h4 class="font-medium text-gray-900 group-hover:text-blue-600 transition line-clamp-2">
              {{ article.title }}
            </h4>
            <p class="text-xs text-gray-500 mt-1">👁 {{ article.views }} views</p>
          </a>
        </li>
      </ul>
    </div>

    <!-- Featured Articles -->
    <div v-if="featuredArticles.length > 0" class="bg-white rounded-lg border-2 border-gray-200 p-6">
      <h3 class="text-lg font-bold text-gray-900 mb-4">Featured Articles</h3>
      <ul class="space-y-3">
        <li v-for="article in featuredArticles" :key="article.id">
          <a
            :href="route('campus-voices.show', article.slug)"
            class="block group"
          >
            <p class="text-sm text-gray-600 mb-1">{{ article.student_name }}</p>
            <h4 class="font-medium text-gray-900 group-hover:text-blue-600 transition line-clamp-2">
              {{ article.title }}
            </h4>
            <span class="inline-block mt-1 text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded border border-blue-200">
              ⭐ Featured
            </span>
          </a>
        </li>
      </ul>
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
