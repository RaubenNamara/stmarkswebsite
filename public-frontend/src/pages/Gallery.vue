<script setup lang="ts">
import { ref } from 'vue'
import { useHead } from '@unhead/vue'
import { api } from '../services/api'
import PageHeader from '../components/PageHeader.vue'
import EmptyState from '../components/EmptyState.vue'

useHead({ title: 'Gallery' })

const { data } = await api.get('/gallery')
const events = data.data.events as Array<{ id: number; title: string; images: Array<{ id: number; image_url: string }> }>

// Some photos are missing from the server (pre-existing, unrelated to this page) - drop them
// from the grid on load failure instead of showing a broken-image icon.
const failedIds = ref(new Set<number>())
function onImageError(id: number) {
  failedIds.value = new Set(failedIds.value).add(id)
}
</script>

<template>
  <PageHeader title="Gallery" />

  <section class="container-wide py-14">
    <EmptyState v-if="!events.length" message="No gallery events yet." />
    <div v-else class="space-y-8">
      <div v-for="event in events" :key="event.id" class="card">
        <h2 class="text-lg font-semibold text-gray-900">{{ event.title }}</h2>
        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6">
          <img
            v-for="img in event.images.filter((i) => !failedIds.has(i.id)).slice(0, 12)"
            :key="img.id"
            :src="img.image_url"
            alt=""
            class="h-36 w-full rounded-lg bg-gray-100 object-cover"
            @error="onImageError(img.id)"
          >
        </div>
        <EmptyState v-if="event.images.every((i) => failedIds.has(i.id))" message="Photos for this event are unavailable." />
        <p v-else-if="event.images.length > 12" class="mt-3 text-sm text-gray-500">+{{ event.images.length - 12 }} more photos</p>
      </div>
    </div>
  </section>
</template>
