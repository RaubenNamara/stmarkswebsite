<script setup lang="ts">
import { ref, computed } from 'vue'
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

const { data: recentRes } = await api.get('/news', { params: { limit: 9 } })
const moreNews = ((recentRes.data.news as Array<Record<string, any>>) ?? [])
  .filter((item) => item.slug !== props.slug)
  .slice(0, 8)

useHead({ title: news ? news.title : 'Page not found' })

// The image file is missing from the server for some older items (pre-existing, unrelated to
// this page) - hide it on load failure instead of showing a broken-image icon.
const imageFailed = ref(false)
const sidebarImageFailed = ref<Record<number, boolean>>({})

function formatDate(value?: string | null): string {
  if (!value) return ''
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return ''
  return d.toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
}

function readingTime(html: string): string {
  const words = html.replace(/<[^>]*>/g, ' ').trim().split(/\s+/).filter(Boolean).length
  return `${Math.max(1, Math.round(words / 200))} min read`
}

// Some articles' rich content repeats the title as its own leading <h1>, duplicating the
// heading already shown in the hero above - strip it if present.
const cleanedContent = computed(() => (news ? (news.content as string).replace(/^\s*<h1[^>]*>[\s\S]*?<\/h1>\s*/i, '') : ''))
</script>

<template>
  <NotFound v-if="!news" message="This news item doesn't exist." />
  <article v-else>
    <div class="grid md:grid-cols-2">
      <div class="aspect-video w-full overflow-hidden bg-gray-100 md:aspect-auto">
        <img
          v-if="news.image_url && !imageFailed"
          :src="news.image_url"
          :alt="news.title"
          class="h-full w-full object-cover"
          @error="imageFailed = true"
        >
        <div v-else class="flex h-full min-h-[280px] items-center justify-center bg-gradient-to-br from-brand-navy to-brand-navy-dark">
          <span class="font-display text-2xl font-extrabold text-white/20">St Mark's College</span>
        </div>
      </div>

      <div class="flex flex-col justify-center bg-brand-navy-dark px-8 py-12 sm:px-12 sm:py-16 md:px-16">
        <h1 class="font-display text-2xl font-extrabold leading-tight text-white sm:text-3xl md:text-4xl">{{ news.title }}</h1>
        <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-blue-100">
          <span v-if="news.published_at || news.created_at" class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v11a2 2 0 002 2z" /></svg>
            {{ formatDate(news.published_at || news.created_at) }}
          </span>
          <span class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            {{ readingTime(news.content) }}
          </span>
        </div>
      </div>
    </div>

    <div class="mx-auto max-w-4xl px-6 py-10 sm:py-14">
      <div
        class="prose prose-slate prose-lg max-w-none prose-headings:font-display prose-headings:text-brand-navy prose-a:font-semibold prose-a:text-brand-navy prose-a:no-underline prose-img:rounded-xl prose-img:shadow-card prose-blockquote:border-brand-gold prose-strong:text-gray-900 hover:prose-a:underline"
        v-html="cleanedContent"
      />

      <div class="mt-10 border-t border-gray-100 pt-6">
        <router-link to="/news" class="group inline-flex items-center gap-2 font-semibold text-brand-navy transition hover:underline">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          Back to News
        </router-link>
      </div>
    </div>

    <section v-if="moreNews.length" class="bg-gray-50 py-14 sm:py-16">
      <div class="container-wide">
        <h2 class="font-display text-2xl font-bold text-gray-900">More News</h2>

        <div class="mt-8 grid gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          <router-link
            v-for="item in moreNews"
            :key="item.id"
            :to="`/news/${item.slug}`"
            class="card-interactive group flex flex-col overflow-hidden bg-white shadow-card ring-1 ring-black/5"
          >
            <div class="aspect-[4/3] w-full overflow-hidden bg-gray-100">
              <img
                v-if="item.image_url && !sidebarImageFailed[item.id]"
                :src="item.image_url"
                :alt="item.title"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                @error="sidebarImageFailed[item.id] = true"
              >
              <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-brand-navy to-brand-navy-dark">
                <span class="font-display text-xl font-extrabold text-white/20">SM</span>
              </div>
            </div>
            <div class="flex flex-1 flex-col p-6">
              <h3 class="line-clamp-2 font-display text-lg font-semibold text-gray-900 transition group-hover:text-brand-navy">{{ item.title }}</h3>
              <p v-if="item.published_at || item.created_at" class="mt-auto pt-4 text-xs font-medium text-gray-400">{{ formatDate(item.published_at || item.created_at) }}</p>
            </div>
          </router-link>
        </div>

        <router-link to="/news" class="mt-8 inline-block font-semibold text-brand-navy hover:underline">View all news &rarr;</router-link>
      </div>
    </section>
  </article>
</template>
