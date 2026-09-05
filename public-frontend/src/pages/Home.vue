<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useHead } from '@unhead/vue'
import { api } from '../services/api'
import ListingCard from '../components/ListingCard.vue'

useHead({
  title: null,
  meta: [{ name: 'description', content: "St Mark's College Namagoma - The Higher Achiever's College." }],
})

const { data } = await api.get('/news', { params: { limit: 3 } })
const latestNews = data.data.news as Array<Record<string, any>>

// Real campus photography for the hero, pulled from the actual Gallery content (drone shots of
// the school blocks + day-to-day campus life) rather than stock imagery - same data source the
// /explore/gallery page uses.
const { data: galleryData } = await api.get('/gallery')
const galleryEvents = galleryData.data.events as Array<{ title: string; images: Array<{ image_url: string }> }>
const heroSlides = ['SCHOOL BLOCKS', 'SCHOOL LIFE']
  .flatMap((title) => {
    const event = galleryEvents.find((e) => e.title.toUpperCase() === title)
    if (!event) return []
    return event.images.slice(0, 3).map((img) => ({
      image: img.image_url,
      caption: title.toLowerCase().replace(/\b\w/g, (c) => c.toUpperCase()),
    }))
  })

const activeSlide = ref(0)
let timer: ReturnType<typeof setInterval> | undefined
onMounted(() => {
  if (heroSlides.length > 1) {
    timer = setInterval(() => {
      activeSlide.value = (activeSlide.value + 1) % heroSlides.length
    }, 5000)
  }
})
onUnmounted(() => clearInterval(timer))

const features = [
  {
    title: 'Our Motto',
    text: '"To Be Not To Seem" — reflecting our founders\' desire to train students with strong values that guide them through life.',
    icon: 'M12 2l2.4 6.6H21l-5.4 4.2 2 6.7L12 15.9 6.4 19.5l2-6.7L3 8.6h6.6L12 2z',
  },
  {
    title: 'Core Values — GREET',
    text: 'Godliness, Reliability, Ethics, Excellence, Team Work — five values every learner is formed by.',
    icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
  },
  {
    title: 'Academic Excellence',
    text: 'Stimulating, rewarding and forward-looking programs that build the whole person.',
    icon: 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.42A12.02 12.02 0 0112 21.5a12.02 12.02 0 01-6.16-10.92L12 14z',
  },
]

const greetWords = ['Godliness', 'Reliability', 'Ethics', 'Excellence', 'Team Work']
</script>

<template>
  <section class="relative h-[80vh] min-h-[520px] w-full overflow-hidden bg-brand-navy-dark text-white">
    <template v-if="heroSlides.length">
      <img
        v-for="(slide, i) in heroSlides"
        :key="slide.image"
        :src="slide.image"
        :alt="slide.caption"
        class="absolute inset-0 h-full w-full object-cover transition-opacity duration-1000"
        :class="i === activeSlide ? 'opacity-100' : 'opacity-0'"
      >
      <div class="absolute inset-0 bg-gradient-to-t from-brand-navy-dark via-brand-navy-dark/40 to-brand-navy-dark/10" />
    </template>
    <div v-else class="absolute inset-0 bg-gradient-to-br from-brand-navy via-brand-navy to-brand-navy-dark">
      <div class="pointer-events-none absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, white 1px, transparent 1px); background-size: 28px 28px;" />
    </div>

    <div class="relative flex h-full flex-col items-center justify-center px-6 text-center">
      <span class="eyebrow">The Higher Achiever's College</span>
      <h1 class="mt-4 max-w-3xl font-display text-4xl font-extrabold tracking-tight sm:text-6xl">Welcome to St Mark's College Namagoma</h1>
      <p class="mx-auto mt-5 max-w-2xl text-lg text-blue-100">Building disciplined, confident and competent learners — a community where character and academic excellence grow together.</p>
      <div class="mt-9 flex flex-wrap items-center justify-center gap-4">
        <router-link to="/admissions" class="btn btn-gold">Apply Now</router-link>
        <router-link to="/about" class="btn btn-outline">Learn More</router-link>
      </div>
    </div>

    <div v-if="heroSlides.length" class="absolute bottom-6 left-1/2 z-10 -translate-x-1/2">
      <p class="mb-3 text-center text-sm font-semibold text-white/90">{{ heroSlides[activeSlide].caption }}</p>
      <div class="flex items-center justify-center gap-2">
        <button
          v-for="(slide, i) in heroSlides"
          :key="slide.image"
          type="button"
          class="h-1.5 rounded-full transition-all"
          :class="i === activeSlide ? 'w-8 bg-brand-gold' : 'w-3 bg-white/40 hover:bg-white/60'"
          :aria-label="`Show slide ${i + 1}`"
          @click="activeSlide = i"
        />
      </div>
    </div>
  </section>

  <section class="bg-white px-6 py-6">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-center gap-3">
      <span v-for="word in greetWords" :key="word" class="rounded-full border border-brand-navy/15 bg-brand-navy/5 px-4 py-1.5 text-xs font-bold uppercase tracking-wide text-brand-navy">{{ word }}</span>
    </div>
  </section>

  <section class="mx-auto max-w-6xl px-6 py-20">
    <div class="mx-auto max-w-2xl text-center">
      <span class="eyebrow text-brand-navy">Why St Mark's</span>
      <h2 class="mt-3 font-display text-3xl font-bold text-gray-900">A foundation for life, not just for exams</h2>
    </div>

    <div class="mt-12 grid gap-6 sm:grid-cols-3">
      <div v-for="feature in features" :key="feature.title" class="card-interactive card">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-navy/5 text-brand-navy">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" :d="feature.icon" />
          </svg>
        </div>
        <h3 class="mt-4 font-display text-lg font-semibold text-gray-900">{{ feature.title }}</h3>
        <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ feature.text }}</p>
      </div>
    </div>
  </section>

  <section v-if="latestNews.length" class="bg-gray-50 px-6 py-20">
    <div class="mx-auto max-w-6xl">
      <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
          <span class="eyebrow text-brand-navy">Stay Informed</span>
          <h2 class="mt-3 font-display text-3xl font-bold text-gray-900">Latest News</h2>
        </div>
        <router-link to="/news" class="font-semibold text-brand-navy hover:underline">View all news &rarr;</router-link>
      </div>
      <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <ListingCard
          v-for="item in latestNews"
          :key="item.id"
          :url="`/news/${item.slug}`"
          :image="item.image_url"
          :title="item.title"
          :excerpt="item.excerpt"
        />
      </div>
    </div>
  </section>

  <section class="relative overflow-hidden bg-brand-navy-dark px-6 py-16 text-center">
    <div class="mx-auto max-w-2xl">
      <h2 class="font-display text-2xl font-bold text-white sm:text-3xl">Ready to begin the journey?</h2>
      <p class="mx-auto mt-3 max-w-lg text-blue-100">Join a community that nurtures discipline, confidence and competence in every learner.</p>
      <router-link to="/admissions" class="btn btn-gold mt-7">Start Your Application</router-link>
    </div>
  </section>
</template>
