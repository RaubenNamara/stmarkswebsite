<template>
  <div class="min-h-screen bg-slate-50">

    <section class="max-w-[1000px] md:max-w-[1200px] lg:max-w-[1400px] xl:max-w-[1600px] 2xl:max-w-[1800px] mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">

      <!-- ================= HEADER ================= -->
      <div class="text-center mb-12">
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-indigo-900">
          Student Leadership
        </h1>

        <p class="mt-3 text-sm sm:text-base text-gray-500 max-w-2xl mx-auto">
          Meet the student leaders, their roles, and shared media.
        </p>
      </div>

      <!-- ================= EMPTY STATE ================= -->
      <div
        v-if="!leaders || leaders.length === 0"
        class="bg-white border shadow-sm rounded-xl p-10 text-center"
      >
        <h2 class="text-xl font-bold text-slate-800">
          No student leaders available
        </h2>
      </div>

      <!-- ================= LEADERS ================= -->
      <div v-else class="space-y-12 lg:space-y-16">

        <article
          v-for="leader in leaders"
          :key="leader.id"
          class="bg-white border shadow-sm rounded-2xl overflow-hidden transition hover:shadow-md"
        >

          <div class="grid grid-cols-1 lg:grid-cols-2">

            <!-- ================= MEDIA ================= -->
            <div class="bg-slate-100 aspect-video lg:aspect-auto">

              <!-- VIDEO FILE -->
              <video
                v-if="leader.video_path"
                controls
                class="w-full h-full object-cover bg-black"
              >
                <source :src="mediaUrl(leader.video_path)" />
              </video>

              <!-- YOUTUBE VIDEO -->
              <iframe
                v-else-if="leader.video_link"
                :src="embedUrl(leader.video_link)"
                class="w-full h-full border-0 bg-black"
                allowfullscreen
              ></iframe>

              <!-- IMAGE -->
              <img
                v-else-if="leader.image_path"
                :src="mediaUrl(leader.image_path)"
                class="w-full h-full object-cover"
                loading="lazy"
              />

              <!-- NO MEDIA -->
              <div
                v-else
                class="w-full h-full flex items-center justify-center text-gray-400 text-sm"
              >
                No media available
              </div>

            </div>

            <!-- ================= CONTENT ================= -->
            <div class="p-6 sm:p-8 lg:p-10 flex flex-col justify-center">

              <!-- TITLE -->
              <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-900">
                {{ leader.title || 'Untitled' }}
              </h2>

              <!-- CONTENT -->
              <p
                v-if="leader.content"
                class="mt-5 text-slate-700 leading-7 sm:leading-8 text-sm sm:text-base text-justify max-w-2xl"
              >
                {{ leader.content }}
              </p>

              <!-- FALLBACK -->
              <p v-else class="mt-5 text-slate-400">
                No content available.
              </p>

            </div>

          </div>

        </article>

      </div>

    </section>

  </div>
</template>

<script setup>
import { ref } from 'vue'
import MainLayout from '@/Layouts/MainLayout.vue'

defineOptions({
  layout: MainLayout
})

defineProps({
  leaders: {
    type: Array,
    default: () => []
  }
})

/* ================= MEDIA URL HANDLER ================= */
function mediaUrl(path) {
  if (!path) return ''

  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path
  }

  if (path.startsWith('/')) {
    return path
  }

  return `/storage/${path}`
}

/* ================= YOUTUBE EMBED HANDLER ================= */
function embedUrl(url) {
  if (!url) return ''

  if (url.includes('/embed/')) return url

  try {
    const parsed = new URL(url)

    // Short YouTube URL
    if (parsed.hostname.includes('youtu.be')) {
      const id = parsed.pathname.replace('/', '')
      return `https://www.youtube.com/embed/${id}`
    }

    // Standard YouTube URL
    if (parsed.hostname.includes('youtube.com')) {
      const videoId = parsed.searchParams.get('v')
      if (videoId) {
        return `https://www.youtube.com/embed/${videoId}`
      }
    }
  } catch (e) {}

  return url
}
</script>

<style scoped>
/* Smooth hover effect */
article {
  transition: all 0.25s ease;
}

/* Optional: slightly nicer video controls overlay spacing */
video {
  background: black;
}
</style>