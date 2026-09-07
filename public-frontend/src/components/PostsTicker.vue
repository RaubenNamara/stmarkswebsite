<script setup lang="ts">
import { api } from '../services/api'

let latest: { title: string; url: string } | null = null
try {
  const { data } = await api.get('/news', { params: { limit: 1 } })
  const item = data.data.news[0]
  if (item) {
    latest = { title: item.title, url: `/news/${item.slug}` }
  }
} catch {
  latest = null
}
</script>

<template>
  <div v-if="latest" class="overflow-hidden bg-brand-navy-dark">
    <router-link :to="latest.url" class="container-wide flex items-center gap-3 py-2.5 text-sm text-white transition hover:bg-white/5">
      <span class="h-2 w-2 shrink-0 animate-pulse rounded-full bg-brand-gold" />
      <span class="shrink-0 font-bold text-brand-gold">Latest News</span>
      <span class="truncate text-blue-100">{{ latest.title }}</span>
    </router-link>
  </div>
</template>
