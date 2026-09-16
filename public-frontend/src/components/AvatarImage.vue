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
    loading="lazy"
    decoding="async"
    class="mx-auto mb-2 aspect-square shrink-0 rounded-full object-cover object-top"
    :class="size === 'lg' ? 'w-28 border-4 border-brand-navy sm:w-40' : 'w-24'"
    @error="failed = true"
  >
  <div
    v-else
    class="mx-auto mb-2 flex aspect-square shrink-0 items-center justify-center rounded-full bg-gray-200 font-bold text-gray-400"
    :class="size === 'lg' ? 'w-28 border-4 border-brand-navy text-3xl sm:w-40 sm:text-4xl' : 'w-24 text-lg'"
  >
    {{ alt.trim().charAt(0).toUpperCase() }}
  </div>
</template>
