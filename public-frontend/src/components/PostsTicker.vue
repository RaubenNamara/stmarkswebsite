<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import { api } from '../services/api'

type NewsItem = { title: string; url: string }

let items: NewsItem[] = []
try {
  const { data } = await api.get('/news', { params: { limit: 5 } })
  items = (data.data.news as Array<{ title: string; slug: string }>).map((n) => ({ title: n.title, url: `/news/${n.slug}` }))
} catch {
  items = []
}

const index = ref(0)
let timer: ReturnType<typeof setInterval> | undefined

onMounted(() => {
  if (items.length > 1) {
    timer = setInterval(() => {
      index.value = (index.value + 1) % items.length
    }, 6000)
  }
})
onUnmounted(() => {
  if (timer) clearInterval(timer)
})
</script>

<template>
  <div v-if="items.length" class="overflow-hidden bg-brand-navy-dark">
    <Transition name="ticker-fade" mode="out-in">
      <router-link :key="index" :to="items[index].url" class="container-wide flex items-center gap-3 py-2.5 text-sm text-white transition hover:bg-white/5">
        <span class="h-2 w-2 shrink-0 animate-pulse rounded-full bg-brand-gold" />
        <span class="shrink-0 font-bold text-brand-gold">Latest News</span>
        <span class="truncate text-blue-100">{{ items[index].title }}</span>
      </router-link>
    </Transition>
  </div>
</template>

<style scoped>
.ticker-fade-enter-active,
.ticker-fade-leave-active {
  transition: opacity 0.4s ease, transform 0.4s ease;
}
.ticker-fade-enter-from {
  opacity: 0;
  transform: translateY(4px);
}
.ticker-fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
@media (prefers-reduced-motion: reduce) {
  .ticker-fade-enter-active,
  .ticker-fade-leave-active {
    transition: none;
  }
}
</style>
