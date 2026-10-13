<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useHead } from '@unhead/vue'
import { api } from '../../services/api'
import NotFound from '../errors/NotFound.vue'
import { staticAsset } from '../../utils/staticAsset'
import { toArticleHtml } from '../../utils/articleHtml'

const props = defineProps<{ slug: string }>()

let club: Record<string, any> | null = null
let others: Array<Record<string, any>> = []
try {
  // the club and the list of other clubs (for the "More clubs" sidebar) load together
  const [one, list] = await Promise.all([
    api.get(`/clubs/${props.slug}`),
    api.get('/clubs').catch(() => ({ data: { data: { clubs: [] } } })),
  ])
  club = one.data.data.club
  others = (list.data.data.clubs as Array<Record<string, any>>).filter((c) => c.slug !== props.slug).slice(0, 5)
} catch {
  club = null
}

useHead({
  title: club ? club.title : 'Page not found',
  meta: club?.content ? [{ name: 'description', content: String(club.content).replace(/\s+/g, ' ').slice(0, 200) }] : [],
})

const body = computed(() => (club ? toArticleHtml(club.content) : ''))

// Some photos are missing from the server (pre-existing, unrelated to this page) - drop them from
// the page on load failure instead of showing a broken-image icon.
const failed = ref(new Set<number>())
function onImageError(id: number) {
  failed.value = new Set(failed.value).add(id)
}
const photos = computed(() => ((club?.images ?? []) as Array<{ id: number; image_url: string }>).filter((i) => !failed.value.has(i.id)))
const heroPhoto = computed(() => photos.value[0] ?? null)
const otherFailed = ref<Record<number, boolean>>({})

// Columns follow the number of photos so the last row is never a lonely stray photo
// (written out in full so Tailwind can see every class).
const galleryColumns = computed(() => {
  switch (photos.value.length) {
    case 1: return 'grid-cols-1 sm:max-w-2xl'
    case 2: return 'grid-cols-2'
    case 3: return 'grid-cols-1 sm:grid-cols-3'
    case 4: return 'grid-cols-2 lg:grid-cols-4'
    case 5: return 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-5'
    case 6: return 'grid-cols-2 sm:grid-cols-3'
    default: return 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4'
  }
})

// ---- photo viewer (click a photo to enlarge it; arrows / swipe / Esc) ----
const viewer = ref<number | null>(null)
const closeBtn = ref<HTMLButtonElement | null>(null)
let touchX = 0
function openViewer(i: number) {
  viewer.value = i
}
function closeViewer() {
  viewer.value = null
}
function step(d: number) {
  if (viewer.value === null || !photos.value.length) return
  viewer.value = (viewer.value + d + photos.value.length) % photos.value.length
}
function onKey(e: KeyboardEvent) {
  if (viewer.value === null) return
  if (e.key === 'Escape') closeViewer()
  else if (e.key === 'ArrowRight') step(1)
  else if (e.key === 'ArrowLeft') step(-1)
}
function onTouchEnd(e: TouchEvent) {
  const dx = (e.changedTouches[0]?.clientX ?? touchX) - touchX
  if (Math.abs(dx) > 50) step(dx < 0 ? 1 : -1)
}
watch(viewer, async (v) => {
  document.documentElement.style.overflow = v === null ? '' : 'hidden'
  if (v !== null) {
    await nextTick()
    closeBtn.value?.focus()
  }
})
onMounted(() => window.addEventListener('keydown', onKey))
onBeforeUnmount(() => {
  window.removeEventListener('keydown', onKey)
  document.documentElement.style.overflow = ''
})
</script>

<template>
  <NotFound v-if="!club" message="This club doesn't exist." />

  <template v-else>
    <!-- ===== Hero: title beside the club's first photo (stacked on phones and tablets) ===== -->
    <header
      class="relative overflow-hidden bg-brand-navy-dark bg-cover bg-center text-white"
      :style="{ backgroundImage: `url('${staticAsset('storage/images/smacon.jpg')}')` }"
    >
      <!-- the school building shows through a navy wash that is darker where the text sits (left) -->
      <div class="pointer-events-none absolute inset-0 bg-gradient-to-r from-brand-navy-dark/95 via-brand-navy-dark/85 to-brand-navy/70" />
      <div class="pointer-events-none absolute -right-32 -top-32 h-96 w-96 rounded-full bg-brand-gold/10 blur-3xl" />
      <div class="pointer-events-none absolute -bottom-32 -left-32 h-96 w-96 rounded-full bg-white/5 blur-3xl" />

      <div
        class="container-wide relative grid items-center gap-8 py-10 sm:py-14 lg:py-16 min-[1920px]:gap-24 min-[1920px]:py-20 min-[2400px]:py-24"
        :class="heroPhoto ? 'lg:grid-cols-[1.15fr_0.85fr] lg:gap-14 min-[1920px]:grid-cols-[1.35fr_1fr]' : ''"
      >
        <div>
          <router-link to="/clubs" class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-sm font-semibold backdrop-blur transition hover:bg-white/20" style="color: inherit">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
            Clubs
          </router-link>

          <div class="mt-6 h-1 w-16 rounded-full bg-brand-gold" />
          <h1 class="mt-5 text-4xl font-extrabold leading-[1.1] tracking-tight text-white sm:text-5xl lg:text-6xl min-[1920px]:text-7xl min-[2400px]:text-8xl">{{ club.title }}</h1>

          <div class="mt-7 flex flex-wrap items-center gap-3 text-blue-100 min-[1920px]:mt-10 min-[1920px]:text-lg">
            <span class="rounded-full bg-brand-gold px-4 py-1.5 text-sm font-bold text-brand-navy-dark">School club</span>
            <span v-if="photos.length" class="flex items-center gap-1.5"><span class="h-1 w-1 rounded-full bg-brand-gold" />{{ photos.length }} {{ photos.length === 1 ? 'photo' : 'photos' }}</span>
          </div>
        </div>

        <div v-if="heroPhoto" class="mx-auto w-full max-w-2xl lg:max-w-none">
          <button type="button" class="group block w-full overflow-hidden rounded-3xl shadow-2xl ring-1 ring-white/20" :aria-label="`Enlarge photo of ${club.title}`" @click="openViewer(0)">
            <img :src="heroPhoto.image_url" :alt="club.title" class="aspect-[4/3] w-full object-cover transition duration-700 group-hover:scale-105 min-[1920px]:aspect-[16/11]" @error="onImageError(heroPhoto.id)">
          </button>
        </div>
      </div>
    </header>

    <!-- ===== Body: the story + sticky sidebar on wide screens, then the photo gallery ===== -->
    <div class="container-wide py-10 sm:py-14 min-[1920px]:py-20">
      <div class="grid gap-12 xl:grid-cols-[minmax(0,1fr)_21rem] min-[1920px]:grid-cols-[minmax(0,1fr)_28rem] min-[1920px]:gap-24">
        <article class="mx-auto w-full max-w-[46rem] min-[1536px]:max-w-[52rem] min-[1920px]:max-w-[64rem] min-[2400px]:max-w-[78rem]">
          <!-- text grows with the screen: 18px on laptops, 20px on 24", 24px on 27"+ monitors -->
          <div
            class="prose prose-lg max-w-none min-[1920px]:prose-xl min-[2400px]:prose-2xl prose-slate prose-headings:font-display prose-headings:font-bold prose-p:leading-[1.85] prose-p:text-gray-800"
            v-html="body"
          />
        </article>

        <aside v-if="others.length" class="xl:sticky xl:top-28 xl:self-start">
          <p class="text-sm font-bold uppercase tracking-widest text-brand-gold min-[1920px]:text-base">More clubs</p>
          <ul class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-1 min-[1920px]:gap-6">
            <li v-for="c in others" :key="c.id">
              <router-link :to="`/clubs/${c.slug}`" class="group flex items-center gap-4 rounded-2xl bg-white p-3 shadow-card ring-1 ring-black/5 transition duration-300 hover:-translate-y-1 hover:shadow-card-hover min-[1920px]:gap-5 min-[1920px]:p-4">
                <span class="h-16 w-16 shrink-0 overflow-hidden rounded-xl bg-gray-100 min-[1920px]:h-24 min-[1920px]:w-24">
                  <img v-if="c.images?.[0]?.image_url && !otherFailed[c.id]" :src="c.images[0].image_url" :alt="c.title" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-500 group-hover:scale-110" @error="otherFailed[c.id] = true">
                  <span v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-brand-navy to-brand-navy-dark font-display text-lg font-extrabold text-white/30">SM</span>
                </span>
                <span class="line-clamp-2 min-w-0 font-display font-bold leading-snug text-gray-900 transition-colors group-hover:text-brand-navy min-[1920px]:text-lg">{{ c.title }}</span>
              </router-link>
            </li>
          </ul>
        </aside>
      </div>

      <!-- photo gallery -->
      <section v-if="photos.length" class="mt-16 min-[1920px]:mt-24">
        <div class="flex items-end justify-between gap-4">
          <div>
            <p class="text-sm font-bold uppercase tracking-widest text-brand-gold min-[1920px]:text-base">Gallery</p>
            <h2 class="mt-1 text-2xl font-bold text-gray-900 sm:text-3xl min-[1920px]:text-4xl">Moments from {{ club.title }}</h2>
          </div>
          <p class="hidden text-sm text-gray-500 sm:block">Click a photo to enlarge</p>
        </div>
        <div class="mt-8 grid gap-3 sm:gap-4 min-[1920px]:gap-6" :class="galleryColumns">
          <button
            v-for="(img, i) in photos"
            :key="img.id"
            type="button"
            class="gallery-tile group relative aspect-[4/3] overflow-hidden rounded-2xl bg-gray-100 shadow-card ring-1 ring-black/5 transition duration-300 hover:-translate-y-1 hover:shadow-card-hover focus-visible:outline focus-visible:outline-4 focus-visible:outline-offset-2 focus-visible:outline-brand-gold"
            :aria-label="`Enlarge photo ${i + 1} of ${photos.length}`"
            @click="openViewer(i)"
          >
            <img :src="img.image_url" :alt="`${club.title} photo ${i + 1}`" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-700 group-hover:scale-110" @error="onImageError(img.id)">
            <span class="pointer-events-none absolute inset-0 bg-gradient-to-t from-brand-navy-dark/50 via-transparent to-transparent opacity-0 transition duration-300 group-hover:opacity-100" />
            <span class="pointer-events-none absolute bottom-3 right-3 flex h-9 w-9 scale-75 items-center justify-center rounded-full bg-white/90 text-brand-navy opacity-0 shadow transition duration-300 group-hover:scale-100 group-hover:opacity-100">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4h4M20 8V4h-4M4 16v4h4M20 16v4h-4" /></svg>
            </span>
          </button>
        </div>
      </section>

      <div class="mt-14 border-t border-gray-200 pt-8">
        <router-link to="/clubs" class="btn btn-gold">&larr; Back to Clubs</router-link>
      </div>
    </div>

    <!-- ===== Photo viewer ===== -->
    <div
      v-if="viewer !== null && photos[viewer]"
      class="fixed inset-0 z-[60] flex items-center justify-center bg-black/90 p-4 sm:p-8"
      role="dialog"
      aria-modal="true"
      :aria-label="`${club.title} photo ${viewer + 1} of ${photos.length}`"
      @click.self="closeViewer"
      @touchstart.passive="touchX = $event.changedTouches[0]?.clientX ?? 0"
      @touchend="onTouchEnd"
    >
      <button ref="closeBtn" type="button" class="absolute right-4 top-4 flex h-12 w-12 items-center justify-center rounded-full bg-white/15 text-2xl text-white transition hover:bg-white/30 focus-visible:outline focus-visible:outline-4 focus-visible:outline-brand-gold" aria-label="Close photo" @click="closeViewer">&times;</button>
      <button v-if="photos.length > 1" type="button" class="absolute left-3 top-1/2 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white/15 text-white transition hover:bg-white/30 sm:left-6" aria-label="Previous photo" @click="step(-1)">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
      </button>
      <img :src="photos[viewer].image_url" :alt="`${club.title} photo ${viewer + 1}`" class="max-h-full max-w-full rounded-xl object-contain shadow-2xl">
      <button v-if="photos.length > 1" type="button" class="absolute right-3 top-1/2 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white/15 text-white transition hover:bg-white/30 sm:right-6" aria-label="Next photo" @click="step(1)">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
      </button>
      <p class="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full bg-white/15 px-4 py-1.5 text-sm font-semibold text-white">{{ viewer + 1 }} / {{ photos.length }}</p>
    </div>
  </template>
</template>
