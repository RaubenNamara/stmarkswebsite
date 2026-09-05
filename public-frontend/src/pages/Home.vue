<script setup lang="ts">
import { useHead } from '@unhead/vue'
import { api } from '../services/api'
import ListingCard from '../components/ListingCard.vue'

useHead({
  title: null,
  meta: [{ name: 'description', content: "St Mark's College Namagoma - The Higher Achiever's College." }],
})

const { data } = await api.get('/news', { params: { limit: 3 } })
const latestNews = data.data.news as Array<Record<string, any>>

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
  <section class="relative overflow-hidden bg-gradient-to-br from-brand-navy via-brand-navy to-brand-navy-dark px-6 py-24 text-center text-white sm:py-32">
    <div class="pointer-events-none absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, white 1px, transparent 1px); background-size: 28px 28px;" />
    <div class="pointer-events-none absolute -right-32 -top-32 h-96 w-96 rounded-full bg-brand-gold/10 blur-3xl" />
    <div class="pointer-events-none absolute -bottom-32 -left-32 h-96 w-96 rounded-full bg-white/5 blur-3xl" />

    <div class="relative mx-auto max-w-3xl">
      <span class="eyebrow">The Higher Achiever's College</span>
      <h1 class="mt-4 font-display text-4xl font-extrabold tracking-tight sm:text-6xl">Welcome to St Mark's College Namagoma</h1>
      <p class="mx-auto mt-5 max-w-2xl text-lg text-blue-100">Building disciplined, confident and competent learners since our founding — a community where character and academic excellence grow together.</p>
      <div class="mt-9 flex flex-wrap items-center justify-center gap-4">
        <router-link to="/admissions" class="btn btn-gold">Apply Now</router-link>
        <router-link to="/about" class="btn btn-outline">Learn More</router-link>
      </div>

      <div class="mt-14 flex flex-wrap items-center justify-center gap-2.5">
        <span v-for="word in greetWords" :key="word" class="rounded-full border border-white/20 bg-white/5 px-4 py-1.5 text-xs font-semibold uppercase tracking-wide text-blue-100">{{ word }}</span>
      </div>
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
