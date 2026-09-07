<script setup lang="ts">
import { ref, reactive, onMounted, onBeforeUnmount } from 'vue'
import { useHead } from '@unhead/vue'
import { api } from '../services/api'
import { staticAsset } from '../utils/staticAsset'
import AvatarImage from '../components/AvatarImage.vue'

useHead({
  title: null,
  meta: [{ name: 'description', content: "St Mark's College Namagoma - The Higher Achiever's College." }],
})

const [slidesRes, mediaRes, newsRes] = await Promise.all([
  api.get('/slides'),
  api.get('/media'),
  api.get('/news', { params: { limit: 20 } }),
])

const slides = (slidesRes.data.data.slides as Array<Record<string, any>>).filter((s) => s.image_url || s.video_url)
const mediaItems = mediaRes.data.data.media as Array<Record<string, any>>
const newsItems = newsRes.data.data.news as Array<Record<string, any>>

const latestNews = newsItems.slice(0, 6)
const featuredPosts = newsItems.slice(0, 5)
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

// -- "Welcome" background section, with a Read More/Show Less toggle --
const backgroundExpanded = ref(false)
const backgroundImageFailed = ref(false)

const backgroundIntro = 'St. Mark\'s College Namagoma is a high-quality private secondary school founded in 2003. It is a mixed boarding school offering both Arts and Sciences at "O" and "A" Level, owned by experienced, well-educated individuals with a strong commitment to excellence in academics and post-school life.'

const backgroundParagraphs = [
  'St. Mark\'s College Namagoma is a high-quality private secondary school founded in 2003. It is a mixed boarding school offering both Arts and Sciences at "O" and "A" Level. The College is owned by experienced and well-educated individuals with a strong commitment to excellence in academics and post-school life.',
  'The College has a seasoned Board of Governors comprising educationists, bankers and senior managers in Ugandan society. It is duly licensed and registered by the Ministry of Education and Sports — registration number PSS/S/261, UNEB centre number U1664.',
  'Since opening its doors on 10th February 2003, the College has achieved tremendous success in student recruitment and retention, and now accommodates over 2,000 students in both "O" and "A" Level sections.',
  'The College campus sits on 30 acres of land at Namagoma, 10 miles along the Kampala–Masaka Road, neighbouring well-established schools such as King\'s College Budo, Trinity College Nabbingo and St. Lawrence Colleges — an environment that provides healthy competition for high academic achievement.',
  'Our philosophy is to value all students for their individual abilities and special talents, nurturing resourceful, disciplined and ethical citizens with strong analytical and problem-solving skills.',
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
  return id ? `https://www.youtube.com/embed/${id}` : ''
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
    <div class="relative h-[60vh] min-h-[360px] sm:h-[65vh] lg:h-[78vh] xl:h-[72vh]">
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
            class="h-full w-full object-cover"
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
            class="h-full w-full object-cover"
            @error="slideFailed[slide.id] = true"
          />
          <div v-else class="h-full w-full bg-gradient-to-br from-brand-navy via-brand-navy to-brand-navy-dark" />

          <div class="absolute inset-0 bg-gradient-to-b from-black/20 via-black/10 to-black/60" />

          <div v-if="slide.title || slide.caption" class="absolute inset-0 z-20 flex items-center justify-center px-6 text-center">
            <div class="max-w-3xl transition-all duration-700" :class="current === i ? 'translate-y-0 opacity-100' : 'translate-y-6 opacity-0'">
              <h2 v-if="slide.title" class="font-display text-3xl font-extrabold text-white drop-shadow-lg sm:text-5xl">{{ slide.title }}</h2>
              <p v-if="slide.caption" class="mx-auto mt-4 max-w-xl text-base text-blue-50/90 sm:text-lg">{{ slide.caption }}</p>
            </div>
          </div>
        </div>
      </template>

      <div v-else class="relative flex h-full items-center justify-center overflow-hidden bg-gradient-to-br from-brand-navy via-brand-navy to-brand-navy-dark px-6 text-center">
        <div class="pointer-events-none absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, white 1px, transparent 1px); background-size: 28px 28px;" />
        <div class="relative mx-auto max-w-3xl">
          <span class="eyebrow">The Higher Achiever's College</span>
          <h1 class="mt-4 font-display text-4xl font-extrabold tracking-tight text-white sm:text-6xl">Welcome to St Mark's College Namagoma</h1>
          <p class="mx-auto mt-5 max-w-2xl text-lg text-blue-100">Building disciplined, confident and competent learners — a community where character and academic excellence grow together.</p>
          <div class="mt-9 flex flex-wrap items-center justify-center gap-4">
            <router-link to="/admissions" class="btn btn-gold">Apply Now</router-link>
            <router-link to="/about" class="btn btn-outline">Learn More</router-link>
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
  <section class="mx-auto mt-8 max-w-7xl px-6">
    <div class="grid overflow-hidden rounded-2xl bg-white shadow-card ring-1 ring-black/5 lg:grid-cols-2">
      <div class="relative h-56 w-full overflow-hidden bg-brand-navy/5 sm:h-72 lg:h-full">
        <img v-if="!backgroundImageFailed" :src="staticAsset('images/home.jpg')" alt="St Mark's College Namagoma campus" class="h-full w-full object-cover" @error="backgroundImageFailed = true">
        <div v-else class="flex h-full items-center justify-center bg-gradient-to-br from-brand-navy to-brand-navy-dark">
          <span class="font-display text-2xl font-extrabold text-white/30">St Mark's College</span>
        </div>
      </div>

      <div class="flex flex-col justify-center p-8 sm:p-10 lg:p-12">
        <span class="eyebrow text-brand-navy">Our Story</span>
        <h2 class="mt-2 font-display text-2xl font-bold text-gray-900 sm:text-3xl">Welcome to St Mark's College Namagoma</h2>

        <p v-if="!backgroundExpanded" class="mt-4 leading-relaxed text-gray-600">{{ backgroundIntro }}</p>
        <div v-else class="mt-4 space-y-4 leading-relaxed text-gray-600">
          <p v-for="(para, i) in backgroundParagraphs" :key="i">{{ para }}</p>
          <p class="font-semibold italic text-brand-navy">"To Be, Not To Seem."</p>
        </div>

        <button type="button" class="btn mt-6 w-fit" :aria-expanded="backgroundExpanded" @click="backgroundExpanded = !backgroundExpanded">
          {{ backgroundExpanded ? 'Show Less' : 'Read More' }}
        </button>
      </div>
    </div>
  </section>

  <!-- ========== SCHOOL LEADERSHIP ========== -->
  <section class="mx-auto max-w-7xl px-6 py-16 sm:py-20">
    <div class="mx-auto max-w-2xl text-center">
      <span class="eyebrow text-brand-navy">Leadership</span>
      <h2 class="mt-3 font-display text-3xl font-bold text-gray-900">School Leadership</h2>
      <p class="mt-3 text-gray-600">Visionary leaders guiding academic excellence, discipline and holistic student development.</p>
    </div>

    <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
      <article v-for="leader in leadership" :key="leader.name" class="card-interactive card text-center">
        <AvatarImage :src="staticAsset(leader.image)" :alt="leader.name" size="lg" />
        <h3 class="mt-4 font-display text-lg font-bold text-gray-900">{{ leader.name }}</h3>
        <p class="mt-1 text-xs font-bold uppercase tracking-widest text-brand-navy/60">{{ leader.role }}</p>
        <p class="mt-3 text-sm leading-relaxed text-gray-600">{{ leader.text }}</p>
        <router-link :to="leader.url" class="btn btn-gold mx-auto mt-5 w-fit px-5 py-2 text-xs">Read More</router-link>
      </article>
    </div>
  </section>

  <!-- ========== LATEST NEWS & EVENTS ========== -->
  <section v-if="latestNews.length" class="bg-gray-50 px-6 py-16 sm:py-20">
    <div class="mx-auto max-w-7xl">
      <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
          <span class="eyebrow text-brand-navy">Stay Informed</span>
          <h2 class="mt-3 font-display text-3xl font-bold text-gray-900">Latest News &amp; Events</h2>
        </div>
        <router-link to="/news" class="font-semibold text-brand-navy hover:underline">View all news &rarr;</router-link>
      </div>

      <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <router-link
          v-for="item in latestNews"
          :key="item.id"
          :to="`/news/${item.slug}`"
          class="group relative block h-64 overflow-hidden rounded-2xl shadow-card ring-1 ring-black/5 sm:h-72"
        >
          <img
            v-if="item.image_url && !newsImageFailed[item.id]"
            :src="item.image_url"
            :alt="item.title"
            class="h-full w-full object-cover transition duration-700 group-hover:scale-110"
            @error="newsImageFailed[item.id] = true"
          >
          <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-brand-navy to-brand-navy-dark" />
          <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent" />
          <div class="absolute inset-0 flex flex-col justify-between p-5">
            <span v-if="item.published_at" class="w-fit rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white backdrop-blur-md">{{ formatDate(item.published_at) }}</span>
            <h3 class="font-display text-lg font-bold leading-snug text-white drop-shadow-md">{{ item.title }}</h3>
          </div>
        </router-link>
      </div>
    </div>
  </section>

  <!-- ========== FEATURED POSTS ========== -->
  <section v-if="featuredPosts.length" class="mx-auto max-w-7xl px-6 py-16 sm:py-20">
    <div class="rounded-2xl bg-gradient-to-br from-brand-navy/5 to-brand-gold/5 p-6 ring-1 ring-brand-navy/10 sm:p-10">
      <span class="eyebrow text-brand-navy">Featured</span>
      <h2 class="mt-2 font-display text-2xl font-bold text-gray-900 sm:text-3xl">Featured Posts</h2>

      <div class="mt-8 grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-5">
        <router-link v-for="post in featuredPosts" :key="post.id" :to="`/news/${post.slug}`" class="group block">
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
  </section>

  <!-- ========== MOTTO / CORE VALUES / WHY CHOOSE ========== -->
  <section class="bg-gradient-to-b from-brand-navy/[0.03] to-white px-6 py-16 sm:py-20">
    <div class="mx-auto max-w-7xl">
      <div class="grid gap-8 lg:grid-cols-3">
        <div class="card">
          <h3 class="font-display text-xl font-bold text-gray-900">The College Motto</h3>
          <p class="mt-3 text-lg font-semibold italic text-brand-navy">"To Be, Not To Seem"</p>
          <p class="mt-4 text-sm leading-relaxed text-gray-600">The motto reflects our founders' desire to train students with strong values that guide them through life — encouraging authenticity, integrity and inner strength over outward appearances.</p>
          <router-link to="/elearning" class="btn btn-gold mt-6 w-fit">
            Visit eSpace
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
          </router-link>
        </div>

        <div class="card">
          <h3 class="text-center font-display text-xl font-bold text-gray-900">Core Values (GREET)</h3>
          <div class="mt-6 space-y-4">
            <div v-for="(value, i) in coreValues" :key="`${value.letter}-${i}`" class="flex items-start gap-4">
              <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-navy font-display font-bold text-white">{{ value.letter }}</span>
              <div>
                <p class="font-bold text-gray-900">{{ value.title }}</p>
                <p class="text-sm text-gray-600">{{ value.text }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <h3 class="font-display text-xl font-bold text-gray-900">Why Choose St Mark's?</h3>
          <p class="mt-4 text-sm leading-relaxed text-gray-600">St Mark's College Namagoma promotes education and excellence with particular focus on each student — academically, spiritually and morally.</p>
          <p class="mt-3 text-sm leading-relaxed text-gray-600">Students benefit from modern facilities and a serene learning environment, dedicated teachers, and preparation for leadership and responsible citizenship.</p>
          <router-link to="/admissions" class="btn mt-6 w-fit">
            Visit Admissions
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
          </router-link>
        </div>
      </div>
    </div>
  </section>

  <!-- ========== MEDIA SHOWCASE ========== -->
  <section v-if="mediaToShow.length" class="bg-gray-50 py-16 sm:py-20">
    <div class="mx-auto max-w-7xl px-6">
      <div class="text-center">
        <span class="eyebrow text-brand-navy">Life at St Mark's</span>
        <h2 class="mt-3 font-display text-3xl font-bold text-gray-900">Media Showcase</h2>
      </div>

      <div class="mt-10 grid gap-6" :class="mediaToShow.length > 1 ? 'sm:grid-cols-2' : ''">
        <div v-for="item in mediaToShow" :key="item.id" class="overflow-hidden rounded-2xl bg-white shadow-card ring-1 ring-black/5">
          <img
            v-if="item.type === 'image' && item.file_url && !mediaImageFailed[item.id]"
            :src="item.file_url"
            :alt="item.title || `St Mark's College media`"
            class="w-full object-cover"
            @error="mediaImageFailed[item.id] = true"
          >
          <div v-else-if="item.type === 'link' && item.video_url && youTubeEmbed(item.video_url)" class="aspect-video w-full">
            <iframe :src="youTubeEmbed(item.video_url)" class="h-full w-full" frameborder="0" allowfullscreen />
          </div>
          <video
            v-else-if="item.type === 'video' && item.file_url && !mediaImageFailed[item.id]"
            :src="item.file_url"
            controls
            class="w-full bg-black"
            @error="mediaImageFailed[item.id] = true"
          />
        </div>
      </div>
    </div>
  </section>

  <!-- ========== MISSION & VISION ========== -->
  <section class="mx-auto max-w-7xl px-6 py-16 sm:py-20">
    <div class="grid gap-6 sm:grid-cols-2">
      <article class="card border-t-4 border-brand-navy">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-navy/10 text-2xl">🎯</div>
        <h3 class="mt-4 font-display text-xl font-bold text-gray-900">Our Mission</h3>
        <p class="mt-3 text-sm leading-relaxed text-gray-600">To provide top-quality secondary education that nurtures in our students a zest for life, a spirit of enterprise, community service and leadership — through a balanced curriculum that prepares them for an ever-changing world.</p>
      </article>
      <article class="card border-t-4 border-brand-gold">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-gold/10 text-2xl">🌍</div>
        <h3 class="mt-4 font-display text-xl font-bold text-gray-900">Our Vision</h3>
        <p class="mt-3 text-sm leading-relaxed text-gray-600">To be a leading academic institution in Uganda and the East African region, producing highly successful and respected individuals in every aspect of life.</p>
      </article>
    </div>
  </section>

  <!-- ========== RELATED POSTS ========== -->
  <section v-if="relatedPosts.length" class="mx-auto max-w-7xl px-6 py-16 sm:py-20">
    <h2 class="font-display text-2xl font-bold text-gray-900 sm:text-3xl">Articles You May Have Missed</h2>

    <div class="mt-8 grid gap-4 sm:grid-cols-2">
      <router-link v-for="post in relatedPosts" :key="post.id" :to="`/news/${post.slug}`" class="card-interactive flex items-start gap-4 rounded-xl bg-white p-4 shadow-card ring-1 ring-black/5">
        <div class="h-20 w-28 shrink-0 overflow-hidden rounded-lg bg-gray-100">
          <img v-if="post.image_url && !relatedImageFailed[post.id]" :src="post.image_url" :alt="post.title" class="h-full w-full object-cover" @error="relatedImageFailed[post.id] = true">
        </div>
        <div class="min-w-0">
          <h3 class="line-clamp-2 font-display text-sm font-bold leading-snug text-gray-900">{{ post.title }}</h3>
          <p v-if="post.excerpt" class="mt-1.5 line-clamp-2 text-xs text-gray-600">{{ excerptFor(post.excerpt) }}</p>
          <p v-if="post.published_at" class="mt-2 text-xs text-gray-400">{{ formatDate(post.published_at) }}</p>
        </div>
      </router-link>
    </div>
  </section>

  <!-- ========== CTA ========== -->
  <section class="relative overflow-hidden bg-brand-navy-dark px-6 py-16 text-center">
    <div class="mx-auto max-w-2xl">
      <h2 class="font-display text-2xl font-bold text-white sm:text-3xl">Ready to begin the journey?</h2>
      <p class="mx-auto mt-3 max-w-lg text-blue-100">Join a community that nurtures discipline, confidence and competence in every learner.</p>
      <router-link to="/admissions" class="btn btn-gold mt-7">Start Your Application</router-link>
    </div>
  </section>
</template>

<style scoped>
.hero-zoom {
  animation: heroZoom 9s ease-in-out infinite alternate;
}
@keyframes heroZoom {
  from {
    transform: scale(1);
  }
  to {
    transform: scale(1.08);
  }
}
@media (prefers-reduced-motion: reduce) {
  .hero-zoom {
    animation: none;
  }
}
</style>
