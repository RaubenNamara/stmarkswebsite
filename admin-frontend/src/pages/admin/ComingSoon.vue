<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { navItems } from '@/router/navItems'

const route = useRoute()
const label = computed(() => navItems.find((item) => item.key === route.params.key)?.label ?? 'This section')
</script>

<template>
  <div class="p-8">
    <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
      <p class="text-lg font-medium text-gray-700">{{ label }}</p>
      <p class="mt-2 text-sm">This section hasn't been migrated from the old admin panel yet.</p>
    </div>
  </div>
</template>
