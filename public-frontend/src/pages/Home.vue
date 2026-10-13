<script setup lang="ts">
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue'
import { useHead } from '@unhead/vue'
import { api } from '../services/api'
import { staticAsset } from '../utils/staticAsset'
import AvatarImage from '../components/AvatarImage.vue'
import NewsCard from '../components/NewsCard.vue'
// Hidden until X_API_BEARER_TOKEN or X_FEED_POST_URLS is set in shared/.env - uncomment this and the <SmaconXFeed /> tag below to show it.
// import SmaconXFeed from '../components/SmaconXFeed.vue'

useHead({
  title: null,
  meta: [{ name: 'description', content: "St. Mark's College Namagoma - The High Achiever's College." }],
})

const [slidesRes, mediaRes, newsRes, campusVoicesRes, staffRes, calendarEventsRes] = await Promise.all([
  api.get('/slides'),
  api.get('/media'),
  api.get('/news', { params: { limit: 20 } }),
  api.get('/campus-voices'),
  api.get('/staff'),
  api.get('/calendar-events'),
])

const slides = (slidesRes.data.data.slides as Array<Record<string, any>>).filter((s) => s.image_url || s.video_url)
const mediaItems = mediaRes.data.data.media as Array<Record<string, any>>
const newsItems = newsRes.data.data.news as Array<Record<string, any>>
const voices = (campusVoicesRes.data.data.articles as Array<Record<string, any>>).filter((v) => v.featured_image_url)

// -- Upcoming Events: every event, on the Home page too (not just the full /academics/
// school-calendar page) - no cap. Past events (by date, not just the admin's is_completed tick)
// stay in the list rather than being hidden, showing a tick instead when completed - when the
// admin ticks one off in /admin/calendar-events, this picks it up automatically since both read
// the same API, no separate sync step. The API already returns event_date ascending, so a stable
// sort on "is this past" keeps each group (upcoming, then past) date-ascending internally while
// moving past events to the end regardless of whether they've been explicitly marked completed.
const today = new Date()
today.setHours(0, 0, 0, 0)
function isPastEvent(event: Record<string, any>): boolean {
  if (event.is_completed) return true
  const d = new Date(`${event.event_date}T00:00:00`)
  return !Number.isNaN(d.getTime()) && d < today
}
const calendarEvents = (calendarEventsRes.data.data.calendar_events as Array<Record<string, any>>)
  .slice()
  .sort((a, b) => Number(isPastEvent(a)) - Number(isPastEvent(b)))
function eventDay(value: string): string {
  const d = new Date(`${value}T00:00:00`)
  return Number.isNaN(d.getTime()) ? '' : String(d.getDate())
}
function eventMonth(value: string): string {
  const d = new Date(`${value}T00:00:00`)
  return Number.isNaN(d.getTime()) ? '' : d.toLocaleDateString(undefined, { month: 'short' })
}
function eventFullDate(value: string): string {
  const d = new Date(`${value}T00:00:00`)
  return Number.isNaN(d.getTime()) ? value : d.toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
}

// -- Meet Our Dedicated Team: a batch of staff cards (name, photo, role) above "Articles You May
// Have Missed" that swaps to a fresh batch every 9s, the whole grid blurring/fading out and the
// next one blurring/fading in together (mode="out-in", reusing the .staff-blur transition).
// 4 cards normally, 5 on 27"+ monitors (>=1920px, matching the same custom breakpoint used
// elsewhere in this file for wide screens) - visibleTeamCount tracks that via a resize listener,
// same resize-listener pattern used by the other responsive counts in this file.
const staffGroups = staffRes.data.data.groups as Record<string, Array<Record<string, any>>>
const teamStaff = Object.values(staffGroups)
  .flat()
  .filter((m) => m.photo_url)
const teamCardImageFailed = reactive<Record<number, boolean>>({})
const teamBase = ref(0)
const visibleTeamCount = ref(4)
const teamCards = computed(() => {
  const count = Math.min(visibleTeamCount.value, teamStaff.length)
  return Array.from({ length: count }, (_, i) => teamStaff[(teamBase.value + i) % teamStaff.length])
})
let teamTimer: ReturnType<typeof setInterval> | null = null

function teamPlay() {
  teamStop()
  if (teamStaff.length <= visibleTeamCount.value) return
  teamTimer = setInterval(() => {
    teamBase.value = (teamBase.value + visibleTeamCount.value) % teamStaff.length
  }, 9000)
}
function teamStop() {
  if (teamTimer) {
    clearInterval(teamTimer)
    teamTimer = null
  }
}
function updateVisibleTeamCount() {
  visibleTeamCount.value = window.innerWidth >= 1920 ? 5 : 4
  teamPlay()
}
onMounted(() => {
  updateVisibleTeamCount()
  window.addEventListener('resize', updateVisibleTeamCount)
})
onBeforeUnmount(() => {
  teamStop()
  window.removeEventListener('resize', updateVisibleTeamCount)
})

const latestNews = newsItems.slice(0, 6)
// Capped rather than every article - the doubled-track marquee below only ever shows a handful
// on screen at once, so 14 is already enough real variety that the loop point isn't obvious,
// without rendering up to 200 image cards (2x100) into the DOM on every home page load.
const featuredPosts = newsItems.slice(0, 14)
const relatedPosts = newsItems.slice(5, 17)
// MediaService::all() orders newest-first (with id DESC as a tiebreaker for items from the same
// multi-image admin upload) - reversed here so a batch the admin selected together plays back in
// the order they were added, oldest (first-added) slide first, rather than backwards.
// Images play as one slideshow; videos and YouTube links each get their own standalone card
// alongside it instead of being mixed into the slide rotation.
const mediaImages = mediaItems.filter((item) => item.type === 'image').slice().reverse()
const mediaVideos = mediaItems.filter((item) => ['video', 'link'].includes(item.type)).slice().reverse()
// Column count for the showcase grid tracks how many cards there actually are (capped at 3) -
// fixing it at 3 left a lone image + video pair stranded in the left two-thirds of the row with
// an empty gap on the right instead of splitting the row evenly between them.
const mediaCardCount = (mediaImages.length ? 1 : 0) + mediaVideos.length
const mediaGridCols = mediaCardCount <= 1 ? '' : mediaCardCount === 2 ? 'sm:grid-cols-2' : 'sm:grid-cols-2 lg:grid-cols-3'

const leadership = [
  { name: 'Omuk. DG Daniel Mukasa Ddamulira', role: 'Director', image: 'images/director-daniel.jpg', url: '/director1', text: 'A visionary education leader committed to institutional growth, integrity and academic excellence.' },
  { name: 'Canon Alice Ddamulira', role: 'Director', image: 'images/director2.jpg', url: '/director2', text: 'Dedicated to nurturing discipline, excellence and strong moral values within the school community.' },
  { name: 'Wabwire Joseph', role: 'Head Teacher', image: 'images/hm.jpg', url: '/headteacher', text: 'Provides strong academic leadership ensuring holistic development and consistent performance.' },
]

const coreValues = [
  { letter: 'G', title: 'Godliness', text: 'Nurturing spiritual and ethical grounding in every learner.' },
  { letter: 'R', title: 'Reliability', text: 'Dependable teaching, administration and pastoral care.' },
  { letter: 'E', title: 'Ethics', text: 'Integrity and moral responsibility in action and learning.' },
  { letter: 'E', title: 'Excellence', text: 'High academic and co-curricular standards, pursued consistently.' },
  { letter: 'T', title: 'Team Work', text: 'Collaboration across students, staff and the wider community.' },
]

// -- Hero slider: autoplay, arrows, dots, swipe and keyboard navigation --
const current = ref(0)
const slideFailed = reactive<Record<number, boolean>>({})
let autoplayTimer: ReturnType<typeof setInterval> | null = null

function play() {
  stop()
  if (slides.length < 2) return
  autoplayTimer = setInterval(goNext, 8000)
}
function stop() {
  if (autoplayTimer) {
    clearInterval(autoplayTimer)
    autoplayTimer = null
  }
}
function goTo(i: number) {
  current.value = i
  play()
}
function goNext() {
  if (!slides.length) return
  current.value = (current.value + 1) % slides.length
}
function goPrev() {
  if (!slides.length) return
  current.value = (current.value - 1 + slides.length) % slides.length
}
function goNextAndReset() {
  goNext()
  play()
}
function goPrevAndReset() {
  goPrev()
  play()
}
function onKey(e: KeyboardEvent) {
  if (e.key === 'ArrowLeft') goPrevAndReset()
  if (e.key === 'ArrowRight') goNextAndReset()
}

let touchStartX = 0
function onTouchStart(e: TouchEvent) {
  touchStartX = e.changedTouches[0]?.clientX ?? 0
  stop()
}
function onTouchEnd(e: TouchEvent) {
  const dx = (e.changedTouches[0]?.clientX ?? touchStartX) - touchStartX
  if (dx > 40) goPrevAndReset()
  else if (dx < -40) goNextAndReset()
  else play()
}

onMounted(() => {
  play()
  window.addEventListener('keydown', onKey)
})
onBeforeUnmount(() => {
  stop()
  window.removeEventListener('keydown', onKey)
})

// -- Campus Voices: a featured slide plus a two-item "up next" queue, both auto-advancing --
const voiceCurrent = ref(0)
const voiceImageFailed = reactive<Record<number, boolean>>({})
let voiceTimer: ReturnType<typeof setInterval> | null = null

function voicePlay() {
  voiceStop()
  if (voices.length < 2) return
  voiceTimer = setInterval(() => {
    voiceCurrent.value = (voiceCurrent.value + 1) % voices.length
  }, 9000)
}
function voiceStop() {
  if (voiceTimer) {
    clearInterval(voiceTimer)
    voiceTimer = null
  }
}
function voiceGoTo(i: number) {
  voiceCurrent.value = i
  voicePlay()
}
const voiceQueue = computed(() => {
  if (voices.length < 2) return []
  const queue = []
  for (let offset = 1; offset < voices.length && queue.length < 3; offset++) {
    queue.push((voiceCurrent.value + offset) % voices.length)
  }
  return queue
})

onMounted(voicePlay)
onBeforeUnmount(voiceStop)

// -- Media Showcase slideshow: autoplay, arrows, dots and swipe, same shape as the hero slider --
const mediaCurrent = ref(0)
let mediaAutoplayTimer: ReturnType<typeof setInterval> | null = null

function mediaPlay() {
  mediaStop()
  if (mediaImages.length < 2) return
  mediaAutoplayTimer = setInterval(mediaGoNext, 6000)
}
function mediaStop() {
  if (mediaAutoplayTimer) {
    clearInterval(mediaAutoplayTimer)
    mediaAutoplayTimer = null
  }
}
function mediaGoTo(i: number) {
  mediaCurrent.value = i
  mediaPlay()
}
function mediaGoNext() {
  if (!mediaImages.length) return
  mediaCurrent.value = (mediaCurrent.value + 1) % mediaImages.length
}
function mediaGoPrev() {
  if (!mediaImages.length) return
  mediaCurrent.value = (mediaCurrent.value - 1 + mediaImages.length) % mediaImages.length
}
function mediaGoNextAndReset() {
  mediaGoNext()
  mediaPlay()
}
function mediaGoPrevAndReset() {
  mediaGoPrev()
  mediaPlay()
}

let mediaTouchStartX = 0
function mediaTouchStart(e: TouchEvent) {
  mediaTouchStartX = e.changedTouches[0]?.clientX ?? 0
  mediaStop()
}
function mediaTouchEnd(e: TouchEvent) {
  const dx = (e.changedTouches[0]?.clientX ?? mediaTouchStartX) - mediaTouchStartX
  if (dx > 40) mediaGoPrevAndReset()
  else if (dx < -40) mediaGoNextAndReset()
  else mediaPlay()
}

onMounted(mediaPlay)
onBeforeUnmount(mediaStop)

// -- "Welcome" background section, with a Read More/Show Less toggle --
const backgroundExpanded = ref(false)
const backgroundImageFailed = ref(false)

const backgroundIntro = 'St. Mark\'s College Namagoma is a high-quality private secondary school founded in 2003. It is a mixed boarding school offering both Arts and Sciences at "O" and "A" Level, owned by experienced, well-educated individuals with a strong commitment to excellence in academics and post-school life.'

const backgroundParagraphs = [
  'St. Mark\'s College Namagoma is a high-quality private secondary school founded in 2003. It is a mixed boarding school offering both Arts and Sciences at "O" and "A" Level. The College is owned by experienced and well-educated individuals with a strong commitment to excellence in academics and post-school life.',
  'The College has a seasoned Board of Governors comprising educationists, bankers and senior managers in Ugandan society. It is duly licensed and registered by the Ministry of Education and Sports, registration number PSS/S/261, UNEB centre number U1664.',
  'Since opening its doors on 10th February 2003, the College has achieved tremendous success in student recruitment and retention, and now accommodates over 2,000 students in both "O" and "A" Level sections. The campus sits on 30 acres of land at Namagoma, 10 miles along the Kampala-Masaka Road, neighbouring well-established schools such as King\'s College Budo, Trinity College Nabbingo and St. Lawrence Colleges, an environment that provides healthy competition for high academic achievement. Our philosophy is to value all students for their individual abilities and special talents, nurturing resourceful, disciplined and ethical citizens with strong analytical and problem-solving skills.',
]

// -- Broken-image tracking for dynamic content (some legacy uploads are missing on disk) --
const postImageFailed = reactive<Record<number, boolean>>({})
const relatedImageFailed = reactive<Record<number, boolean>>({})
const mediaImageFailed = reactive<Record<number, boolean>>({})

function formatDate(value?: string | null): string {
  if (!value) return ''
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return ''
  return d.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' })
}

function excerptFor(text: string, max = 100): string {
  const plain = (text || '').replace(/<[^>]*>/g, '')
  return plain.length > max ? plain.slice(0, max).trim() + '…' : plain
}

function youTubeId(url: string): string | null {
  if (url.includes('youtu.be/')) return url.split('youtu.be/')[1]?.split('?')[0] ?? null
  if (url.includes('youtube.com/watch')) return new URL(url).searchParams.get('v')
  if (url.includes('youtube.com/embed/')) return url.split('embed/')[1]?.split('?')[0] ?? null
  return null
}
function youTubeEmbed(url: string): string {
  const id = youTubeId(url)
  if (!id) return ''
  // autoplay only actually works muted (browsers block audible autoplay); loop on a single
  // video needs playlist set to that same id, or YouTube just stops after one play.
  return `https://www.youtube.com/embed/${id}?autoplay=1&mute=1&loop=1&playlist=${id}&controls=1`
}

// -- Media Showcase video cards: each video/YouTube-link item is its own static card (unlike the
// image slideshow, these don't rotate) - both uploaded videos and YouTube embeds autoplay muted
// and looping as soon as the page loads, same as the hero slider's video slides.
</script>

<template>
  <!-- ========== HERO SLIDER ========== -->
  <section
    class="relative w-full overflow-hidden bg-brand-navy-dark"
    @mouseenter="stop"
    @mouseleave="play"
    @touchstart.passive="onTouchStart"
    @touchend.passive="onTouchEnd"
  >
    <div class="relative h-[60vh] min-h-[280px] max-h-[520px] sm:h-[65vh] sm:min-h-[380px] sm:max-h-[640px] lg:h-[78vh] lg:max-h-[760px] xl:h-[72vh]">
      <template v-if="slides.length">
        <div
          v-for="(slide, i) in slides"
          :key="slide.id"
          class="absolute inset-0 transition-opacity duration-700 ease-in-out"
          :class="current === i ? 'z-10 opacity-100' : 'z-0 opacity-0'"
        >
          <img
            v-if="slide.type === 'image' && slide.image_url && !slideFailed[slide.id]"
            :src="slide.image_url"
            :alt="slide.title || `St. Mark's College Namagoma`"
            :fetchpriority="i === 0 ? 'high' : 'auto'"
            :loading="i === 0 ? 'eager' : 'lazy'"
            decoding="async"
            class="h-full w-full object-cover object-top"
            :class="current === i ? 'hero-zoom' : ''"
            @error="slideFailed[slide.id] = true"
          >
          <video
            v-else-if="slide.type === 'video' && slide.video_url && !slideFailed[slide.id]"
            :src="slide.video_url"
            poster="/images/hero-video-poster.jpg"
            autoplay
            muted
            loop
            playsinline
            class="h-full w-full object-cover object-top"
            @error="slideFailed[slide.id] = true"
          />
          <div v-else class="h-full w-full bg-gradient-to-br from-brand-navy via-brand-navy to-brand-navy-dark" />

          <div class="absolute inset-0 bg-gradient-to-b from-black/20 via-black/10 to-black/60" />
        </div>
      </template>

      <div v-else class="relative flex h-full items-center justify-center overflow-hidden bg-gradient-to-br from-brand-navy via-brand-navy to-brand-navy-dark px-6 text-center">
        <div class="pointer-events-none absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, white 1px, transparent 1px); background-size: 28px 28px;" />
        <div v-reveal class="relative mx-auto max-w-3xl">
          <span class="eyebrow">The High Achiever's College</span>
          <h1 class="mt-4 font-display text-4xl font-extrabold tracking-tight text-white sm:text-6xl">Welcome to St. Mark's College Namagoma</h1>
          <p class="mx-auto mt-5 max-w-2xl text-lg text-blue-100">Building disciplined, confident and competent learners — a community where character and academic excellence grow together.</p>
          <div class="mt-9 flex flex-wrap items-center justify-center gap-4">
            <a href="https://admission.stmark.sc.ug/" target="_blank" rel="noopener" class="btn btn-gold">Apply Now</a>
            <router-link to="/college-name" class="btn btn-outline">Learn More</router-link>
          </div>
        </div>
      </div>
    </div>

    <template v-if="slides.length > 1">
      <button type="button" aria-label="Previous slide" class="absolute left-4 top-1/2 z-30 -translate-y-1/2 rounded-full bg-white/15 p-2.5 text-white backdrop-blur-md transition hover:bg-white/25 sm:p-3" @click="goPrevAndReset">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
      </button>
      <button type="button" aria-label="Next slide" class="absolute right-4 top-1/2 z-30 -translate-y-1/2 rounded-full bg-white/15 p-2.5 text-white backdrop-blur-md transition hover:bg-white/25 sm:p-3" @click="goNextAndReset">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
      </button>
      <div class="absolute bottom-6 left-1/2 z-30 flex -translate-x-1/2 gap-2.5">
        <button v-for="(slide, i) in slides" :key="slide.id" type="button" :aria-label="`Go to slide ${i + 1}`" class="h-2 rounded-full transition-all duration-300" :class="current === i ? 'w-8 bg-white' : 'w-2.5 bg-white/40'" @click="goTo(i)" />
      </div>
    </template>
  </section>

  <!-- ========== WELCOME / BACKGROUND ========== -->
  <section v-reveal class="container-wide mt-4">
    <div class="overflow-hidden rounded-2xl bg-white shadow-card ring-1 ring-black/5 transition-shadow duration-300 hover:shadow-card-hover">
      <div class="group grid lg:grid-cols-2 lg:items-start">
        <div class="relative h-56 w-full overflow-hidden rounded-xl bg-brand-navy/5 sm:h-72 lg:m-6 lg:h-80 lg:w-auto">
          <img v-if="!backgroundImageFailed" :src="staticAsset('images/home.jpg')" alt="St. Mark's College Namagoma campus" class="h-full w-full object-cover transition duration-700 group-hover:scale-105" @error="backgroundImageFailed = true">
          <div v-else class="flex h-full items-center justify-center bg-gradient-to-br from-brand-navy to-brand-navy-dark">
            <span class="font-display text-2xl font-extrabold text-white/30">St. Mark's College</span>
          </div>
        </div>

        <div class="flex flex-col justify-center p-8 sm:p-10 lg:p-12">
          <h2 class="font-display text-2xl font-bold text-gray-900 sm:text-3xl">Welcome to St. Mark's College Namagoma</h2>

          <p v-if="!backgroundExpanded" class="mt-4 max-w-2xl text-justify leading-relaxed text-gray-600">{{ backgroundIntro }}</p>
          <div v-else class="mt-4 max-w-2xl space-y-4 text-justify leading-relaxed text-gray-600">
            <p v-for="(para, i) in backgroundParagraphs.slice(0, 2)" :key="i">{{ para }}</p>
          </div>

          <button v-if="!backgroundExpanded" type="button" class="btn mt-6 w-fit" aria-expanded="false" @click="backgroundExpanded = true">
            Read More
          </button>
        </div>
      </div>

      <div v-if="backgroundExpanded" class="border-t border-gray-100 px-8 pb-8 pt-6 sm:px-10 lg:px-12">
        <p v-for="(para, i) in backgroundParagraphs.slice(2)" :key="i" class="text-justify leading-[1.8] text-gray-600">{{ para }}</p>

        <div class="mt-6 flex flex-wrap items-center justify-between gap-4 border-t border-gray-100 pt-6">
          <p class="font-display text-lg font-semibold italic text-brand-navy">
            <span class="mr-1 text-2xl not-italic text-brand-gold">&ldquo;</span>To Be, Not To Seem.<span class="ml-1 text-2xl not-italic text-brand-gold">&rdquo;</span>
          </p>
          <button type="button" class="btn w-fit" aria-expanded="true" @click="backgroundExpanded = false">
            Show Less
          </button>
        </div>
      </div>
    </div>
  </section>

  <!-- ========== SCHOOL LEADERSHIP ========== -->
  <section class="container-wide pb-6 pt-4 sm:pb-8 sm:pt-6">
    <div v-reveal class="mx-auto max-w-2xl text-center">
      <h2 class="font-display text-3xl font-bold text-gray-900">School Leadership</h2>
      <p class="mt-3 text-gray-600">Visionary leaders guiding academic excellence, discipline and holistic student development.</p>
    </div>

    <div class="mt-12 grid gap-8 md:grid-cols-3">
      <div
        v-for="(leader, i) in leadership"
        :key="leader.name"
        class="leadership-float"
        :style="{ animationDelay: `${i * 300}ms` }"
      >
        <article
          v-reveal="i * 120"
          class="leadership-card card-interactive group relative overflow-hidden rounded-2xl bg-white p-6 text-center shadow-card ring-1 ring-black/5 sm:p-8"
          :class="i === 0 ? 'reveal-from-left' : i === leadership.length - 1 ? 'reveal-from-right' : ''"
        >
          <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-brand-navy via-brand-gold to-brand-navy" />
          <div class="relative mx-auto w-fit">
            <div class="absolute inset-0 -z-10 rounded-full bg-brand-gold/20 blur-xl transition-opacity duration-300 group-hover:bg-brand-gold/30" />
            <AvatarImage :src="staticAsset(leader.image)" :alt="leader.name" size="lg" />
          </div>
          <h3 class="mt-4 font-display text-lg font-bold text-gray-900">{{ leader.name }}</h3>
          <span class="mt-2 inline-block rounded-full bg-brand-navy/5 px-3 py-1 text-xs font-bold uppercase tracking-widest text-brand-navy/70">{{ leader.role }}</span>
          <p class="mt-3 text-sm leading-relaxed text-gray-600">{{ leader.text }}</p>
          <router-link :to="leader.url" class="btn btn-gold group/btn mx-auto mt-5 w-fit px-5 py-2 text-xs">
            Read More
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition group-hover/btn:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
          </router-link>
        </article>
      </div>
    </div>
  </section>

  <!-- ========== LATEST NEWS & EVENTS ========== -->
  <section v-if="latestNews.length" class="bg-gray-50 pb-2 pt-4 sm:pb-3 sm:pt-6">
    <div class="container-wide">
      <div v-reveal class="flex flex-wrap items-end justify-between gap-4">
        <div>
          <h2 class="font-display text-3xl font-bold text-gray-900">Latest News &amp; Events</h2>
        </div>
        <router-link to="/news" class="font-semibold text-brand-navy transition hover:underline">View all news &rarr;</router-link>
      </div>

      <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <NewsCard
          v-for="(item, i) in latestNews"
          :key="item.id"
          v-reveal="(i % 3) * 100"
          :url="`/news/${item.slug}`"
          :image="item.image_url"
          :title="item.title"
          :date="item.published_at"
          class="news-card-size h-64 sm:h-72 lg:h-80 2xl:h-96"
        />
      </div>
    </div>
  </section>

  <!-- ========== LATEST UPDATES FROM X (@Smacon24) ========== -->
  <!-- Hidden until the X feed is configured (see the import comment above): <SmaconXFeed /> -->

  <!-- ========== UPCOMING EVENTS ========== -->
  <!-- Key-date cards (white, not navy tiles), auto-scrolling sideways the same way Featured
       Posts does below - a doubled track animated with the shared .marquee-wrap/.marquee-track
       classes (pauses on hover, respects prefers-reduced-motion via the same rule those classes
       already carry), rather than a static grid or a manually-dragged strip. Just the date badge
       + title, so it reads as a quick calendar glance rather than a full agenda. The green tick
       badge uses isPastEvent() (not the raw is_completed flag) - once an event's date has gone
       by, it's shown as done automatically, with no admin action needed; an admin tick in
       /admin/calendar-events still works too (is_completed - checked by isPastEvent() - can mark
       something done early, e.g. same-day). -->
  <section v-if="calendarEvents.length" class="container-wide pb-6 pt-4 sm:pb-8 sm:pt-6">
    <div v-reveal class="card-interactive relative overflow-hidden rounded-2xl bg-white p-6 shadow-card ring-1 ring-black/5 sm:p-8">
      <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-brand-navy via-brand-gold to-brand-navy" />

      <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
          <h2 class="font-display text-xl font-bold text-gray-900 sm:text-2xl">Key dates on the school calendar</h2>
        </div>
        <router-link to="/academics/school-calendar" class="font-semibold text-brand-navy transition hover:underline">View full calendar &rarr;</router-link>
      </div>

      <div class="marquee-wrap relative mt-8 overflow-hidden">
        <div class="marquee-track flex w-max gap-3" :style="{ animationDuration: `${calendarEvents.length * 4}s` }">
          <router-link
            v-for="(event, i) in [...calendarEvents, ...calendarEvents]"
            :key="`${event.id}-${i}`"
            :to="{ path: '/academics/school-calendar', hash: `#event-${event.id}` }"
            class="group relative flex w-28 shrink-0 flex-col items-center gap-2 rounded-xl border border-gray-100 bg-white p-3 text-center shadow-sm transition-all duration-200 hover:-translate-y-1 hover:border-brand-gold/40 hover:shadow-md sm:w-32"
          >
            <div :title="eventFullDate(event.event_date)" class="relative flex w-fit items-baseline gap-1 rounded-md bg-gray-50 px-2 py-1 ring-1 ring-gray-100 transition-colors duration-200 group-hover:bg-brand-gold/5">
              <span class="font-display text-base font-extrabold leading-none text-brand-navy">{{ eventDay(event.event_date) }}</span>
              <span class="text-[12px] font-bold uppercase tracking-widest text-brand-gold">{{ eventMonth(event.event_date) }}</span>
              <span v-if="isPastEvent(event)" class="absolute -right-1.5 -top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-green-500 text-white shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
              </span>
            </div>
            <h3 :title="event.title" class="line-clamp-2 font-display text-xs font-bold text-gray-900 transition-colors group-hover:text-brand-navy">{{ event.title }}</h3>
          </router-link>
        </div>
      </div>
    </div>
  </section>

  <!-- ========== FEATURED POSTS ========== -->
  <section v-if="featuredPosts.length" v-reveal class="container-wide pb-6 pt-0 sm:pb-8">
    <div class="rounded-2xl bg-gradient-to-br from-brand-navy/5 to-brand-gold/5 p-6 ring-1 ring-brand-navy/10 sm:p-10">
      <h2 class="font-display text-2xl font-bold text-gray-900 sm:text-3xl">Featured Posts</h2>

      <div class="marquee-wrap relative mt-8 overflow-hidden">
        <div class="marquee-track flex w-max gap-6" :style="{ animationDuration: `${featuredPosts.length * 4}s` }">
          <router-link v-for="(post, i) in [...featuredPosts, ...featuredPosts]" :key="`${post.id}-${i}`" :to="`/news/${post.slug}`" class="group block w-36 shrink-0 sm:w-48">
            <div class="relative h-36 overflow-hidden rounded-xl bg-white shadow-card ring-1 ring-black/5 sm:h-40">
              <img
                v-if="post.image_url && !postImageFailed[post.id]"
                :src="post.image_url"
                :alt="post.title"
                loading="lazy"
                decoding="async"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-110"
                @error="postImageFailed[post.id] = true"
              >
              <div v-else class="flex h-full w-full items-center justify-center bg-brand-navy/5">
                <span class="font-display text-2xl font-extrabold text-brand-navy/15">SM</span>
              </div>
            </div>
            <h3 class="mt-3 line-clamp-2 font-display text-sm font-bold text-gray-900 transition group-hover:text-brand-navy">{{ post.title }}</h3>
          </router-link>
        </div>
      </div>
    </div>
  </section>

  <!-- ========== MOTTO / CORE VALUES / WHY CHOOSE ========== -->
  <section class="bg-gradient-to-b from-brand-navy/[0.03] to-white py-6 sm:py-8">
    <div class="container-wide">
      <div class="grid gap-8 md:grid-cols-3">
        <div v-reveal class="card-interactive group relative flex flex-col overflow-hidden rounded-2xl bg-white p-6 shadow-card ring-1 ring-black/5 duration-500 hover:-translate-y-2 hover:scale-[1.02] hover:shadow-2xl hover:ring-2 hover:ring-brand-gold/40 sm:p-8">
          <span class="pointer-events-none absolute -right-4 -top-6 font-display text-8xl font-black text-brand-navy/[0.04]">&ldquo;</span>
          <h3 class="relative font-display text-xl font-bold text-gray-900">The College Motto</h3>
          <p class="relative mt-3 text-lg font-semibold italic text-brand-navy">"To Be, Not To Seem"</p>
          <p class="relative mt-4 flex-1 text-justify text-sm leading-[1.8] text-gray-600">The motto reflects our founders' desire to train students with strong values that guide them through life, encouraging authenticity, integrity and inner strength over outward appearances. It challenges every learner at St. Mark's College Namagoma to build genuine character rather than a polished image, so that who they are in private matches who they present to the world. This conviction shapes our approach to discipline, academics and pastoral care alike.</p>
          <a href="https://espace.stmark.sc.ug/" target="_blank" rel="noopener" class="btn btn-gold group/btn relative mt-6 w-fit">
            Visit eSpace
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition group-hover/btn:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
          </a>
        </div>

        <div v-reveal="120" class="card-interactive flex flex-col rounded-2xl bg-white p-6 shadow-card ring-1 ring-black/5 duration-500 hover:-translate-y-2 hover:scale-[1.02] hover:shadow-2xl hover:ring-2 hover:ring-brand-gold/40 sm:p-8">
          <h3 class="text-center font-display text-xl font-bold text-gray-900">Core Values (GREET)</h3>
          <div class="mt-6 flex-1 space-y-4">
            <div v-for="(value, i) in coreValues" :key="`${value.letter}-${i}`" class="group flex items-start gap-4">
              <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-navy font-display font-bold text-white shadow-sm transition duration-300 group-hover:scale-110 group-hover:bg-brand-gold group-hover:text-brand-navy-dark">{{ value.letter }}</span>
              <div>
                <p class="font-bold text-gray-900">{{ value.title }}</p>
                <p class="text-justify text-sm leading-[1.8] text-gray-600">{{ value.text }}</p>
              </div>
            </div>
          </div>
        </div>

        <div v-reveal="240" class="card-interactive group relative flex flex-col overflow-hidden rounded-2xl bg-white p-6 shadow-card ring-1 ring-black/5 duration-500 hover:-translate-y-2 hover:scale-[1.02] hover:shadow-2xl hover:ring-2 hover:ring-brand-gold/40 sm:p-8">
          <span class="pointer-events-none absolute -right-4 -top-6 font-display text-8xl font-black text-brand-gold/[0.06]">✓</span>
          <h3 class="relative font-display text-xl font-bold text-gray-900">Why Choose St. Mark's?</h3>
          <p class="relative mt-4 text-justify text-sm leading-[1.8] text-gray-600">St. Mark's College Namagoma promotes education and excellence with particular focus on each student, academically, spiritually and morally.</p>
          <p class="relative mt-3 flex-1 text-justify text-sm leading-[1.8] text-gray-600">Students benefit from modern facilities and a serene learning environment, dedicated teachers, and preparation for leadership and responsible citizenship. Small class sizes and attentive mentorship ensure no learner is left behind, while a vibrant co-curricular program builds confidence beyond the classroom.</p>
          <router-link to="/admissions" class="btn group/btn relative mt-6 w-fit">
            Visit Admissions
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition group-hover/btn:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
          </router-link>
        </div>
      </div>
    </div>
  </section>

  <!-- ========== MEDIA SHOWCASE ========== -->
  <!-- One grid holding two independent pieces fed by /admin/media, side by side in the same row(s)
       rather than stacked sections: an autoplaying image slideshow (same shape as the hero slider
       - fade cross-transition, arrows, dots, swipe) sharing its row with standalone video/
       YouTube-link cards that don't rotate. All cards share the same fixed height per breakpoint
       so they line up evenly; extra video cards beyond the first wrap to further rows. Each
       slide/card's title and caption (admin-entered "text") render as an overlay over a gradient
       so they stay legible on any photo or thumbnail. -->
  <section v-if="mediaImages.length || mediaVideos.length" class="bg-gray-50 py-6 sm:py-8">
    <div class="container-wide">
      <div class="grid grid-cols-1 gap-6" :class="mediaGridCols">
        <div
          v-if="mediaImages.length"
          v-reveal="120"
          class="media-showcase relative h-72 overflow-hidden rounded-2xl shadow-card-hover ring-1 ring-black/5 sm:h-96 lg:h-[480px] 2xl:h-[560px]"
          @mouseenter="mediaStop"
          @mouseleave="mediaPlay"
          @touchstart.passive="mediaTouchStart"
          @touchend.passive="mediaTouchEnd"
        >
          <div
            v-for="(item, i) in mediaImages"
            :key="item.id"
            class="absolute inset-0 transition-opacity duration-700 ease-in-out"
            :class="mediaCurrent === i ? 'z-10 opacity-100' : 'z-0 opacity-0'"
          >
            <img
              v-if="item.file_url && !mediaImageFailed[item.id]"
              :src="item.file_url"
              :alt="item.title || `St. Mark's College media`"
              loading="lazy"
              decoding="async"
              class="h-full w-full object-cover"
              :class="mediaCurrent === i ? 'hero-zoom' : ''"
              @error="mediaImageFailed[item.id] = true"
            >
            <div v-else class="h-full w-full bg-gradient-to-br from-brand-navy to-brand-navy-dark" />

            <div v-if="item.title || item.caption" class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent" />
          </div>

          <!-- The caption text lives outside the per-slide cross-fade above, as one instance bound
               to whichever slide is current - a plain opacity cross-fade felt instant/flat for
               text, so instead each new slide's caption gets its own slow, deliberate entrance:
               it slides in from the right over ~1.6s once the previous slide's caption has faded
               out, rather than all captions existing at once and just swapping visibility. -->
          <Transition name="caption-slide" mode="out-in">
            <div
              v-if="mediaImages[mediaCurrent] && (mediaImages[mediaCurrent].title || mediaImages[mediaCurrent].caption)"
              :key="mediaImages[mediaCurrent].id"
              class="pointer-events-none absolute inset-x-0 bottom-0 z-20 p-6 sm:p-9"
            >
              <h3 v-if="mediaImages[mediaCurrent].title" class="font-display text-xl font-bold leading-tight text-white drop-shadow-md sm:text-3xl">{{ mediaImages[mediaCurrent].title }}</h3>
              <p v-if="mediaImages[mediaCurrent].caption" class="mt-2 max-w-2xl text-sm leading-relaxed text-white/85 sm:text-base">{{ mediaImages[mediaCurrent].caption }}</p>
            </div>
          </Transition>

          <template v-if="mediaImages.length > 1">
            <button type="button" aria-label="Previous" class="absolute left-4 top-1/2 z-30 -translate-y-1/2 rounded-full bg-white/15 p-2.5 text-white backdrop-blur-md transition hover:bg-white/25 sm:p-3" @click="mediaGoPrevAndReset">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
            </button>
            <button type="button" aria-label="Next" class="absolute right-4 top-1/2 z-30 -translate-y-1/2 rounded-full bg-white/15 p-2.5 text-white backdrop-blur-md transition hover:bg-white/25 sm:p-3" @click="mediaGoNextAndReset">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
            </button>
            <div class="absolute bottom-6 right-6 z-30 flex gap-2.5">
              <button v-for="(item, i) in mediaImages" :key="item.id" type="button" :aria-label="`Go to slide ${i + 1}`" class="h-2 rounded-full transition-all duration-300" :class="mediaCurrent === i ? 'w-8 bg-white' : 'w-2.5 bg-white/40'" @click="mediaGoTo(i)" />
            </div>
          </template>
        </div>

        <!-- Standalone video/YouTube cards - each its own tile, not part of the slideshow rotation -->
        <div v-for="item in mediaVideos" :key="item.id" v-reveal class="card-interactive group relative h-72 overflow-hidden rounded-2xl bg-black shadow-card ring-1 ring-black/5 sm:h-96 lg:h-[480px] 2xl:h-[560px]">
          <!-- Uploaded video file: autoplays muted/looping like the hero slider's video slides (browsers block audible autoplay, so mute is required for it to play at all) -->
          <video
            v-if="item.type === 'video' && item.file_url && !mediaImageFailed[item.id]"
            :src="item.file_url"
            autoplay
            muted
            loop
            playsinline
            controls
            class="h-full w-full object-cover"
            @error="mediaImageFailed[item.id] = true"
          />

          <!-- YouTube link: embeds directly and autoplays muted/looping, same as the uploaded-video case above -->
          <iframe
            v-else-if="item.type === 'link' && item.video_url && youTubeId(item.video_url)"
            :src="youTubeEmbed(item.video_url)"
            class="h-full w-full"
            frameborder="0"
            allow="autoplay; encrypted-media"
            allowfullscreen
          />

          <!-- Non-YouTube external link: no embeddable player available, so a plain open-in-new-tab tile -->
          <a v-else-if="item.type === 'link' && item.video_url" :href="item.video_url" target="_blank" rel="noopener" class="flex h-full w-full flex-col items-center justify-center gap-2 bg-gradient-to-br from-brand-navy to-brand-navy-dark text-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
            <span class="text-sm font-semibold">Watch Video</span>
          </a>

          <div v-if="item.title || item.caption" class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/85 via-black/10 to-transparent" />
          <div v-if="item.title || item.caption" class="media-caption-loop pointer-events-none absolute inset-x-0 bottom-0 p-4 sm:p-5">
            <h3 v-if="item.title" class="line-clamp-1 font-display text-base font-bold leading-tight text-white drop-shadow-md">{{ item.title }}</h3>
            <p v-if="item.caption" class="mt-1 line-clamp-2 text-xs text-white/85 sm:text-sm">{{ item.caption }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ========== MISSION & VISION ========== -->
  <section class="container-wide py-6 sm:py-8">
    <div class="grid gap-6 sm:grid-cols-2">
      <article v-reveal class="card-interactive group relative overflow-hidden rounded-2xl border-t-4 border-brand-navy bg-white p-6 shadow-card ring-1 ring-black/5 sm:p-8">
        <div class="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full bg-brand-navy/5 transition-transform duration-500 group-hover:scale-125" />
        <div class="relative flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-navy to-brand-navy-dark text-2xl text-white shadow-md transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">🎯</div>
        <h3 class="relative mt-5 font-display text-xl font-bold text-gray-900">Our Mission</h3>
        <p class="relative mt-3 text-sm leading-relaxed text-gray-600">To provide top-quality secondary education that nurtures in our students a zest for life, a spirit of enterprise, community service and leadership, through a balanced curriculum that prepares them for an ever-changing world.</p>
      </article>
      <article v-reveal="120" class="card-interactive group relative overflow-hidden rounded-2xl border-t-4 border-brand-gold bg-white p-6 shadow-card ring-1 ring-black/5 sm:p-8">
        <div class="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full bg-brand-gold/10 transition-transform duration-500 group-hover:scale-125" />
        <div class="relative flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-gold to-yellow-400 text-2xl text-brand-navy-dark shadow-md transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">🌍</div>
        <h3 class="relative mt-5 font-display text-xl font-bold text-gray-900">Our Vision</h3>
        <p class="relative mt-3 text-sm leading-relaxed text-gray-600">To be a leading academic institution in Uganda and the East African region, producing highly successful and respected individuals in every aspect of life.</p>
      </article>
    </div>
  </section>

  <!-- ========== CAMPUS VOICES ========== -->
  <section v-if="voices.length" v-reveal class="container-wide py-6 sm:py-8">
    <div class="rounded-3xl bg-gradient-to-br from-brand-navy/[0.04] to-brand-gold/[0.04] p-6 ring-1 ring-black/5 sm:p-8">
      <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
          <h2 class="font-display text-3xl font-bold text-gray-900">Campus Voices</h2>
        </div>
        <router-link to="/campus-voices" class="group flex items-center gap-1 font-semibold text-brand-navy transition hover:underline">
          View all voices
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
        </router-link>
      </div>

      <div class="voice-wrap grid gap-6 lg:grid-cols-3" @mouseenter="voiceStop" @mouseleave="voicePlay">
        <!-- Featured slide. Height grows at the same breakpoints container-wide itself widens
             at (2xl=1536px, and the custom 1920px step below) so the box's aspect ratio stays
             roughly constant instead of getting wider-and-shorter (crushing portrait photos) as
             the screen grows - see container-wide's own comment in style.css. -->
        <div class="voice-hero relative h-80 overflow-hidden rounded-2xl shadow-card-hover ring-1 ring-black/5 lg:col-span-2 lg:h-[420px] 2xl:h-[540px]">
          <router-link
            v-for="(voice, idx) in voices"
            :key="voice.id"
            :to="`/campus-voices/${voice.slug}`"
            class="absolute inset-0 transition-opacity duration-700 ease-in-out"
            :class="idx === voiceCurrent ? 'z-10 opacity-100' : 'z-0 opacity-0'"
          >
            <img
              v-if="voice.featured_image_url && !voiceImageFailed[voice.id]"
              :src="voice.featured_image_url"
              :alt="voice.title"
              class="h-full w-full object-cover object-top"
              :class="idx === voiceCurrent ? 'hero-zoom' : ''"
              @error="voiceImageFailed[voice.id] = true"
            >
            <div v-else class="h-full w-full bg-gradient-to-br from-brand-navy to-brand-navy-dark" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-black/5" />

            <svg class="pointer-events-none absolute left-5 top-5 h-14 w-14 text-white/10 sm:left-7 sm:top-7 sm:h-20 sm:w-20" fill="currentColor" viewBox="0 0 32 32"><path d="M9.4 8C5.3 10.4 3 13.8 3 18.1c0 4.3 2.8 7.2 6.4 7.2 3 0 5.3-2.2 5.3-5.2 0-2.8-2-4.9-4.6-4.9-.5 0-1 .1-1.3.2.4-2.8 3-5.6 5.9-7.1L9.4 8Zm14 0c-4.1 2.4-6.4 5.8-6.4 10.1 0 4.3 2.8 7.2 6.4 7.2 3 0 5.3-2.2 5.3-5.2 0-2.8-2-4.9-4.6-4.9-.5 0-1 .1-1.3.2.4-2.8 3-5.6 5.9-7.1L23.4 8Z" /></svg>

            <div class="absolute inset-0 flex flex-col justify-end p-6 sm:p-9">
              <span class="w-fit rounded-full bg-brand-gold px-3 py-1 text-xs font-bold uppercase tracking-wide text-brand-navy-dark shadow-sm">{{ voice.category || 'eVoices' }}</span>
              <h3 class="mt-4 line-clamp-2 font-display text-2xl font-bold leading-tight text-white drop-shadow-md sm:text-4xl">{{ voice.title }}</h3>
              <p v-if="voice.summary" class="mt-3 line-clamp-2 max-w-2xl text-sm leading-relaxed text-white/80 sm:text-base">{{ excerptFor(voice.summary, 140) }}</p>
              <div class="mt-4 flex items-center gap-2.5 text-sm font-medium text-white/80">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-white/15 text-xs font-bold text-white backdrop-blur-sm">{{ voice.student_name?.trim().charAt(0).toUpperCase() }}</span>
                <span>{{ voice.student_name }}</span>
                <template v-if="voice.published_at">
                  <span class="text-white/40">&middot;</span>
                  <span class="text-white/60">{{ formatDate(voice.published_at) }}</span>
                </template>
              </div>
            </div>

          </router-link>

          <div v-if="voices.length > 1" class="absolute inset-x-0 bottom-0 z-20 h-1 bg-white/10">
            <div :key="voiceCurrent" class="voice-progress-bar h-full bg-brand-gold" />
          </div>

          <div v-if="voices.length > 1" class="absolute bottom-5 right-6 z-20 flex gap-2 sm:right-8">
            <button v-for="(voice, idx) in voices" :key="voice.id" type="button" :aria-label="`Show voice ${idx + 1}`" class="h-2 rounded-full transition-all duration-300" :class="idx === voiceCurrent ? 'w-6 bg-white' : 'w-2 bg-white/40 hover:bg-white/70'" @click="voiceGoTo(idx)" />
          </div>
        </div>

        <!-- Up-next queue -->
        <div class="flex max-w-md flex-col gap-4">
          <span class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-brand-navy/40">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
            Up Next
          </span>

          <Transition name="voice-fade" mode="out-in">
            <router-link v-if="voiceQueue[0] !== undefined" :key="voices[voiceQueue[0]].id" :to="`/campus-voices/${voices[voiceQueue[0]].slug}`" class="card-interactive group flex flex-1 items-center gap-4 rounded-xl bg-white p-4 shadow-card ring-1 ring-black/5">
              <div class="h-20 w-28 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                <img v-if="voices[voiceQueue[0]].featured_image_url && !voiceImageFailed[voices[voiceQueue[0]].id]" :src="voices[voiceQueue[0]].featured_image_url" :alt="voices[voiceQueue[0]].title" loading="lazy" decoding="async" class="h-full w-full object-cover object-top transition duration-500 group-hover:scale-110" @error="voiceImageFailed[voices[voiceQueue[0]].id] = true">
              </div>
              <div class="min-w-0">
                <h4 class="line-clamp-2 font-display text-sm font-bold leading-snug text-gray-900 transition group-hover:text-brand-navy">{{ voices[voiceQueue[0]].title }}</h4>
                <p v-if="voices[voiceQueue[0]].summary" class="mt-1.5 line-clamp-2 text-xs text-gray-600">{{ excerptFor(voices[voiceQueue[0]].summary) }}</p>
                <p class="mt-2 text-xs text-gray-400">{{ voices[voiceQueue[0]].student_name }}</p>
              </div>
            </router-link>
          </Transition>

          <Transition name="voice-fade" mode="out-in">
            <router-link v-if="voiceQueue[1] !== undefined" :key="voices[voiceQueue[1]].id" :to="`/campus-voices/${voices[voiceQueue[1]].slug}`" class="card-interactive group flex flex-1 items-center gap-4 rounded-xl bg-white p-4 shadow-card ring-1 ring-black/5">
              <div class="h-20 w-28 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                <img v-if="voices[voiceQueue[1]].featured_image_url && !voiceImageFailed[voices[voiceQueue[1]].id]" :src="voices[voiceQueue[1]].featured_image_url" :alt="voices[voiceQueue[1]].title" loading="lazy" decoding="async" class="h-full w-full object-cover object-top transition duration-500 group-hover:scale-110" @error="voiceImageFailed[voices[voiceQueue[1]].id] = true">
              </div>
              <div class="min-w-0">
                <h4 class="line-clamp-2 font-display text-sm font-bold leading-snug text-gray-900 transition group-hover:text-brand-navy">{{ voices[voiceQueue[1]].title }}</h4>
                <p v-if="voices[voiceQueue[1]].summary" class="mt-1.5 line-clamp-2 text-xs text-gray-600">{{ excerptFor(voices[voiceQueue[1]].summary) }}</p>
                <p class="mt-2 text-xs text-gray-400">{{ voices[voiceQueue[1]].student_name }}</p>
              </div>
            </router-link>
          </Transition>

          <Transition name="voice-fade" mode="out-in">
            <router-link v-if="voiceQueue[2] !== undefined" :key="voices[voiceQueue[2]].id" :to="`/campus-voices/${voices[voiceQueue[2]].slug}`" class="card-interactive group flex flex-1 items-center gap-4 rounded-xl bg-white p-4 shadow-card ring-1 ring-black/5">
              <div class="h-20 w-28 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                <img v-if="voices[voiceQueue[2]].featured_image_url && !voiceImageFailed[voices[voiceQueue[2]].id]" :src="voices[voiceQueue[2]].featured_image_url" :alt="voices[voiceQueue[2]].title" loading="lazy" decoding="async" class="h-full w-full object-cover object-top transition duration-500 group-hover:scale-110" @error="voiceImageFailed[voices[voiceQueue[2]].id] = true">
              </div>
              <div class="min-w-0">
                <h4 class="line-clamp-2 font-display text-sm font-bold leading-snug text-gray-900 transition group-hover:text-brand-navy">{{ voices[voiceQueue[2]].title }}</h4>
                <p v-if="voices[voiceQueue[2]].summary" class="mt-1.5 line-clamp-2 text-xs text-gray-600">{{ excerptFor(voices[voiceQueue[2]].summary) }}</p>
                <p class="mt-2 text-xs text-gray-400">{{ voices[voiceQueue[2]].student_name }}</p>
              </div>
            </router-link>
          </Transition>
        </div>
      </div>
    </div>
  </section>

  <!-- ========== MEET OUR DEDICATED TEAM ========== -->
  <section v-if="teamStaff.length" class="container-wide pb-6 pt-0 sm:pb-8">
    <div v-reveal class="mx-auto max-w-2xl text-center">
      <h2 class="font-display text-3xl font-bold text-gray-900">Meet Our Dedicated Team</h2>
      <p class="mt-3 text-gray-600">The educators and staff shaping every student's journey at St. Mark's College Namagoma.</p>
    </div>

    <Transition name="staff-blur" mode="out-in">
      <div :key="teamBase" class="team-grid mt-10 grid grid-cols-2 gap-6 sm:grid-cols-4">
        <div
          v-for="(member, i) in teamCards"
          :key="member.id"
          class="team-card-float"
          :style="{ animationDelay: `${i * 250}ms` }"
        >
          <router-link
            :to="{ path: '/staff', hash: `#staff-${member.id}` }"
            class="card-interactive group relative block overflow-hidden rounded-2xl bg-white p-6 text-center shadow-card ring-1 ring-black/5 duration-500 hover:-translate-y-2 hover:scale-[1.02] hover:shadow-2xl hover:ring-2 hover:ring-brand-gold/40"
          >
            <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-brand-navy via-brand-gold to-brand-navy" />
            <div class="relative mx-auto w-fit">
              <div class="absolute inset-0 -z-10 rounded-full bg-brand-gold/20 blur-xl transition-opacity duration-300 group-hover:bg-brand-gold/30" />
              <div class="mx-auto h-20 w-20 overflow-hidden rounded-full border-2 border-brand-navy/10 shadow-md sm:h-24 sm:w-24">
                <img
                  v-if="!teamCardImageFailed[member.id]"
                  :src="member.photo_url"
                  :alt="member.name"
                  loading="lazy"
                  decoding="async"
                  class="h-full w-full object-cover object-top transition duration-500 group-hover:scale-110"
                  @error="teamCardImageFailed[member.id] = true"
                >
                <div v-else class="flex h-full w-full items-center justify-center bg-brand-navy/10 text-2xl font-bold text-brand-navy">{{ member.name.trim().charAt(0).toUpperCase() }}</div>
              </div>
            </div>
            <h3 class="relative mt-4 font-display text-base font-bold text-gray-900">{{ member.name }}</h3>
            <span class="relative mt-1 inline-block rounded-full bg-brand-gold/10 px-3 py-1 text-xs font-bold uppercase tracking-wide text-brand-navy">{{ member.department }}</span>
          </router-link>
        </div>
      </div>
    </Transition>
  </section>

  <!-- ========== RELATED POSTS ========== -->
  <section v-if="relatedPosts.length" class="container-wide py-6 sm:py-8">
    <h2 v-reveal class="font-display text-2xl font-bold text-gray-900 sm:text-3xl">Articles You May Have Missed</h2>

    <div class="mt-8 grid gap-4 sm:grid-cols-2">
      <router-link v-for="(post, i) in relatedPosts" :key="post.id" v-reveal="(i % 6) * 60" :to="`/news/${post.slug}`" class="card-interactive group flex items-start gap-4 rounded-xl bg-white p-4 shadow-card ring-1 ring-black/5">
        <div class="h-20 w-28 shrink-0 overflow-hidden rounded-lg bg-gray-100">
          <img v-if="post.image_url && !relatedImageFailed[post.id]" :src="post.image_url" :alt="post.title" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-500 group-hover:scale-110" @error="relatedImageFailed[post.id] = true">
        </div>
        <div class="min-w-0">
          <h3 class="line-clamp-2 font-display text-sm font-bold leading-snug text-gray-900">{{ post.title }}</h3>
          <p v-if="post.excerpt" class="mt-1.5 line-clamp-2 text-xs text-gray-600">{{ excerptFor(post.excerpt) }}</p>
          <p v-if="post.published_at" class="mt-2 text-xs text-gray-400">{{ formatDate(post.published_at) }}</p>
        </div>
      </router-link>
    </div>
  </section>
</template>

<style scoped>
/* School Leadership: the two outer cards slide in from their respective sides instead of the
   sitewide v-reveal fade-up (style.css), settling into their fixed grid position with a bouncy
   overshoot - the same cubic-bezier(0.34, 1.56, 0.64, 1) curve
   for the entrance, just stretched to 1.8s so it reads as slow motion rather
   than a quick pop. Higher specificity than the global .reveal/.reveal.revealed (two classes vs.
   these three, plus scoped CSS's own data-v-* attribute) is what lets this override it without
   touching v-reveal's behavior anywhere else on the site. */
.leadership-card.reveal-from-left,
.leadership-card.reveal-from-right {
  transition: opacity 1.8s cubic-bezier(0.34, 1.56, 0.64, 1), transform 1.8s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.leadership-card.reveal-from-left {
  transform: translateX(-90px);
}
.leadership-card.reveal-from-right {
  transform: translateX(90px);
}
.leadership-card.reveal-from-left.revealed,
.leadership-card.reveal-from-right.revealed {
  transform: translateX(0);
}
@media (prefers-reduced-motion: reduce) {
  .leadership-card.reveal-from-left,
  .leadership-card.reveal-from-right {
    transform: none;
  }
}

/* Media Showcase video cards: the title then the caption drop in from the upper right on their
   own staggered timing (caption 0.25s behind the title) rather than the whole block sliding in
   as one flat unit - each overshoots slightly past its resting spot and settles back, the small
   bounce that reads as a "drop" rather than a plain slide. Runs continuously for as long as the
   card is on screen (not the one-shot v-reveal-on-scroll-into-view pattern used elsewhere), since
   the card's video is itself always in motion; both keyframe endpoints match (off-screen,
   invisible) so the loop restarts with no visible jump. */
.media-caption-loop h3,
.media-caption-loop p {
  animation: mediaCaptionDropIn 9s cubic-bezier(0.22, 1, 0.36, 1) infinite;
}
.media-caption-loop p {
  animation-delay: 0.25s;
}
@keyframes mediaCaptionDropIn {
  0%, 100% {
    opacity: 0;
    transform: translate(90px, -22px) scale(0.96);
  }
  15% {
    opacity: 1;
    transform: translate(-6px, 5px) scale(1.015);
  }
  25%, 80% {
    opacity: 1;
    transform: translate(0, 0) scale(1);
  }
  95% {
    opacity: 0;
  }
}
@media (prefers-reduced-motion: reduce) {
  .media-caption-loop h3,
  .media-caption-loop p {
    animation: none;
    opacity: 1;
    transform: none;
  }
}

/* Media Showcase image slideshow caption: a slow, deliberate slide-in from the right each time the
   current slide changes (mode="out-in" on the Transition above waits for the old caption's quick
   fade-out before this starts, so the two never overlap mid-motion). 1.6s is intentionally much
   slower than the hero slider's own 0.7s cross-fade - the caption is meant to read as its own
   considered entrance, not just follow the image swap. */
.caption-slide-enter-active {
  transition: opacity 1.6s cubic-bezier(0.22, 1, 0.36, 1), transform 1.6s cubic-bezier(0.22, 1, 0.36, 1);
}
.caption-slide-leave-active {
  transition: opacity 0.25s ease;
}
.caption-slide-enter-from {
  opacity: 0;
  transform: translateX(140px);
}
.caption-slide-leave-to {
  opacity: 0;
}
@media (prefers-reduced-motion: reduce) {
  .caption-slide-enter-active,
  .caption-slide-leave-active {
    transition: none;
  }
}

/* Continuous gentle bob, always running (not just on scroll-into-view) - lives on a wrapper
   around the card rather than the card itself so it doesn't fight over `transform` with either
   the reveal-from-left/right entrance above or .card-interactive's own hover:-translate-y-1
   (style.css): the wrapper's float and the card's own transform compose instead of one
   overriding the other. animationDelay is set per-card inline in the template (i * 300ms) so
   the three don't bob in lockstep. */
.leadership-float {
  animation: leadershipFloat 4.5s ease-in-out infinite alternate;
}
@keyframes leadershipFloat {
  from {
    transform: translateY(0);
  }
  to {
    transform: translateY(-10px);
  }
}
@media (prefers-reduced-motion: reduce) {
  .leadership-float {
    animation: none;
  }
}

/* Same wrapper-based continuous bob as .leadership-float above, kept as its own class/keyframes
   (rather than shared) so Meet Our Dedicated Team's motion can be tuned independently later
   without touching School Leadership. Lives on a wrapper around the router-link for the same
   reason: it shouldn't fight over `transform` with the link's own hover lift/scale. */
.team-card-float {
  animation: teamCardFloat 4.5s ease-in-out infinite alternate;
}
@keyframes teamCardFloat {
  from {
    transform: translateY(0);
  }
  to {
    transform: translateY(-10px);
  }
}
@media (prefers-reduced-motion: reduce) {
  .team-card-float {
    animation: none;
  }
}

/* Matches container-wide's own custom 1920px breakpoint (style.css) where it stops capping at
   1800px and grows to 96vw - without this, the featured slide would get proportionally wider
   than tall again on 27"+ monitors even with the 2xl height bump above. */
@media (min-width: 1920px) {
  .voice-hero {
    height: 640px;
  }
  .news-card-size {
    height: 460px;
  }
  /* 27"+ monitors get a 5th team card - visibleTeamCount in script setup matches this same
     1920px threshold so the grid never shows an empty 5th slot below it. */
  .team-grid {
    grid-template-columns: repeat(5, minmax(0, 1fr));
  }
}

/* Anchored at the top (not center) so zooming in crops extra from the bottom of the frame
   instead of pushing subjects' heads out of view - a plain center-origin zoom clips faces on
   any portrait shot where the head sits in the upper part of the image. Single-direction and
   `forwards` (no alternate/infinite) so it settles at the zoomed-in state instead of breathing
   in and out - each slide gets a fresh zoom-in when it becomes active again, since the class is
   removed while a slide is inactive and re-added when its turn comes back around. */
.hero-zoom {
  transform-origin: 50% 0%;
  animation: heroZoom 9s ease-out forwards;
}
@keyframes heroZoom {
  from {
    transform: scale(1);
  }
  to {
    transform: scale(1.12);
  }
}

.marquee-track {
  animation: marquee 22s linear infinite;
}
.marquee-wrap:hover .marquee-track {
  animation-play-state: paused;
}

/* Matches voicePlay()'s 9000ms interval (itself matching hero-zoom's 9s duration so the zoom
   finishes before the slide changes) - re-keyed by voiceCurrent in the template so it restarts
   fresh from 0% every time the featured slide advances. */
.voice-progress-bar {
  animation: voiceProgress 9s linear forwards;
}
.voice-wrap:hover .voice-progress-bar {
  animation-play-state: paused;
}

@media (prefers-reduced-motion: reduce) {
  .hero-zoom,
  .marquee-track,
  .voice-progress-bar {
    animation: none;
  }
  .staff-blur-enter-active,
  .staff-blur-leave-active {
    transition: none;
  }
}

@keyframes marquee {
  from {
    transform: translateX(0);
  }
  to {
    transform: translateX(-50%);
  }
}

@keyframes voiceProgress {
  from {
    width: 0%;
  }
  to {
    width: 100%;
  }
}

.voice-fade-enter-active,
.voice-fade-leave-active {
  transition: opacity 0.4s ease, transform 0.4s ease;
}
.voice-fade-enter-from {
  opacity: 0;
  transform: translateY(10px);
}
.voice-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* Slow, deliberate cross-fade (matches teamPlay's 9000ms interval, which is long enough for
   this 2.2s transition to fully settle before the next tick fires) rather than a quick swap. */
.staff-blur-enter-active,
.staff-blur-leave-active {
  transition: opacity 2.2s ease, filter 2.2s ease, transform 2.2s ease;
}
.staff-blur-enter-from {
  opacity: 0;
  filter: blur(8px);
  transform: scale(1.15);
}
.staff-blur-leave-to {
  opacity: 0;
  filter: blur(8px);
  transform: scale(0.85);
}
</style>
