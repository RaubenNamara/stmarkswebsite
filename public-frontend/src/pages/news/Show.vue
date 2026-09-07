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

useHead({ title: news ? news.title : 'Page not found' })

// The image file is missing from the server for some older items (pre-existing, unrelated to
// this page) - hide it on load failure instead of showing a broken-image icon.
const imageFailed = ref(false)

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

    <div class="mx-auto max-w-3xl px-6 py-10 sm:py-14">
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
  </article>
</template>
