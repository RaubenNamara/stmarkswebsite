<script setup lang="ts">
import { ref } from 'vue'

withDefaults(defineProps<{ src?: string | null; alt: string; size?: 'sm' | 'lg' }>(), { size: 'sm' })
const failed = ref(false)
</script>

<template>
  <img
    v-if="src && !failed"
    :src="src"
    :alt="alt"
    class="mx-auto mb-2 rounded-full object-cover"
    :class="size === 'lg' ? 'h-40 w-40 border-4 border-brand-navy' : 'h-24 w-24'"
    @error="failed = true"
  >
  <div
    v-else
    class="mx-auto mb-2 flex items-center justify-center rounded-full bg-gray-200 font-bold text-gray-400"
    :class="size === 'lg' ? 'h-40 w-40 border-4 border-brand-navy text-4xl' : 'h-24 w-24 text-lg'"
  >
    {{ alt.trim().charAt(0).toUpperCase() }}
  </div>
</template>
