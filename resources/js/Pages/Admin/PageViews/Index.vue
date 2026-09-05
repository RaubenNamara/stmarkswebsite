<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ref, onMounted } from 'vue'

defineOptions({
  layout: AdminLayout
})

const viewStats = ref({
  total_views: 0,
  daily_views: [],
  by_page_type: []
})

const loading = ref(true)

async function loadViewStats() {
  try {
    const response = await fetch('/api/page-view/stats')
    const data = await response.json()
    viewStats.value = data
  } catch (error) {
    console.error('Failed to load view stats:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadViewStats()
})

function formatDate(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('en-GB', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}
</script>

<template>
  <div class="p-8 space-y-8">
    <!-- PAGE HEADER -->
    <div class="flex items-center justify-between">
      <h1 class="text-3xl font-bold text-gray-800">
        Page Views Statistics
      </h1>
    </div>

    <!-- TOTAL VIEWS CARD -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-lg p-6 text-white">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-blue-100 text-sm font-medium mb-1">Total Page Views</p>
          <p class="text-4xl font-bold">
            {{ viewStats.total_views }}
          </p>
        </div>
        <div class="text-6xl opacity-80">
          👁
        </div>
      </div>
    </div>

    <!-- DAILY VIEWS CHART -->
    <div class="bg-white rounded-xl shadow-lg p-6">
      <h2 class="text-xl font-bold text-gray-800 mb-4">
        Daily Views (Last 30 Days)
      </h2>

      <div v-if="loading" class="text-center py-8 text-gray-500">
        Loading...
      </div>

      <div v-else-if="viewStats.daily_views.length === 0" class="text-center py-8 text-gray-500">
        No view data available yet.
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="item in viewStats.daily_views"
          :key="item.view_date"
          class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
        >
          <div class="flex items-center gap-3">
            <span class="text-sm font-medium text-gray-700">
              {{ formatDate(item.view_date) }}
            </span>
          </div>
          <div class="flex items-center gap-2">
            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
              {{ item.views }} views
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- VIEWS BY PAGE TYPE -->
    <div class="bg-white rounded-xl shadow-lg p-6">
      <h2 class="text-xl font-bold text-gray-800 mb-4">
        Views by Page Type
      </h2>

      <div v-if="loading" class="text-center py-8 text-gray-500">
        Loading...
      </div>

      <div v-else-if="viewStats.by_page_type.length === 0" class="text-center py-8 text-gray-500">
        No page type data available yet.
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="item in viewStats.by_page_type"
          :key="item.page_type"
          class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
        >
          <div class="flex items-center gap-3">
            <span class="text-sm font-medium text-gray-700 uppercase">
              {{ item.page_type }}
            </span>
          </div>
          <div class="flex items-center gap-2">
            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
              {{ item.views }} views
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
