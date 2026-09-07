<script setup lang="ts">
import { ref, computed } from 'vue'
import { useHead } from '@unhead/vue'
import { api } from '../../services/api'
import NotFound from '../errors/NotFound.vue'
import PageHeader from '../../components/PageHeader.vue'

const props = defineProps<{ slug: string }>()

let news: Record<string, any> | null = null
try {
  const { data } = await api.get(`/news/${props.slug}`)
  news = data.data.news
} catch {
  news = null
}

const { data: recentRes } = await api.get('/news', { params: { limit: 7 } })
const moreNews = ((recentRes.data.news as Array<Record<string, any>>) ?? [])
  .filter((item) => item.slug !== props.slug)
  .slice(0, 6)

useHead({ title: news ? news.title : 'Page not found' })

// The image file is missing from the server for some older items (pre-existing, unrelated to
// this page) - hide it on load failure instead of showing a broken-image icon.
const imageFailed = ref(false)
const sidebarImageFailed = ref<Record<number, boolean>>({})

function formatDate(value?: string | null): string {
  if (!value) return ''
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return ''
  return d.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' })
}

function readingTime(html: string): string {
  const words = html.replace(/<[^>]*>/g, ' ').trim().split(/\s+/).filter(Boolean).length
  return `${Math.max(1, Math.round(words / 200))} min read`
}

const subtitle = computed(() => {
  if (!news) return null
  return [formatDate(news.published_at), readingTime(news.content)].filter(Boolean).join(' · ')
})

// Some articles' rich content repeats the title as its own leading <h1>, duplicating the
// heading already shown in the page header above - strip it if present.
const cleanedContent = computed(() => (news ? (news.content as string).replace(/^\s*<h1[^>]*>[\s\S]*?<\/h1>\s*/i, '') : ''))
</script>

<template>
  <NotFound v-if="!news" message="This news item doesn't exist." />
  <article v-else>
    <PageHeader :title="news.title" :subtitle="subtitle" />

    <div class="mx-auto max-w-6xl px-6 py-10 sm:py-14">
      <div class="grid gap-10 md:grid-cols-[1fr_280px] md:items-start xl:grid-cols-[1fr_320px]">
        <div class="min-w-0">
          <img
            v-if="news.image_url && !imageFailed"
            :src="news.image_url"
            :alt="news.title"
            class="aspect-video w-full rounded-2xl object-cover shadow-card ring-1 ring-black/5 sm:aspect-[16/9]"
            @error="imageFailed = true"
          >

          <div
            class="prose prose-slate mt-8 max-w-none prose-headings:font-display prose-headings:text-brand-navy prose-a:font-semibold prose-a:text-brand-navy prose-a:no-underline prose-img:rounded-xl prose-img:shadow-card prose-blockquote:border-brand-gold prose-strong:text-gray-900 hover:prose-a:underline"
            v-html="cleanedContent"
          />

          <div class="mt-10 border-t border-gray-100 pt-6">
            <router-link to="/news" class="group inline-flex items-center gap-2 font-semibold text-brand-navy transition hover:underline">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
              Back to News
            </router-link>
          </div>
        </div>

        <aside v-if="moreNews.length" class="md:sticky md:top-24">
          <h2 class="font-display text-lg font-bold text-gray-900">More News</h2>
          <div class="mt-5 space-y-4">
            <router-link
              v-for="item in moreNews"
              :key="item.id"
              :to="`/news/${item.slug}`"
              class="group flex gap-3 rounded-xl p-2 transition hover:bg-gray-50"
            >
              <div class="h-16 w-20 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                <img
                  v-if="item.image_url && !sidebarImageFailed[item.id]"
                  :src="item.image_url"
                  :alt="item.title"
                  class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                  @error="sidebarImageFailed[item.id] = true"
                >
              </div>
              <div class="min-w-0">
                <h3 class="line-clamp-2 text-sm font-semibold leading-snug text-gray-900 transition group-hover:text-brand-navy">{{ item.title }}</h3>
                <p v-if="item.published_at" class="mt-1.5 text-xs text-gray-400">{{ formatDate(item.published_at) }}</p>
              </div>
            </router-link>
          </div>
          <router-link to="/news" class="mt-2 inline-block text-sm font-semibold text-brand-navy hover:underline">View all news &rarr;</router-link>
        </aside>
      </div>
    </div>
  </article>
</template>
