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
</script>

<template>
  <section class="bg-gradient-to-b from-brand-navy to-brand-navy-dark px-6 py-20 text-center text-white sm:py-28">
    <h1 class="mx-auto max-w-3xl text-4xl font-bold tracking-tight sm:text-5xl">Welcome to St Mark's College Namagoma</h1>
    <p class="mx-auto mt-4 max-w-2xl text-lg text-blue-100">The Higher Achiever's College — building disciplined, confident and competent learners since our founding.</p>
    <router-link to="/admissions" class="btn btn-gold mt-8">Apply Now</router-link>
  </section>

  <section class="mx-auto max-w-6xl px-6 py-14 sm:py-16">
    <div class="grid gap-6 sm:grid-cols-3">
      <div class="card border-t-4 border-brand-navy">
        <h2 class="text-lg font-semibold">Our Motto</h2>
        <p class="mt-2 text-sm text-gray-600">"To Be Not To Seem" — reflecting our founders' desire to train students with strong values that guide them through life.</p>
      </div>
      <div class="card border-t-4 border-brand-gold">
        <h2 class="text-lg font-semibold">Core Values — GREET</h2>
        <p class="mt-2 text-sm text-gray-600">Godliness, Reliability, Ethics, Excellence, Team Work.</p>
      </div>
      <div class="card border-t-4 border-brand-navy">
        <h2 class="text-lg font-semibold">Academic Excellence</h2>
        <p class="mt-2 text-sm text-gray-600">Stimulating, rewarding and forward-looking programs that build the whole person.</p>
      </div>
    </div>
  </section>

  <section v-if="latestNews.length" class="mx-auto max-w-6xl px-6 pb-16">
    <h2 class="text-2xl font-bold text-gray-900">Latest News</h2>
    <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <ListingCard
        v-for="item in latestNews"
        :key="item.id"
        :url="`/news/${item.slug}`"
        :image="item.image_url"
        :title="item.title"
        :excerpt="item.excerpt"
      />
    </div>
    <p class="mt-6"><router-link to="/news" class="font-semibold text-brand-navy hover:underline">View all news &rarr;</router-link></p>
  </section>
</template>
