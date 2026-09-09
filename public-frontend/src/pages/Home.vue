<script setup lang="ts">
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue'
import { useHead } from '@unhead/vue'
import { api } from '../services/api'
import { staticAsset } from '../utils/staticAsset'
import AvatarImage from '../components/AvatarImage.vue'

useHead({
  title: null,
  meta: [{ name: 'description', content: "St Mark's College Namagoma - The High Achiever's College." }],
})

const [slidesRes, mediaRes, newsRes, campusVoicesRes] = await Promise.all([
  api.get('/slides'),
  api.get('/media'),
  api.get('/news', { params: { limit: 100 } }),
  api.get('/campus-voices'),
])

const slides = (slidesRes.data.data.slides as Array<Record<string, any>>).filter((s) => s.image_url || s.video_url)
const mediaItems = mediaRes.data.data.media as Array<Record<string, any>>
const newsItems = newsRes.data.data.news as Array<Record<string, any>>
const voices = (campusVoicesRes.data.data.articles as Array<Record<string, any>>).filter((v) => v.featured_image_url)

const latestNews = newsItems.slice(0, 6)
// Every published article, not just a handful - the marquee has enough real variety that its
// loop point is never obvious (see the doubled-track technique in the template below).
const featuredPosts = newsItems
const relatedPosts = newsItems.slice(5, 17)
const mediaToShow = mediaItems.filter((item) => ['image', 'link', 'video'].includes(item.type)).slice(0, 2)

const leadership = [
  { name: 'Owek Ddamulira Daniel', role: 'Director', image: 'images/director1.jpg', url: '/director1', text: 'A visionary education leader committed to institutional growth, integrity and academic excellence.' },
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

// -- eCampus innovations dock: hovering the floating launcher throws big cards out onto an arc
// reaching toward the centre of the screen, one at a time via a JS timer chain (not a CSS-only
// stagger) - each is a real link straight to its page in the same tab, no modal/new window.
const revealedDockCount = ref(0)
let dockTimer: ReturnType<typeof setTimeout> | null = null

// Real, separate systems hosted under stmark.sc.ug (not routes in this SPA) - hence plain <a>
// links with target="_blank" in the template below, not router-link.
const innovations = [
  { icon: '📚', title: 'eLearning', text: 'Lessons and resources, anytime.', url: 'https://stmark.sc.ug/elearning/' },
  { icon: '🗳️', title: 'eVoting System', text: 'Secure student leadership elections.', url: 'https://stmark.sc.ug/votesystem/index.php' },
  { icon: '🛡️', title: 'Cyber Monitor', text: 'Keeping our digital campus safe.', url: 'https://stmark.sc.ug/laptop_tracking_system/auth/login.php' },
  { icon: '🎶', title: 'eConcerting', text: 'Livestreamed school concerts.', url: 'https://stmark.sc.ug/concerting/' },
  { icon: '🎓', title: 'Admissions', text: 'Apply online in minutes.', url: 'https://stmark.sc.ug/stmarks_admission/' },
]

// Places card i on an arc around the launcher button, sweeping clockwise from upper-left
// (index 0) toward the right (the last index) - the button sits in the bottom-left corner, so
// that quadrant is the only one that stays on-screen. Revealing index 0 first and counting up
// means the fan visibly rotates clockwise as more cards appear. The angle range stops short of
// a true 0-90 quarter circle: at exactly "straight up" a wide card's centre sits right at the
// screen edge and clips off it, and at exactly "straight right" its bottom edge can clip the
// viewport floor - MIN/MAX_ANGLE keep every card's centre far enough from the button to leave
// half its width/height on-screen at any card size this dock uses. The radius is generous (and
// the angle range wide) specifically so the chord distance between neighbouring cards clears
// their width - a tight radius with wide cards packs them close enough to overlap.
const dockRadius = ref(580)
const DOCK_MIN_ANGLE = 5
const DOCK_MAX_ANGLE = 80
function dockItemStyle(index: number) {
  const step = (DOCK_MAX_ANGLE - DOCK_MIN_ANGLE) / (innovations.length - 1)
  const angleDeg = DOCK_MAX_ANGLE - index * step
  const angleRad = (angleDeg * Math.PI) / 180
  const x = Math.cos(angleRad) * dockRadius.value
  const y = -Math.sin(angleRad) * dockRadius.value
  return { transform: `translate(-50%, -50%) translate(${x}px, ${y}px)` }
}
function updateDockRadius() {
  dockRadius.value = window.innerWidth < 640 ? 300 : window.innerWidth < 1024 ? 440 : 580
}

function stopDockReveal() {
  if (dockTimer) {
    clearTimeout(dockTimer)
    dockTimer = null
  }
}

function revealNextDockItem(index: number) {
  revealedDockCount.value = index + 1
  if (index + 1 < innovations.length) {
    dockTimer = setTimeout(() => revealNextDockItem(index + 1), 90)
  }
}

function openDock() {
  if (revealedDockCount.value > 0) return
  stopDockReveal()
  dockTimer = setTimeout(() => revealNextDockItem(0), 60)
}

function closeDock() {
  stopDockReveal()
  revealedDockCount.value = 0
}

function toggleDock() {
  if (revealedDockCount.value > 0) closeDock()
  else openDock()
}

// Cards land up to ~280px from the button across empty gaps (it's a fan, not a solid block), so
// a plain mouseleave-closes-immediately handler would collapse the dock the instant the pointer
// crosses one of those gaps on the way to a card - before a click could ever land. Every
// interactive piece (button + each card) shares this same grace timer: leaving one schedules a
// close a moment later, but entering any other piece in the meantime cancels it, so the dock
// only actually closes once the pointer has been away from all of them for a beat.
let dockCloseGraceTimer: ReturnType<typeof setTimeout> | null = null

function cancelDockClose() {
  if (dockCloseGraceTimer) {
    clearTimeout(dockCloseGraceTimer)
    dockCloseGraceTimer = null
  }
}

function enterDockZone() {
  cancelDockClose()
  openDock()
}

function scheduleDockClose() {
  cancelDockClose()
  dockCloseGraceTimer = setTimeout(closeDock, 350)
}

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
  if (e.key === 'Escape') closeDock()
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
  updateDockRadius()
  window.addEventListener('resize', updateDockRadius)
})
onBeforeUnmount(() => {
  stop()
  window.removeEventListener('keydown', onKey)
  window.removeEventListener('resize', updateDockRadius)
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
  }, 6000)
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
onBeforeUnmount(() => {
  stopDockReveal()
  cancelDockClose()
})

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
const newsImageFailed = reactive<Record<number, boolean>>({})
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

function youTubeEmbed(url: string): string {
  let id: string | null = null
  if (url.includes('youtu.be/')) id = url.split('youtu.be/')[1]?.split('?')[0] ?? null
  else if (url.includes('youtube.com/watch')) id = new URL(url).searchParams.get('v')
  else if (url.includes('youtube.com/embed/')) id = url.split('embed/')[1]?.split('?')[0] ?? null
  if (!id) return ''
  // autoplay only actually works muted (browsers block audible autoplay); loop on a single
  // video needs playlist set to that same id, or YouTube just stops after one play.
  return `https://www.youtube.com/embed/${id}?autoplay=1&mute=1&loop=1&playlist=${id}&controls=1`
}
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
            :alt="slide.title || `St Mark's College Namagoma`"
            class="h-full w-full object-cover object-top"
            :class="current === i ? 'hero-zoom' : ''"
            @error="slideFailed[slide.id] = true"
          >
          <video
            v-else-if="slide.type === 'video' && slide.video_url && !slideFailed[slide.id]"
            :src="slide.video_url"
            autoplay
            muted
            loop
            playsinline
            class="h-full w-full object-cover object-top"
            @error="slideFailed[slide.id] = true"
          />
          <div v-else class="h-full w-full bg-gradient-to-br from-brand-navy via-brand-navy to-brand-navy-dark" />

          <div class="absolute inset-0 bg-gradient-to-b from-black/20 via-black/10 to-black/60" />

          <div class="absolute inset-0 z-20 flex items-center justify-center px-6 text-center">
            <div class="max-w-3xl transition-all duration-700" :class="current === i ? 'translate-y-0 opacity-100' : 'translate-y-6 opacity-0'">
              <h2 class="font-display text-3xl font-extrabold text-white drop-shadow-lg sm:text-5xl">{{ slide.title || `St Mark's College Namagoma` }}</h2>
              <p class="mx-auto mt-4 max-w-xl text-base text-blue-50/90 sm:text-lg">{{ slide.caption || `The High Achiever's College` }}</p>
            </div>
          </div>
        </div>
      </template>

      <div v-else class="relative flex h-full items-center justify-center overflow-hidden bg-gradient-to-br from-brand-navy via-brand-navy to-brand-navy-dark px-6 text-center">
        <div class="pointer-events-none absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, white 1px, transparent 1px); background-size: 28px 28px;" />
        <div v-reveal class="relative mx-auto max-w-3xl">
          <span class="eyebrow">The High Achiever's College</span>
          <h1 class="mt-4 font-display text-4xl font-extrabold tracking-tight text-white sm:text-6xl">Welcome to St Mark's College Namagoma</h1>
          <p class="mx-auto mt-5 max-w-2xl text-lg text-blue-100">Building disciplined, confident and competent learners — a community where character and academic excellence grow together.</p>
          <div class="mt-9 flex flex-wrap items-center justify-center gap-4">
            <a href="https://stmark.sc.ug/stmarks_admission/" target="_blank" rel="noopener" class="btn btn-gold">Apply Now</a>
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
          <img v-if="!backgroundImageFailed" :src="staticAsset('images/home.jpg')" alt="St Mark's College Namagoma campus" class="h-full w-full object-cover transition duration-700 group-hover:scale-105" @error="backgroundImageFailed = true">
          <div v-else class="flex h-full items-center justify-center bg-gradient-to-br from-brand-navy to-brand-navy-dark">
            <span class="font-display text-2xl font-extrabold text-white/30">St Mark's College</span>
          </div>
        </div>

        <div class="flex flex-col justify-center p-8 sm:p-10 lg:p-12">
          <h2 class="font-display text-2xl font-bold text-gray-900 sm:text-3xl">Welcome to St Mark's College Namagoma</h2>

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
      <article v-for="(leader, i) in leadership" :key="leader.name" v-reveal="i * 120" class="card-interactive card text-center">
        <AvatarImage :src="staticAsset(leader.image)" :alt="leader.name" size="lg" />
        <h3 class="mt-4 font-display text-lg font-bold text-gray-900">{{ leader.name }}</h3>
        <p class="mt-1 text-xs font-bold uppercase tracking-widest text-brand-navy/60">{{ leader.role }}</p>
        <p class="mt-3 text-sm leading-relaxed text-gray-600">{{ leader.text }}</p>
        <router-link :to="leader.url" class="btn btn-gold mx-auto mt-5 w-fit px-5 py-2 text-xs">Read More</router-link>
      </article>
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
        <router-link
          v-for="(item, i) in latestNews"
          :key="item.id"
          v-reveal="(i % 3) * 100"
          :to="`/news/${item.slug}`"
          class="news-card card-interactive group relative block h-64 overflow-hidden rounded-2xl shadow-card ring-1 ring-black/5 sm:h-72 lg:h-80 2xl:h-96"
        >
          <img
            v-if="item.image_url && !newsImageFailed[item.id]"
            :src="item.image_url"
            :alt="item.title"
            class="h-full w-full object-cover object-top transition duration-700 group-hover:scale-110"
            @error="newsImageFailed[item.id] = true"
          >
          <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-brand-navy to-brand-navy-dark" />
          <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent" />
          <div class="absolute inset-0 flex flex-col justify-between p-5">
            <span v-if="item.published_at" class="w-fit rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white backdrop-blur-md">{{ formatDate(item.published_at) }}</span>
            <h3 class="line-clamp-3 font-display text-lg font-bold leading-snug text-white drop-shadow-md">{{ item.title }}</h3>
          </div>
        </router-link>
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
        <div v-reveal class="card-interactive card">
          <h3 class="font-display text-xl font-bold text-gray-900">The College Motto</h3>
          <p class="mt-3 text-lg font-semibold italic text-brand-navy">"To Be, Not To Seem"</p>
          <p class="mt-4 text-justify text-sm leading-[1.8] text-gray-600">The motto reflects our founders' desire to train students with strong values that guide them through life, encouraging authenticity, integrity and inner strength over outward appearances. It challenges every learner at St Mark's College Namagoma to build genuine character rather than a polished image, so that who they are in private matches who they present to the world. This conviction shapes our approach to discipline, academics and pastoral care alike.</p>
          <router-link to="/elearning" class="btn btn-gold group mt-6 w-fit">
            Visit eSpace
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
          </router-link>
        </div>

        <div v-reveal="120" class="card-interactive card">
          <h3 class="text-center font-display text-xl font-bold text-gray-900">Core Values (GREET)</h3>
          <div class="mt-6 space-y-4">
            <div v-for="(value, i) in coreValues" :key="`${value.letter}-${i}`" class="group flex items-start gap-4">
              <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-navy font-display font-bold text-white transition duration-300 group-hover:scale-110 group-hover:bg-brand-gold group-hover:text-brand-navy-dark">{{ value.letter }}</span>
              <div>
                <p class="font-bold text-gray-900">{{ value.title }}</p>
                <p class="text-justify text-sm leading-[1.8] text-gray-600">{{ value.text }}</p>
              </div>
            </div>
          </div>
        </div>

        <div v-reveal="240" class="card-interactive card">
          <h3 class="font-display text-xl font-bold text-gray-900">Why Choose St Mark's?</h3>
          <p class="mt-4 text-justify text-sm leading-[1.8] text-gray-600">St Mark's College Namagoma promotes education and excellence with particular focus on each student, academically, spiritually and morally.</p>
          <p class="mt-3 text-justify text-sm leading-[1.8] text-gray-600">Students benefit from modern facilities and a serene learning environment, dedicated teachers, and preparation for leadership and responsible citizenship. Small class sizes and attentive mentorship ensure no learner is left behind, while a vibrant co-curricular program builds confidence beyond the classroom.</p>
          <router-link to="/admissions" class="btn group mt-6 w-fit">
            Visit Admissions
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
          </router-link>
        </div>
      </div>
    </div>
  </section>

  <!-- ========== MEDIA SHOWCASE ========== -->
  <section v-if="mediaToShow.length" class="bg-gray-50 pb-6 pt-4 sm:pb-8 sm:pt-6">
    <div class="container-wide">
      <div v-reveal class="text-center">
        <span class="eyebrow text-brand-navy">Life at St Mark's</span>
      </div>

      <div class="mt-6 grid gap-6" :class="mediaToShow.length > 1 ? 'sm:grid-cols-2' : ''">
        <div v-for="(item, i) in mediaToShow" :key="item.id" v-reveal="i * 120" class="card-interactive group overflow-hidden rounded-2xl bg-white shadow-card ring-1 ring-black/5">
          <img
            v-if="item.type === 'image' && item.file_url && !mediaImageFailed[item.id]"
            :src="item.file_url"
            :alt="item.title || `St Mark's College media`"
            class="w-full object-cover transition duration-700 group-hover:scale-105"
            @error="mediaImageFailed[item.id] = true"
          >
          <div v-else-if="item.type === 'link' && item.video_url && youTubeEmbed(item.video_url)" class="aspect-video w-full">
            <iframe :src="youTubeEmbed(item.video_url)" class="h-full w-full" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen />
          </div>
          <video
            v-else-if="item.type === 'video' && item.file_url && !mediaImageFailed[item.id]"
            :src="item.file_url"
            autoplay
            muted
            loop
            playsinline
            controls
            class="w-full bg-black"
            @error="mediaImageFailed[item.id] = true"
          />
        </div>
      </div>
    </div>
  </section>

  <!-- ========== MISSION & VISION ========== -->
  <section class="container-wide py-6 sm:py-8">
    <div class="grid gap-6 sm:grid-cols-2">
      <article v-reveal class="card-interactive group card border-t-4 border-brand-navy">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-navy/10 text-2xl transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6">🎯</div>
        <h3 class="mt-4 font-display text-xl font-bold text-gray-900">Our Mission</h3>
        <p class="mt-3 text-sm leading-relaxed text-gray-600">To provide top-quality secondary education that nurtures in our students a zest for life, a spirit of enterprise, community service and leadership — through a balanced curriculum that prepares them for an ever-changing world.</p>
      </article>
      <article v-reveal="120" class="card-interactive group card border-t-4 border-brand-gold">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-gold/10 text-2xl transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">🌍</div>
        <h3 class="mt-4 font-display text-xl font-bold text-gray-900">Our Vision</h3>
        <p class="mt-3 text-sm leading-relaxed text-gray-600">To be a leading academic institution in Uganda and the East African region, producing highly successful and respected individuals in every aspect of life.</p>
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

            <span class="absolute right-5 top-5 z-20 rounded-full bg-black/30 px-2.5 py-1 text-xs font-bold text-white backdrop-blur-sm sm:right-7 sm:top-7">{{ String(voiceCurrent + 1).padStart(2, '0') }} / {{ String(voices.length).padStart(2, '0') }}</span>
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
                <img v-if="voices[voiceQueue[0]].featured_image_url && !voiceImageFailed[voices[voiceQueue[0]].id]" :src="voices[voiceQueue[0]].featured_image_url" :alt="voices[voiceQueue[0]].title" class="h-full w-full object-cover object-top transition duration-500 group-hover:scale-110" @error="voiceImageFailed[voices[voiceQueue[0]].id] = true">
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
                <img v-if="voices[voiceQueue[1]].featured_image_url && !voiceImageFailed[voices[voiceQueue[1]].id]" :src="voices[voiceQueue[1]].featured_image_url" :alt="voices[voiceQueue[1]].title" class="h-full w-full object-cover object-top transition duration-500 group-hover:scale-110" @error="voiceImageFailed[voices[voiceQueue[1]].id] = true">
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
                <img v-if="voices[voiceQueue[2]].featured_image_url && !voiceImageFailed[voices[voiceQueue[2]].id]" :src="voices[voiceQueue[2]].featured_image_url" :alt="voices[voiceQueue[2]].title" class="h-full w-full object-cover object-top transition duration-500 group-hover:scale-110" @error="voiceImageFailed[voices[voiceQueue[2]].id] = true">
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

  <!-- ========== RELATED POSTS ========== -->
  <section v-if="relatedPosts.length" class="container-wide py-6 sm:py-8">
    <h2 v-reveal class="font-display text-2xl font-bold text-gray-900 sm:text-3xl">Articles You May Have Missed</h2>

    <div class="mt-8 grid gap-4 sm:grid-cols-2">
      <router-link v-for="(post, i) in relatedPosts" :key="post.id" v-reveal="(i % 6) * 60" :to="`/news/${post.slug}`" class="card-interactive group flex items-start gap-4 rounded-xl bg-white p-4 shadow-card ring-1 ring-black/5">
        <div class="h-20 w-28 shrink-0 overflow-hidden rounded-lg bg-gray-100">
          <img v-if="post.image_url && !relatedImageFailed[post.id]" :src="post.image_url" :alt="post.title" class="h-full w-full object-cover transition duration-500 group-hover:scale-110" @error="relatedImageFailed[post.id] = true">
        </div>
        <div class="min-w-0">
          <h3 class="line-clamp-2 font-display text-sm font-bold leading-snug text-gray-900">{{ post.title }}</h3>
          <p v-if="post.excerpt" class="mt-1.5 line-clamp-2 text-xs text-gray-600">{{ excerptFor(post.excerpt) }}</p>
          <p v-if="post.published_at" class="mt-2 text-xs text-gray-400">{{ formatDate(post.published_at) }}</p>
        </div>
      </router-link>
    </div>
  </section>

  <!-- ========== FLOATING INNOVATIONS DOCK ========== -->
  <!-- Hovering (or, on touch devices with no hover, tapping) the lightbulb button pops big
       shortcut cards out onto an arc reaching toward the centre of the screen, one at a time via
       the JS timer chain above, sweeping clockwise (see dockItemStyle). Each is a direct
       same-tab link to its page, no modal/new window.

       z-[100] clears the sticky header's z-50 - at equal z-index the dock could end up rendered
       behind it depending on DOM order, hiding the cards and blocking clicks on them.

       Hover state is tracked per-element (button + every card) rather than on one shared
       wrapper, because the cards sit up to ~280px from the button across empty gaps: crossing
       one of those gaps on the way to a card would otherwise fire this container's mouseleave
       and collapse the dock before a click could land. enterDockZone/scheduleDockClose share one
       grace timer, so leaving one piece only schedules a close - arriving at another piece in
       time cancels it. -->
  <div class="fixed bottom-6 left-6 z-[100] h-14 w-14">
    <button
      type="button"
      aria-label="Show digital campus innovations"
      :aria-expanded="revealedDockCount > 0"
      class="innovations-float relative z-10 flex h-14 w-14 items-center justify-center rounded-full bg-brand-gold text-brand-navy-dark shadow-lg transition-transform duration-300 hover:scale-110"
      @click="toggleDock"
      @mouseenter="enterDockZone"
      @mouseleave="scheduleDockClose"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 18h6M10 21h4M12 3a6 6 0 0 0-4 10.5c.5.5.75 1 .75 1.75V16h6.5v-.75c0-.75.25-1.25.75-1.75A6 6 0 0 0 12 3z" />
      </svg>
    </button>

    <div
      v-for="(item, i) in innovations"
      :key="item.title"
      class="absolute left-0 top-0"
      :style="{ zIndex: 20 + i, ...dockItemStyle(i) }"
    >
      <Transition name="dock-pop" appear>
        <a
          v-if="i < revealedDockCount"
          :href="item.url"
          target="_blank"
          rel="noopener"
          class="card-interactive group flex w-32 items-center gap-2.5 rounded-2xl bg-white p-3 shadow-xl ring-1 ring-black/5 hover:ring-brand-gold/40 sm:w-44 sm:gap-3 sm:p-3.5"
          @mouseenter="enterDockZone"
          @mouseleave="scheduleDockClose"
        >
          <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-navy/10 text-2xl transition-transform duration-300 group-hover:scale-110 sm:h-14 sm:w-14 sm:text-3xl">{{ item.icon }}</div>
          <div class="min-w-0">
            <h3 class="truncate font-display text-sm font-bold text-gray-900 transition group-hover:text-brand-navy sm:text-base">{{ item.title }}</h3>
            <p class="mt-0.5 truncate text-xs text-gray-500 sm:text-sm">{{ item.text }}</p>
          </div>
        </a>
      </Transition>
    </div>
  </div>

  <!-- ========== FLOATING WHATSAPP BUTTON ========== -->
  <a
    href="https://wa.me/256775831844"
    target="_blank"
    rel="noopener"
    aria-label="Chat with us on WhatsApp"
    class="whatsapp-float fixed bottom-6 right-6 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-lg transition-transform duration-300 hover:scale-110"
  >
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" class="h-8 w-8" fill="currentColor">
      <path d="M16.004 3C9.377 3 4 8.377 4 15.004c0 2.65.864 5.1 2.33 7.086L4.999 28l6.09-1.6a11.94 11.94 0 0 0 4.915 1.045h.005c6.627 0 12.004-5.377 12.004-12.004C28.013 8.377 22.636 3 16.004 3zm7.03 17.13c-.294.828-1.457 1.516-2.393 1.716-.638.135-1.472.243-4.283-.92-3.594-1.485-5.906-5.128-6.086-5.365-.174-.237-1.457-1.94-1.457-3.7 0-1.76.923-2.622 1.25-2.98.328-.36.717-.45.956-.45.24 0 .478.002.687.013.22.011.516-.084.808.617.294.7.998 2.42 1.086 2.596.09.176.15.383.03.62-.12.237-.18.383-.36.59-.18.208-.376.464-.537.624-.18.176-.367.367-.158.72.21.353.933 1.539 2.004 2.492 1.376 1.225 2.536 1.605 2.887 1.786.35.18.556.15.762-.09.207-.24.884-1.03 1.12-1.383.238-.353.478-.294.807-.176.328.117 2.086.984 2.443 1.163.357.18.596.267.685.416.09.15.09.87-.203 1.7z" />
    </svg>
  </a>
</template>

<style scoped>
/* Matches container-wide's own custom 1920px breakpoint (style.css) where it stops capping at
   1800px and grows to 96vw - without this, the featured slide would get proportionally wider
   than tall again on 27"+ monitors even with the 2xl height bump above. */
@media (min-width: 1920px) {
  .voice-hero {
    height: 640px;
  }
  .news-card {
    height: 460px;
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

.whatsapp-float {
  animation: whatsappPulse 2.5s ease-in-out infinite;
}
@keyframes whatsappPulse {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.5);
  }
  50% {
    box-shadow: 0 0 0 14px rgba(37, 211, 102, 0);
  }
}

.innovations-float {
  animation: whatsappPulse 2.5s ease-in-out infinite;
  animation-name: innovationsPulse;
}
@keyframes innovationsPulse {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(250, 204, 21, 0.5);
  }
  50% {
    box-shadow: 0 0 0 14px rgba(250, 204, 21, 0);
  }
}

/* A bouncy overshoot easing gives the "thrown out" feel each shortcut pops in with - enter and
   leave use different curves/durations since leaving should feel quick and tidy, not springy. */
.dock-pop-enter-active {
  transition: opacity 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.dock-pop-enter-from {
  opacity: 0;
  transform: scale(0.2);
}
.dock-pop-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.dock-pop-leave-to {
  opacity: 0;
  transform: scale(0.5);
}

.marquee-track {
  animation: marquee 22s linear infinite;
}
.marquee-wrap:hover .marquee-track {
  animation-play-state: paused;
}

/* Matches voicePlay()'s 6000ms interval - re-keyed by voiceCurrent in the template so it
   restarts fresh from 0% every time the featured slide advances. */
.voice-progress-bar {
  animation: voiceProgress 6s linear forwards;
}
.voice-wrap:hover .voice-progress-bar {
  animation-play-state: paused;
}

@media (prefers-reduced-motion: reduce) {
  .hero-zoom,
  .whatsapp-float,
  .innovations-float,
  .marquee-track,
  .voice-progress-bar {
    animation: none;
  }
  .dock-pop-enter-active,
  .dock-pop-leave-active {
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
</style>
