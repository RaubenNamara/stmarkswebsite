<script setup lang="ts">
import { ref } from 'vue'

withDefaults(
  defineProps<{
    url?: string | null
    image?: string | null
    video?: string | null
    title: string
    excerpt?: string | null
    meta?: string | null
    badge?: string | null
  }>(),
  { url: null, image: null, video: null, excerpt: null, meta: null, badge: null },
)

// Some content still points at image files missing from the server (pre-existing, unrelated to
// this page) - hide the broken-image icon and fall back to a plain placeholder instead of an
// ugly broken box.
const imageFailed = ref(false)
</script>

<template>
  <div class="card-interactive group relative flex flex-col overflow-hidden rounded-2xl bg-white p-0 shadow-card ring-1 ring-black/5">
    <div v-if="image && !imageFailed" class="h-48 w-full overflow-hidden bg-gray-100">
      <img :src="image" :alt="title" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" @error="imageFailed = true">
    </div>
    <video v-else-if="video" :src="video" controls class="h-48 w-full bg-gray-900 object-cover" />
    <div v-else class="flex h-48 w-full items-center justify-center bg-gray-50">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h.01M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z" />
      </svg>
    </div>
    <div class="flex flex-1 flex-col p-6">
      <span v-if="badge" class="mb-2 inline-block w-fit rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">{{ badge }}</span>
      <h3 class="font-display font-semibold text-gray-900">
        <router-link v-if="url" :to="url" class="transition group-hover:text-brand-navy"><span class="absolute inset-0" />{{ title }}</router-link>
        <template v-else>{{ title }}</template>
      </h3>
      <p v-if="excerpt" class="mt-2 text-sm leading-relaxed text-gray-600">{{ excerpt }}</p>
      <div v-if="meta" class="mt-auto pt-4 text-xs font-medium text-gray-400">{{ meta }}</div>
      <div v-if="url" class="mt-4 flex items-center gap-1 text-sm font-semibold text-brand-navy opacity-0 transition group-hover:opacity-100">
        Read more
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
        </svg>
      </div>
    </div>
  </div>
</template>
