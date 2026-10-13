<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useHead } from '@unhead/vue'
import { api } from '../../services/api'
import NotFound from '../errors/NotFound.vue'
import { staticAsset } from '../../utils/staticAsset'
import { toArticleHtml } from '../../utils/articleHtml'

const props = defineProps<{ slug: string }>()

let article: Record<string, any> | null = null
let others: Array<Record<string, any>> = []
try {
  // the article and the list of other eVoices (for the "more stories" sidebar) load together
  const [one, list] = await Promise.all([
    api.get(`/campus-voices/${props.slug}`),
    api.get('/campus-voices').catch(() => ({ data: { data: { articles: [] } } })),
  ])
  article = one.data.data.article
  others = (list.data.data.articles as Array<Record<string, any>>).filter((a) => a.slug !== props.slug).slice(0, 4)
} catch {
  article = null
}

useHead({
  title: article ? article.title : 'Page not found',
  meta: article?.summary ? [{ name: 'description', content: String(article.summary).replace(/\s+/g, ' ').slice(0, 200) }] : [],
})

// The image file is missing from the server for some older articles (pre-existing, unrelated to
// this page) - hide it on load failure instead of showing a broken-image icon.
const imageFailed = ref(false)
const otherImageFailed = ref<Record<number, boolean>>({})

// Word-pasted styling is stripped and plain text becomes paragraphs (see utils/articleHtml.ts).
const body = computed(() => (article ? toArticleHtml(article.content) : ''))

// en-GB and UTC are fixed so the prerendered page and the browser always print the same date.
const published = computed(() => {
  const d = article?.published_at ? new Date(String(article.published_at).replace(' ', 'T') + 'Z') : null
  return d && !Number.isNaN(d.getTime()) ? d.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC' }) : ''
})
const initials = computed(() =>
  String(article?.student_name ?? '')
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((w) => w[0]?.toUpperCase())
    .join(''),
)

// thin reading-progress bar along the top of the screen
const progress = ref(0)
function onScroll() {
  const el = document.documentElement
  const max = el.scrollHeight - el.clientHeight
  progress.value = max > 0 ? Math.min(100, Math.max(0, (el.scrollTop / max) * 100)) : 0
}
onMounted(() => {
  onScroll()
  window.addEventListener('scroll', onScroll, { passive: true })
})
onBeforeUnmount(() => window.removeEventListener('scroll', onScroll))
</script>

<template>
  <NotFound v-if="!article" message="This article doesn't exist." />

  <template v-else>
    <div class="pointer-events-none fixed inset-x-0 top-0 z-[55] h-1 bg-transparent" aria-hidden="true">
      <div class="h-full bg-brand-gold transition-[width] duration-150" :style="{ width: `${progress}%` }" />
    </div>

    <!-- ===== Hero: title + byline beside the photo (stacked on phones and tablets) ===== -->
    <header
      class="relative overflow-hidden bg-brand-navy-dark bg-cover bg-center text-white"
      :style="{ backgroundImage: `url('${staticAsset('storage/images/smacon.jpg')}')` }"
    >
      <!-- the school building shows through a navy wash that is darker where the text sits (left) -->
      <div class="pointer-events-none absolute inset-0 bg-gradient-to-r from-brand-navy-dark/95 via-brand-navy-dark/85 to-brand-navy/70" />
      <div class="pointer-events-none absolute -right-32 -top-32 h-96 w-96 rounded-full bg-brand-gold/10 blur-3xl" />
      <div class="pointer-events-none absolute -bottom-32 -left-32 h-96 w-96 rounded-full bg-white/5 blur-3xl" />

      <div class="container-wide relative grid items-center gap-8 py-10 sm:py-14 lg:grid-cols-[1.15fr_0.85fr] lg:gap-14 lg:py-16 min-[1920px]:grid-cols-[1.35fr_1fr] min-[1920px]:gap-24 min-[1920px]:py-20 min-[2400px]:py-24">
        <div>
          <router-link to="/campus-voices" class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-sm font-semibold text-blue-100 backdrop-blur transition hover:bg-white/20 hover:text-white" style="color: inherit">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
            eVoices
          </router-link>

          <div class="mt-6 h-1 w-16 rounded-full bg-brand-gold" />
          <h1 class="mt-5 text-4xl font-extrabold leading-[1.1] tracking-tight text-white sm:text-5xl lg:text-6xl min-[1920px]:text-7xl min-[2400px]:text-8xl">{{ article.title }}</h1>

          <div class="mt-7 flex flex-wrap items-center gap-x-5 gap-y-3 text-blue-100 min-[1920px]:mt-10 min-[1920px]:text-lg">
            <span class="flex items-center gap-3">
              <span class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-gold font-display text-base font-extrabold text-brand-navy-dark min-[1920px]:h-14 min-[1920px]:w-14 min-[1920px]:text-lg">{{ initials }}</span>
              <span class="leading-tight">
                <span class="block font-semibold text-white">{{ article.student_name }}</span>
                <span v-if="article.author_bio && String(article.author_bio).length <= 24" class="block text-sm text-blue-200">{{ article.author_bio }}</span>
              </span>
            </span>
            <span v-if="published" class="flex items-center gap-1.5"><span class="h-1 w-1 rounded-full bg-brand-gold" />{{ published }}</span>
            <span class="flex items-center gap-1.5"><span class="h-1 w-1 rounded-full bg-brand-gold" />{{ article.reading_time }} min read</span>
            <span class="flex items-center gap-1.5"><span class="h-1 w-1 rounded-full bg-brand-gold" />{{ Number(article.views).toLocaleString('en-GB') }} views</span>
            <span v-if="article.category && article.category !== 'None'" class="rounded-full bg-white/10 px-3 py-1 text-sm font-semibold text-white">{{ article.category }}</span>
          </div>
        </div>

        <div v-if="article.featured_image_url && !imageFailed" class="mx-auto w-full max-w-2xl lg:max-w-none">
          <div class="overflow-hidden rounded-3xl shadow-2xl ring-1 ring-white/20">
            <img :src="article.featured_image_url" :alt="article.title" class="aspect-[4/3] w-full object-cover object-top min-[1920px]:aspect-[16/11]" @error="imageFailed = true">
          </div>
        </div>
      </div>
    </header>

    <!-- ===== Body: the story, with a sticky sidebar on wide screens ===== -->
    <div class="container-wide py-10 sm:py-14 min-[1920px]:py-20">
      <div class="grid gap-12 xl:grid-cols-[minmax(0,1fr)_21rem] min-[1920px]:grid-cols-[minmax(0,1fr)_28rem] min-[1920px]:gap-24">
        <article class="mx-auto w-full max-w-[46rem] min-[1536px]:max-w-[52rem] min-[1920px]:max-w-[64rem] min-[2400px]:max-w-[78rem]">
          <!-- text grows with the screen: 18px on laptops, 20px on 24", 24px on 27"+ monitors -->
          <div
            class="prose prose-lg max-w-none min-[1920px]:prose-xl min-[2400px]:prose-2xl prose-slate prose-headings:font-display prose-headings:font-bold prose-a:text-brand-navy prose-img:rounded-2xl prose-p:leading-[1.85] prose-p:text-gray-800"
            v-html="body"
          />

          <div v-if="article.author_bio && String(article.author_bio).length > 24" class="mt-12 rounded-3xl bg-gray-50 p-6 ring-1 ring-black/5 sm:p-8 min-[1920px]:p-10">
            <p class="text-sm font-bold uppercase tracking-widest text-brand-gold">About the author</p>
            <p class="mt-2 font-display text-xl font-bold text-gray-900 min-[1920px]:text-2xl">{{ article.student_name }}</p>
            <p class="mt-2 leading-relaxed text-gray-600 min-[1920px]:text-lg">{{ article.author_bio }}</p>
          </div>

          <div class="mt-12 flex flex-wrap items-center justify-between gap-4 border-t border-gray-200 pt-8">
            <router-link to="/campus-voices" class="btn btn-gold">&larr; Back to eVoices</router-link>
            <p class="text-sm text-gray-500">Written by <strong class="text-gray-800">{{ article.student_name }}</strong></p>
          </div>
        </article>

        <aside v-if="others.length" class="xl:sticky xl:top-28 xl:self-start">
          <p class="text-sm font-bold uppercase tracking-widest text-brand-gold min-[1920px]:text-base">More from eVoices</p>
          <ul class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-1 min-[1920px]:gap-6">
            <li v-for="o in others" :key="o.id">
              <router-link :to="`/campus-voices/${o.slug}`" class="group flex items-center gap-4 rounded-2xl bg-white p-3 shadow-card ring-1 ring-black/5 transition duration-300 hover:-translate-y-1 hover:shadow-card-hover min-[1920px]:gap-5 min-[1920px]:p-4">
                <span class="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-gray-100 min-[1920px]:h-28 min-[1920px]:w-28">
                  <img v-if="o.featured_image_url && !otherImageFailed[o.id]" :src="o.featured_image_url" :alt="o.title" loading="lazy" decoding="async" class="h-full w-full object-cover object-top transition duration-500 group-hover:scale-110" @error="otherImageFailed[o.id] = true">
                  <span v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-brand-navy to-brand-navy-dark font-display text-xl font-extrabold text-white/30">SM</span>
                </span>
                <span class="min-w-0">
                  <span class="line-clamp-2 block font-display font-bold leading-snug text-gray-900 transition-colors group-hover:text-brand-navy min-[1920px]:text-lg">{{ o.title }}</span>
                  <span class="mt-1 block text-sm text-gray-500 min-[1920px]:text-base">by {{ o.student_name }}</span>
                </span>
              </router-link>
            </li>
          </ul>
        </aside>
      </div>
    </div>
  </template>
</template>
