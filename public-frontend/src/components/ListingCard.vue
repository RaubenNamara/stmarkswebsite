<script setup lang="ts">
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
</script>

<template>
  <div class="group relative flex flex-col overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-black/5 transition hover:shadow-md">
    <img v-if="image" :src="image" :alt="title" class="h-44 w-full bg-gray-100 object-cover">
    <video v-else-if="video" :src="video" controls class="h-44 w-full bg-gray-900 object-cover" />
    <div class="flex flex-1 flex-col p-5">
      <span v-if="badge" class="mb-2 inline-block w-fit rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">{{ badge }}</span>
      <h3 class="font-semibold text-gray-900">
        <router-link v-if="url" :to="url" class="hover:text-brand-navy"><span class="absolute inset-0" />{{ title }}</router-link>
        <template v-else>{{ title }}</template>
      </h3>
      <p v-if="excerpt" class="mt-1.5 text-sm text-gray-600">{{ excerpt }}</p>
      <div v-if="meta" class="mt-auto pt-3 text-xs text-gray-400">{{ meta }}</div>
    </div>
  </div>
</template>
