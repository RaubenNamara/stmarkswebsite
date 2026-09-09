<script setup lang="ts">
import { computed, ref } from 'vue'

const props = withDefaults(
  defineProps<{
    url?: string | null
    image?: string | null
    video?: string | null
    videoLink?: string | null
    title: string
    excerpt?: string | null
    fullText?: string | null
    meta?: string | null
    badge?: string | null
  }>(),
  { url: null, image: null, video: null, videoLink: null, excerpt: null, fullText: null, meta: null, badge: null },
)

// When there's no detail page to link to (no `url`) but the full text is longer than the
// excerpt, let visitors expand the card in place instead of leaving them with a dead-end "...".
const expanded = ref(false)
const canExpand = computed(() => !props.url && !!props.fullText && props.fullText.length > (props.excerpt?.length ?? 0))

// Some content still points at image files missing from the server (pre-existing, unrelated to
// this page) - hide the broken-image icon and fall back to a plain placeholder instead of an
// ugly broken box.
const imageFailed = ref(false)

// Some items have no uploaded image/video file, only an external YouTube link - it may arrive
// via either prop depending on the caller's field name, and a raw <video src> can't play a
// youtube.com page at all. Detect it from whichever prop holds it and show a thumbnail (with a
// play button), embedding the real video in place on click instead of a broken player or a trip
// to youtube.com.
function extractYoutubeId(url: string | null): string | null {
  if (!url) return null
  const match = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([a-zA-Z0-9_-]{11})/)
  return match ? match[1] : null
}
const youtubeId = computed(() => extractYoutubeId(props.videoLink) ?? extractYoutubeId(props.video))
const playingYoutube = ref(false)
</script>

<template>
  <div class="card-interactive group relative flex flex-col overflow-hidden rounded-2xl bg-white p-0 shadow-card ring-1 ring-black/5">
    <div v-if="image && !imageFailed" class="aspect-[4/3] w-full overflow-hidden bg-gray-100">
      <img :src="image" :alt="title" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" @error="imageFailed = true">
    </div>
    <video v-else-if="video && !youtubeId" :src="video" controls class="aspect-[4/3] w-full bg-gray-900 object-cover" />
    <div v-else-if="youtubeId && playingYoutube" class="aspect-[4/3] w-full overflow-hidden bg-black">
      <iframe
        :src="`https://www.youtube.com/embed/${youtubeId}?autoplay=1`"
        :title="title"
        class="h-full w-full"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
        allowfullscreen
      />
    </div>
    <button
      v-else-if="youtubeId"
      type="button"
      class="relative z-10 block aspect-[4/3] w-full overflow-hidden bg-gray-900 text-left"
      @click="playingYoutube = true"
    >
      <img :src="`https://img.youtube.com/vi/${youtubeId}/mqdefault.jpg`" :alt="title" loading="lazy" decoding="async" class="h-full w-full object-cover opacity-80 transition duration-500 group-hover:scale-105 group-hover:opacity-100">
      <span class="absolute inset-0 flex items-center justify-center">
        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-white/90 text-brand-navy shadow-lg transition group-hover:scale-110">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 translate-x-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
        </span>
      </span>
    </button>
    <div v-else class="flex aspect-[4/3] w-full items-center justify-center bg-gradient-to-br from-brand-navy to-brand-navy-dark">
      <span class="font-display text-xl font-extrabold text-white/20">SM</span>
    </div>
    <div class="flex flex-1 flex-col p-6">
      <span v-if="badge" class="mb-2 inline-block w-fit rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">{{ badge }}</span>
      <h3 class="font-display font-semibold text-gray-900">
        <router-link v-if="url" :to="url" class="transition group-hover:text-brand-navy"><span class="absolute inset-0" />{{ title }}</router-link>
        <template v-else>{{ title }}</template>
      </h3>
      <p v-if="excerpt" class="mt-2 whitespace-pre-line text-sm leading-relaxed text-gray-600">{{ expanded ? fullText : excerpt }}</p>
      <button
        v-if="canExpand"
        type="button"
        class="relative z-10 mt-2 w-fit text-sm font-semibold text-brand-navy hover:underline"
        @click="expanded = !expanded"
      >
        {{ expanded ? 'View less' : 'View more' }}
      </button>
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
