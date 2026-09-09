<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useHead } from '@unhead/vue'
import { api } from '../services/api'
import PageHeader from '../components/PageHeader.vue'
import EmptyState from '../components/EmptyState.vue'
import { staticAsset } from '../utils/staticAsset'

useHead({ title: 'Gallery' })

type GalleryImage = { id: number; image_url: string }
type GalleryEvent = { id: number; title: string; images: GalleryImage[] }

const { data } = await api.get('/gallery')
const events = data.data.events as GalleryEvent[]

// Some photos are missing from the server (pre-existing, unrelated to this page) - drop them
// from the grid on load failure instead of showing a broken-image icon.
const failedIds = ref(new Set<number>())
function onImageError(id: number) {
  failedIds.value = new Set(failedIds.value).add(id)
}
function visibleImages(event: GalleryEvent) {
  return event.images.filter((i) => !failedIds.value.has(i.id))
}

// Clicking a photo enlarges it (and lets you step through the rest of that event's photos) -
// every photo is already visible on the page, this is just a closer look, not a reveal.
const lightboxEventId = ref<number | null>(null)
const lightboxIndex = ref(0)
const lightboxImages = computed(() => {
  const event = events.find((e) => e.id === lightboxEventId.value)
  return event ? visibleImages(event) : []
})
function openLightbox(event: GalleryEvent, index: number) {
  lightboxEventId.value = event.id
  lightboxIndex.value = index
}
function closeLightbox() {
  lightboxEventId.value = null
}
function nextImage() {
  if (!lightboxImages.value.length) return
  lightboxIndex.value = (lightboxIndex.value + 1) % lightboxImages.value.length
}
function prevImage() {
  if (!lightboxImages.value.length) return
  lightboxIndex.value = (lightboxIndex.value - 1 + lightboxImages.value.length) % lightboxImages.value.length
}
function onKeydown(e: KeyboardEvent) {
  if (lightboxEventId.value === null) return
  if (e.key === 'Escape') closeLightbox()
  else if (e.key === 'ArrowRight') nextImage()
  else if (e.key === 'ArrowLeft') prevImage()
}
onMounted(() => window.addEventListener('keydown', onKeydown))
onUnmounted(() => window.removeEventListener('keydown', onKeydown))
</script>

<template>
  <PageHeader title="Gallery" subtitle="A look back at life, events and milestones at St Mark's College Namagoma." :bg-image="staticAsset('storage/images/smacon.jpg')" />

  <section class="container-wide space-y-14 py-14">
    <EmptyState v-if="!events.length" message="No gallery events yet." />

    <div v-for="event in events" v-else :key="event.id">
      <div v-reveal class="flex flex-wrap items-end justify-between gap-3">
        <h2 class="font-display text-xl font-bold text-brand-navy sm:text-2xl">{{ event.title }}</h2>
        <span class="text-sm font-medium text-gray-500">{{ visibleImages(event).length }} photos</span>
      </div>

      <div v-if="visibleImages(event).length" class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6">
        <button
          v-for="(img, index) in visibleImages(event)"
          :key="img.id"
          type="button"
          class="group aspect-square overflow-hidden rounded-lg bg-gray-100"
          @click="openLightbox(event, index)"
        >
          <img :src="img.image_url" alt="" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-300 group-hover:scale-110" @error="onImageError(img.id)">
        </button>
      </div>
      <EmptyState v-else message="Photos for this event are unavailable." />
    </div>
  </section>

  <!-- Lightbox -->
  <div v-if="lightboxEventId !== null && lightboxImages[lightboxIndex]" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/90 p-4" @click.self="closeLightbox">
    <button type="button" aria-label="Close" class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20" @click="closeLightbox">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
    </button>

    <button v-if="lightboxImages.length > 1" type="button" aria-label="Previous photo" class="absolute left-2 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20 sm:left-4" @click.stop="prevImage">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
    </button>
    <button v-if="lightboxImages.length > 1" type="button" aria-label="Next photo" class="absolute right-2 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20 sm:right-4" @click.stop="nextImage">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
    </button>

    <img :src="lightboxImages[lightboxIndex].image_url" alt="" class="max-h-[85vh] max-w-full rounded-lg object-contain shadow-2xl">

    <span v-if="lightboxImages.length > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full bg-black/60 px-3 py-1 text-xs font-semibold text-white backdrop-blur-sm">
      {{ lightboxIndex + 1 }} / {{ lightboxImages.length }}
    </span>
  </div>
</template>
