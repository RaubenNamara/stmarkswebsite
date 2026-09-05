<template>
  <main class="min-h-screen bg-slate-50/70">
    <div class="px-4 sm:px-6 lg:px-8 py-6 sm:py-10 lg:py-14">
      <article class="bg-white shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
        <!-- HERO -->
        <header class="flex flex-col md:flex-row">
          <div v-if="news.image_url" class="w-full md:w-1/2">
            <img
              :src="news.image_url"
              :alt="news.title"
              class="w-full h-auto object-contain"
              loading="lazy"
            />
          </div>

          <div v-if="news.image_url" class="w-full md:w-1/2 bg-slate-900 px-5 py-6 sm:px-8 sm:py-8 md:px-12 md:py-10 lg:px-16 lg:py-12 flex flex-col justify-center">
            <div>
              <h1
                class="text-2xl font-extrabold leading-tight tracking-tight text-white sm:text-3xl md:text-4xl lg:text-5xl xl:text-5xl"
              >
                {{ news.title }}
              </h1>

              <div class="mt-5 flex flex-wrap gap-3 text-sm text-slate-300">
                <span class="inline-flex items-center gap-2">
                  <span>📅</span>
                  <span>{{ beautifulDate(news.created_at) }}</span>
                </span>

                <span class="inline-flex items-center gap-2">
                  <span>⏱</span>
                  <span>{{ readingTime }}</span>
                </span>
              </div>
            </div>
          </div>

          <div
            v-else
            class="px-5 py-14 sm:px-8 sm:py-18 md:px-12 md:py-24 bg-gradient-to-br from-indigo-50 via-white to-sky-50 border-b border-slate-200 text-center"
          >
            <div class="mx-auto max-w-4xl">
              <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl md:text-5xl lg:text-6xl">
                {{ news.title }}
              </h1>
              <p class="mt-4 text-sm sm:text-base text-slate-600">
                {{ beautifulDate(news.created_at) }} · {{ readingTime }}
              </p>
            </div>
          </div>
        </header>

        <!-- CONTENT -->
        <section class="px-4 sm:px-6 md:px-10 lg:px-16 py-8 sm:py-10 lg:py-14">
          <div class="mx-auto max-w-5xl">
            <div
              v-if="news.content"
              class="news-content prose prose-slate max-w-none prose-headings:scroll-mt-28 prose-a:text-indigo-700 prose-a:no-underline hover:prose-a:underline prose-img:rounded-none prose-img:shadow-lg prose-blockquote:border-indigo-200 prose-blockquote:bg-indigo-50/60 prose-blockquote:py-1 prose-blockquote:px-4 prose-blockquote:rounded-none"
              v-html="news.content"
            ></div>

            <div
              v-else
              class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center text-slate-600"
            >
              <p class="text-base sm:text-lg font-medium">No content available for this article.</p>
            </div>
          </div>
        </section>

        <!-- FOOTER -->
        <footer class="border-t border-slate-200 bg-slate-50/80 px-4 sm:px-6 md:px-10 lg:px-16 py-5 sm:py-6">
          <div class="mx-auto flex max-w-5xl flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="text-sm text-slate-600">
              Published on
              <span class="font-semibold text-slate-900">
                {{ beautifulDate(news.created_at) }}
              </span>
            </div>

            <div class="flex flex-wrap items-center gap-3">
              <a
                :href="shareUrl('x')"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center justify-center rounded-full bg-sky-50 px-4 py-2 text-sm font-semibold text-sky-700 transition hover:bg-sky-100"
              >
                Share on X
              </a>

              <a
                :href="shareUrl('facebook')"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center justify-center rounded-full bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100"
              >
                Share on Facebook
              </a>
            </div>
          </div>
        </footer>
      </article>

      <!-- POSTS GRID -->
      <section v-if="displayedPosts.length" class="mt-10 sm:mt-14 lg:mt-20">
        <div class="mb-6 sm:mb-8 flex items-end justify-between gap-4">
          <div>
            <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">
              More News &amp; Updates
            </h2>
            <p class="mt-2 text-sm sm:text-base text-slate-600">
              Latest stories from St. Mark's College Namagoma.
            </p>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
          <article
            v-for="post in displayedPosts"
            :key="post.id"
            class="group overflow-hidden border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl"
          >
            <div class="relative h-52 sm:h-56 overflow-hidden bg-slate-100">
              <img
                :src="post.image_url || placeholder"
                :alt="post.title"
                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                loading="lazy"
              />

              <div
                v-if="post.created_at || post.date || post.published_at"
                class="absolute left-4 top-4 rounded-xl border border-white/70 bg-white/90 px-3 py-2 text-center shadow-lg backdrop-blur"
              >
                <div class="text-lg font-extrabold leading-none text-slate-900">
                  {{ dateParts(post.created_at || post.date || post.published_at).day }}
                </div>
                <div class="text-[11px] font-bold uppercase tracking-widest text-indigo-700">
                  {{ dateParts(post.created_at || post.date || post.published_at).month }}
                </div>
              </div>
            </div>

            <div class="p-5 sm:p-6">
              <a
                :href="post.url || `/news/${post.slug || post.id}`"
                class="mb-3 block text-base sm:text-lg font-bold leading-snug text-slate-900 transition group-hover:text-indigo-700 line-clamp-2"
              >
                {{ post.title }}
              </a>

              <p class="mb-4 text-sm leading-7 text-slate-600 text-justify line-clamp-3">
                {{ cleanExcerpt(post) }}
              </p>

              <div class="flex items-center justify-between gap-3">
                <div class="text-xs font-medium text-slate-400">
                  {{ beautifulDate(post.created_at || post.date || post.published_at) }}
                </div>

                <a
                  :href="post.url || `/news/${post.slug || post.id}`"
                  class="text-sm font-semibold text-indigo-700 transition hover:text-indigo-900"
                >
                  Read More →
                </a>
              </div>
            </div>
          </article>
        </div>
      </section>

      <section v-else class="mt-10 sm:mt-14 lg:mt-16 text-center text-slate-500">
        <p>No posts to show yet.</p>
      </section>
    </div>
  </main>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import MainLayout from '@/Layouts/MainLayout.vue'
import { usePageView } from '@/composables/usePageView'

defineOptions({
  layout: MainLayout
})

const props = defineProps({
  news: { type: Object, required: true },
  posts: { type: Array, default: () => [] }
})

// Track page view for this news item
usePageView('news', props.news.id)

const placeholder = '/images/placeholder-news.jpg'

onMounted(() => {
  document.title = props.news?.title
    ? `${props.news.title} — St. Mark's College Namagoma`
    : "News — St. Mark's College Namagoma"
})

function beautifulDate(value) {
  if (!value) return ''
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return value

  return d.toLocaleDateString(undefined, {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

function dateParts(dateStr) {
  if (!dateStr) return { day: '', month: '' }
  const d = new Date(dateStr)
  if (Number.isNaN(d.getTime())) return { day: '', month: '' }

  return {
    day: String(d.getDate()).padStart(2, '0'),
    month: d.toLocaleString(undefined, { month: 'short' }).toUpperCase()
  }
}

function stripHtml(html = '') {
  return String(html).replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim()
}

function truncate(str = '', max = 160) {
  return str.length > max ? `${str.slice(0, max).trim()}...` : str
}

function cleanExcerpt(post) {
  const raw = post.excerpt || post.content || ''
  return truncate(stripHtml(raw), 160)
}

const readingTime = computed(() => {
  const text = stripHtml(props.news?.content || '')
  const words = text ? text.split(/\s+/).filter(Boolean).length : 0
  const minutes = Math.max(1, Math.round(words / 200))
  return `${minutes} min read`
})

const displayedPosts = computed(() => {
  return (props.posts || []).slice(0, 24)
})

function shareUrl(provider) {
  if (typeof window === 'undefined') return '#'

  const url = encodeURIComponent(window.location.href)
  const title = encodeURIComponent(props.news?.title || '')

  if (provider === 'twitter' || provider === 'x') {
    return `https://twitter.com/intent/tweet?text=${title}&url=${url}`
  }

  if (provider === 'facebook') {
    return `https://www.facebook.com/sharer/sharer.php?u=${url}`
  }

  return '#'
}
</script>

<style scoped>
.news-content {
  line-height: 1.9;
  font-size: 1.06rem;
  color: #0f172a;
  text-align: justify;
  text-justify: inter-word;
}

.news-content :deep(p),
.news-content :deep(li),
.news-content :deep(blockquote) {
  text-align: justify;
  text-justify: inter-word;
}

.news-content :deep(img) {
  border-radius: 0 !important;
  display: block;
  height: auto;
}

.news-content :deep(h1),
.news-content :deep(h2),
.news-content :deep(h3),
.news-content :deep(h4) {
  color: #0f172a;
  font-weight: 800;
  line-height: 1.2;
}

.news-content :deep(a) {
  word-break: break-word;
}

.line-clamp-2,
.line-clamp-3 {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-2 {
  -webkit-line-clamp: 2;
}

.line-clamp-3 {
  -webkit-line-clamp: 3;
}


@media (prefers-reduced-motion: reduce) {
  * {
    scroll-behavior: auto !important;
    transition: none !important;
    animation: none !important;
  }
}
</style>