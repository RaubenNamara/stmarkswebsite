<script setup>
import MainLayout from '@/Layouts/MainLayout.vue'
import { Head, usePage, router } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import HeroArticle from '@/Components/CampusVoices/HeroArticle.vue'
import ArticleCard from '@/Components/CampusVoices/ArticleCard.vue'
import Sidebar from '@/Components/CampusVoices/Sidebar.vue'

defineOptions({ layout: MainLayout })

const page = usePage()
const allArticles = ref(page.props.articles || [])
const featuredArticle = ref(page.props.featuredArticle || null)
const categories = ref(page.props.categories || [])
const recentArticles = ref(page.props.recentArticles || [])
const mostViewed = ref(page.props.mostViewed || [])

/*
|--------------------------------------------------------------------------
| SEARCH (Client-side filtering)
|--------------------------------------------------------------------------
*/
const searchQuery = ref('')

const articles = computed(() => {
  if (!searchQuery.value) {
    return allArticles.value
  }
  const query = searchQuery.value.toLowerCase()
  return allArticles.value.filter(article =>
    article.student_name.toLowerCase().includes(query)
  )
})

function clearSearch() {
  searchQuery.value = ''
}

/*
|--------------------------------------------------------------------------
| GRID LAYOUT
|--------------------------------------------------------------------------
*/
const gridCols = computed(() =>
  'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-4 md:gap-6 lg:gap-6 xl:gap-8'
)
</script>

<template>
  <Head title="Campus Voices" />

  <div class="min-h-screen bg-white">
    
    <!-- ================= HERO SECTION ================= -->
    <HeroArticle v-if="featuredArticle" :article="featuredArticle" />

    <!-- ================= MAIN CONTENT ================= -->
    <div class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 2xl:px-16 py-12 xl:py-16">

      <div class="flex flex-col lg:flex-row gap-6 lg:gap-8 xl:gap-10 2xl:gap-12">

        <!-- Main Content Area -->
        <div class="flex-1">

          <!-- Page Title -->
          <div class="mb-8">
            <h2 class="text-3xl xl:text-4xl font-bold text-gray-900 mb-6">Campus Voices</h2>
          </div>

          <!-- No Results -->
          <div v-if="articles.length === 0" class="text-center py-12">
            <p class="text-xl text-gray-600">
              {{ searchQuery ? 'No articles found matching your search.' : 'No Campus Voices articles yet.' }}
            </p>
          </div>

          <!-- Articles Grid -->
          <div v-else :class="gridCols">
            <ArticleCard
              v-for="article in articles"
              :key="article.id"
              :article="article"
            />
          </div>

        </div>

        <!-- Sidebar -->
        <div class="lg:w-80 2xl:w-96 shrink-0">
          <Sidebar
            :categories="categories"
            :recent-articles="recentArticles"
            :most-viewed="mostViewed"
            :search-query="searchQuery"
            @update:searchQuery="searchQuery = $event"
            @clearSearch="clearSearch"
          />
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

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
