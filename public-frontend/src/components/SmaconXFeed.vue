<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { api } from '../services/api'

/**
 * "Latest Updates from SMACON" - up to four real posts from x.com/Smacon24, served by the
 * backend's /x-feed endpoint (see shared/src/Services/XFeedService.php for where they come from
 * and why X's own embedded-timeline widget isn't used). Nothing here ever fabricates a post: when
 * the backend has none, the section shows a follow-us panel instead of placeholder "updates".
 *
 * Fetched client-side only (onMounted never runs during vite-ssg's prerender), so the static
 * HTML never freezes a stale snapshot of the feed, and only once the section nears the viewport.
 */

interface FeedMedia {
  type: 'photo' | 'video'
  image_url: string
  width: number | null
  height: number | null
  alt: string
}
interface FeedPost {
  id: string
  url: string
  text: string
  created_at: string | null
  date_precision: 'day' | 'time'
  media: FeedMedia[]
  has_media: boolean
}
interface FeedAccount {
  name: string
  username: string
  url: string
  profile_image_url: string | null
  verified: boolean | null
}

const PROFILE_URL = 'https://x.com/Smacon24'
const SLOTS = 4
// Roughly what fits in the clamped text area of a card at its narrowest four-column width.
const LONG_POST_CHARS = 220

const state = ref<'loading' | 'ready' | 'unavailable'>('loading')
const posts = ref<FeedPost[]>([])
const account = ref<FeedAccount | null>(null)
const avatarFailed = ref(false)
const mediaFailed = ref<Record<string, boolean>>({})
const sectionEl = ref<HTMLElement | null>(null)
let observer: IntersectionObserver | null = null

// When X returns only 1-3 posts, the last grid slot becomes a "more on X" card so the row stays
// balanced - a link out, never a stand-in post.
const showMoreCard = computed(() => posts.value.length > 0 && posts.value.length < SLOTS)

async function load() {
  try {
    const { data } = await api.get('/x-feed')
    account.value = data.data.account
    posts.value = (data.data.posts as FeedPost[]).slice(0, SLOTS)
    state.value = posts.value.length ? 'ready' : 'unavailable'
    if (import.meta.env.DEV && !posts.value.length) {
      console.info(`[SmaconXFeed] no posts (source: ${data.data.source}) - set X_API_BEARER_TOKEN or X_FEED_POST_URLS in shared/.env`)
    }
  } catch (error) {
    state.value = 'unavailable'
    if (import.meta.env.DEV) console.warn('[SmaconXFeed] feed request failed', error)
  }
}

onMounted(() => {
  if (typeof IntersectionObserver === 'undefined' || !sectionEl.value) {
    load()
    return
  }
  observer = new IntersectionObserver((entries) => {
    if (entries.some((e) => e.isIntersecting)) {
      observer?.disconnect()
      observer = null
      load()
    }
  }, { rootMargin: '400px 0px' })
  observer.observe(sectionEl.value)
})
onBeforeUnmount(() => observer?.disconnect())

// ---- text: split into plain/link tokens rendered as text nodes (never v-html) ----
type Token = { kind: 'text' | 'url' | 'mention' | 'hashtag'; value: string; href?: string }
const TOKEN_RE = /(https?:\/\/[^\s]+)|(@\w{1,15})|(#[\p{L}\p{N}_]+)/gu

function tokenize(text: string): Token[] {
  const tokens: Token[] = []
  let last = 0
  for (const match of text.matchAll(TOKEN_RE)) {
    const index = match.index ?? 0
    if (index > last) tokens.push({ kind: 'text', value: text.slice(last, index) })
    const [value, url, mention] = match
    if (url) tokens.push({ kind: 'url', value: url.replace(/^https?:\/\//, ''), href: url })
    else if (mention) tokens.push({ kind: 'mention', value, href: `https://x.com/${value.slice(1)}` })
    else tokens.push({ kind: 'hashtag', value, href: `https://x.com/hashtag/${encodeURIComponent(value.slice(1))}` })
    last = index + value.length
  }
  if (last < text.length) tokens.push({ kind: 'text', value: text.slice(last) })
  return tokens
}

function isLong(post: FeedPost): boolean {
  return post.text.length > LONG_POST_CHARS || post.text.split('\n').length > 6
}

// ---- dates ----
function parseDate(post: FeedPost): Date | null {
  if (!post.created_at) return null
  // Day-only dates ("2026-10-03") are calendar days, not UTC midnights - parse as local.
  const date = post.date_precision === 'day' ? new Date(`${post.created_at}T00:00:00`) : new Date(post.created_at)
  return Number.isNaN(date.getTime()) ? null : date
}

function fullDate(post: FeedPost): string {
  const date = parseDate(post)
  if (!date) return ''
  return date.toLocaleDateString('en-GB', {
    day: 'numeric', month: 'long', year: 'numeric',
    ...(post.date_precision === 'time' ? { hour: '2-digit', minute: '2-digit' } : {}),
  })
}

function shortDate(post: FeedPost): string {
  const date = parseDate(post)
  if (!date) return ''
  const rtf = new Intl.RelativeTimeFormat('en', { numeric: 'auto' })
  const diffMs = Date.now() - date.getTime()
  const days = Math.floor(diffMs / 86_400_000)

  if (post.date_precision === 'time' && days < 1) {
    const hours = Math.floor(diffMs / 3_600_000)
    return hours < 1 ? rtf.format(-Math.max(1, Math.floor(diffMs / 60_000)), 'minute') : rtf.format(-hours, 'hour')
  }
  if (days >= 0 && days < 7) return rtf.format(-days, 'day')
  return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

function isoDate(post: FeedPost): string | undefined {
  return parseDate(post)?.toISOString()
}
</script>

<template>
  <section ref="sectionEl" class="container-wide py-6 sm:py-8" aria-labelledby="smacon-x-heading">
    <div v-reveal class="flex flex-wrap items-end justify-between gap-4">
      <div class="max-w-2xl">
        <h2 id="smacon-x-heading" class="font-display text-3xl font-bold text-gray-900">Latest Updates from SMACON</h2>
        <p class="mt-2 leading-relaxed text-gray-600">Stay connected with the latest news, announcements, achievements and activities from St. Mark’s College Namagoma.</p>
      </div>
      <a :href="PROFILE_URL" target="_blank" rel="noopener noreferrer" class="btn x-focus shrink-0 px-5 py-2.5">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.9 2H22l-7.6 8.7L23.3 22H16.7l-5.2-6.8L5.5 22H2.4l8.1-9.3L1.5 2h6.8l4.7 6.2L18.9 2zm-1.2 18h1.7L7.4 4h-1.8l12.1 16z" /></svg>
        Follow Us on X
        <span class="sr-only">(opens in a new tab)</span>
      </a>
    </div>

    <!-- Loading: same footprint as the real cards, so nothing below jumps when posts arrive -->
    <div v-if="state === 'loading'" class="mt-8 grid gap-6 sm:grid-cols-2 xl:grid-cols-4" aria-busy="true" aria-label="Loading latest posts from X">
      <div v-for="n in SLOTS" :key="n" class="x-card animate-pulse motion-reduce:animate-none">
        <div class="flex items-center gap-3">
          <div class="h-11 w-11 rounded-full bg-gray-200" />
          <div class="flex-1 space-y-2"><div class="h-3 w-2/3 rounded bg-gray-200" /><div class="h-3 w-1/3 rounded bg-gray-100" /></div>
        </div>
        <div class="mt-5 space-y-2.5"><div class="h-3 rounded bg-gray-100" /><div class="h-3 rounded bg-gray-100" /><div class="h-3 w-4/5 rounded bg-gray-100" /></div>
        <div class="mt-5 aspect-video rounded-xl bg-gray-100" />
      </div>
    </div>

    <!-- Real posts -->
    <ul v-else-if="state === 'ready'" class="mt-8 grid gap-6 sm:grid-cols-2 xl:grid-cols-4" role="list">
      <li v-for="(post, i) in posts" :key="post.id" v-reveal="i * 80" class="x-card group">
        <article class="flex h-full flex-col" :aria-labelledby="`x-post-${post.id}-author`">
          <!-- Account identity -->
          <header class="flex items-center gap-3">
            <img
              v-if="account?.profile_image_url && !avatarFailed"
              :src="account.profile_image_url"
              alt=""
              width="44"
              height="44"
              loading="lazy"
              decoding="async"
              class="h-11 w-11 shrink-0 rounded-full object-cover ring-1 ring-black/5"
              @error="avatarFailed = true"
            >
            <span v-else class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-navy font-display text-sm font-bold tracking-wide text-brand-gold" aria-hidden="true">SM</span>
            <div class="min-w-0 flex-1">
              <p :id="`x-post-${post.id}-author`" class="flex items-center gap-1 truncate font-bold leading-tight text-gray-900">
                <span class="truncate" :title="account?.name">{{ account?.name }}</span>
                <svg v-if="account?.verified === true" class="h-4 w-4 shrink-0 text-sky-500" viewBox="0 0 24 24" fill="currentColor" role="img" aria-label="Verified account"><path d="M22.25 12c0-1.43-.88-2.67-2.19-3.34.46-1.39.2-2.9-.81-3.91s-2.52-1.27-3.91-.81c-.66-1.31-1.91-2.19-3.34-2.19s-2.67.88-3.33 2.19c-1.4-.46-2.91-.2-3.92.81s-1.26 2.52-.8 3.91C2.63 9.33 1.75 10.57 1.75 12s.88 2.67 2.2 3.34c-.46 1.39-.21 2.9.8 3.91s2.52 1.26 3.91.81c.67 1.31 1.91 2.19 3.34 2.19s2.68-.88 3.34-2.19c1.39.45 2.9.2 3.91-.81s1.27-2.52.81-3.91c1.31-.67 2.19-1.91 2.19-3.34zm-11.71 4.2L6.8 12.46l1.41-1.42 2.26 2.26 4.8-5.23 1.47 1.36-6.2 6.77z" /></svg>
              </p>
              <p class="truncate text-sm text-gray-500">@{{ account?.username }}</p>
            </div>
            <svg class="h-5 w-5 shrink-0 text-gray-900" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.9 2H22l-7.6 8.7L23.3 22H16.7l-5.2-6.8L5.5 22H2.4l8.1-9.3L1.5 2h6.8l4.7 6.2L18.9 2zm-1.2 18h1.7L7.4 4h-1.8l12.1 16z" /></svg>
          </header>

          <!-- Post text: clamped so one long post can't stretch the whole row -->
          <p class="mt-4 line-clamp-6 whitespace-pre-line break-words text-[0.95rem] leading-relaxed text-gray-800"><template v-for="(token, t) in tokenize(post.text)" :key="t"><a
            v-if="token.href"
            :href="token.href"
            target="_blank"
            rel="noopener noreferrer nofollow"
            class="x-focus rounded-sm font-medium text-brand-navy underline-offset-2 hover:underline"
          >{{ token.value }}</a><template v-else>{{ token.value }}</template></template></p>
          <a v-if="isLong(post)" :href="post.url" target="_blank" rel="noopener noreferrer" class="x-focus mt-1 w-fit rounded-sm text-sm font-semibold text-brand-navy hover:underline">
            Read more<span class="sr-only"> of this post on X (opens in a new tab)</span>
          </a>

          <!-- Media: real image/preview from X when the API supplied one -->
          <a
            v-if="post.media.length && !mediaFailed[post.id]"
            :href="post.url"
            target="_blank"
            rel="noopener noreferrer"
            class="x-focus relative mt-4 block aspect-video overflow-hidden rounded-xl bg-gray-100 ring-1 ring-black/5"
          >
            <img
              :src="post.media[0].image_url"
              :alt="post.media[0].alt || (post.media[0].type === 'video' ? 'Video preview from this post' : 'Photo from this post')"
              loading="lazy"
              decoding="async"
              class="h-full w-full object-cover transition duration-500 motion-safe:group-hover:scale-[1.03]"
              @error="mediaFailed[post.id] = true"
            >
            <span v-if="post.media[0].type === 'video'" class="absolute inset-0 flex items-center justify-center bg-black/20" aria-hidden="true">
              <span class="flex h-12 w-12 items-center justify-center rounded-full bg-white/90 shadow-md"><svg class="ml-0.5 h-5 w-5 text-brand-navy" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg></span>
            </span>
            <span v-if="post.media.length > 1" class="absolute right-2 top-2 rounded-full bg-black/60 px-2 py-0.5 text-xs font-semibold text-white">+{{ post.media.length - 1 }}</span>
            <span class="sr-only">View the media on X (opens in a new tab)</span>
          </a>
          <!-- The post has media X didn't hand over (oEmbed mode, or a failed image): say so honestly -->
          <a
            v-else-if="post.has_media"
            :href="post.url"
            target="_blank"
            rel="noopener noreferrer"
            class="x-focus mt-4 flex w-fit items-center gap-2 rounded-full bg-brand-cream px-3 py-1.5 text-xs font-semibold text-brand-navy ring-1 ring-brand-navy/10 transition hover:bg-brand-cream/70"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" /><circle cx="8.5" cy="10" r="1.5" /><path d="m21 15-5-5L5 19" /></svg>
            Includes photo or video – view on X
          </a>

          <!-- Footer pinned to the card bottom so every card in a row lines up -->
          <div class="min-h-[1.25rem] flex-1" aria-hidden="true" />
          <footer class="flex items-center justify-between gap-3 border-t border-gray-100 pt-3 text-sm">
            <time v-if="parseDate(post)" :datetime="isoDate(post)" :title="fullDate(post)" class="text-gray-500">{{ shortDate(post) }}</time>
            <span v-else />
            <a :href="post.url" target="_blank" rel="noopener noreferrer" class="x-focus inline-flex min-h-[44px] items-center gap-1 rounded-sm font-semibold text-brand-navy hover:underline">
              View Post on X<span class="sr-only"> (opens in a new tab)</span>
              <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8" /></svg>
            </a>
          </footer>
        </article>
      </li>

      <li v-if="showMoreCard" class="x-card items-center justify-center bg-brand-cream/40 text-center">
        <svg class="h-8 w-8 text-gray-900" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.9 2H22l-7.6 8.7L23.3 22H16.7l-5.2-6.8L5.5 22H2.4l8.1-9.3L1.5 2h6.8l4.7 6.2L18.9 2zm-1.2 18h1.7L7.4 4h-1.8l12.1 16z" /></svg>
        <p class="mt-3 font-display text-lg font-bold text-gray-900">More from @Smacon24</p>
        <p class="mt-1 text-sm text-gray-600">See every update on our X profile.</p>
        <a :href="PROFILE_URL" target="_blank" rel="noopener noreferrer" class="btn btn-gold x-focus mt-4 px-5 py-2.5">
          Visit our profile<span class="sr-only"> on X (opens in a new tab)</span>
        </a>
      </li>
    </ul>

    <!-- Unavailable / no posts: a real invitation, never placeholder posts -->
    <div v-else class="mt-8 flex flex-col items-center gap-5 rounded-2xl bg-brand-cream/50 px-6 py-10 text-center ring-1 ring-brand-navy/10 sm:flex-row sm:px-10 sm:text-left">
      <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-brand-navy text-white" aria-hidden="true">
        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M18.9 2H22l-7.6 8.7L23.3 22H16.7l-5.2-6.8L5.5 22H2.4l8.1-9.3L1.5 2h6.8l4.7 6.2L18.9 2zm-1.2 18h1.7L7.4 4h-1.8l12.1 16z" /></svg>
      </span>
      <div class="flex-1">
        <p class="font-display text-xl font-bold text-gray-900">Our latest posts are on X</p>
        <p class="mt-1 text-gray-600">We couldn’t load recent updates here right now. Follow <span class="font-semibold text-brand-navy">@Smacon24</span> for news, announcements and highlights from St. Mark’s College Namagoma.</p>
      </div>
      <a :href="PROFILE_URL" target="_blank" rel="noopener noreferrer" class="btn btn-gold x-focus shrink-0 px-5 py-2.5">
        Visit @Smacon24 on X<span class="sr-only"> (opens in a new tab)</span>
      </a>
    </div>
  </section>
</template>

<style scoped>
.x-card {
  @apply flex min-w-0 flex-col rounded-2xl bg-white p-5 shadow-card ring-1 ring-black/5 transition duration-200;
}
.x-card:hover {
  @apply shadow-card-hover;
}
/* outline, not ring: .btn's own box-shadow would otherwise swallow a ring utility */
.x-focus:focus-visible {
  outline: 2px solid theme('colors.brand-navy');
  outline-offset: 3px;
}
@media (prefers-reduced-motion: reduce) {
  .x-card,
  .x-card :deep(*) {
    transition: none !important;
  }
}
</style>
