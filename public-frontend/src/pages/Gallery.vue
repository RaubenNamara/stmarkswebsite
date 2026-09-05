<script setup lang="ts">
import { useHead } from '@unhead/vue'
import { api } from '../services/api'
import PageHeader from '../components/PageHeader.vue'
import EmptyState from '../components/EmptyState.vue'

useHead({ title: 'Gallery' })

const { data } = await api.get('/gallery')
const events = data.data.events as Array<{ id: number; title: string; images: Array<{ id: number; image_url: string }> }>
</script>

<template>
  <PageHeader title="Gallery" />

  <section class="mx-auto max-w-6xl px-6 py-14">
    <EmptyState v-if="!events.length" message="No gallery events yet." />
    <div v-else class="space-y-8">
      <div v-for="event in events" :key="event.id" class="card">
        <h2 class="text-lg font-semibold text-gray-900">{{ event.title }}</h2>
        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
          <img v-for="img in event.images.slice(0, 12)" :key="img.id" :src="img.image_url" alt="" class="h-36 w-full rounded-lg object-cover">
        </div>
        <p v-if="event.images.length > 12" class="mt-3 text-sm text-gray-500">+{{ event.images.length - 12 }} more photos</p>
      </div>
    </div>
  </section>
</template>
