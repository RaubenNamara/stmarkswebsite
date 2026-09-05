<script setup lang="ts">
import { useHead } from '@unhead/vue'
import { api } from '../../services/api'
import NotFound from '../errors/NotFound.vue'

const props = defineProps<{ slug: string }>()

let news: Record<string, any> | null = null
try {
  const { data } = await api.get(`/news/${props.slug}`)
  news = data.data.news
} catch {
  news = null
}

useHead({ title: news ? news.title : 'Page not found' })
</script>

<template>
  <NotFound v-if="!news" message="This news item doesn't exist." />
  <article v-else class="mx-auto max-w-3xl px-6 py-14">
    <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ news.title }}</h1>
    <img v-if="news.image_url" :src="news.image_url" :alt="news.title" class="mt-6 w-full rounded-xl object-cover">
    <div class="prose prose-slate mt-6 max-w-none" v-html="news.content" />
    <p class="mt-8"><router-link to="/news" class="font-semibold text-brand-navy hover:underline">&larr; Back to News</router-link></p>
  </article>
</template>
