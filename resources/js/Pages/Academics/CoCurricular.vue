<script setup>
import MainLayout from '@/Layouts/MainLayout.vue'

defineOptions({
  layout: MainLayout
})

const props = defineProps({
  items: {
    type: Array,
    default: () => []
  }
})

function isYouTube(url) {
  return !!url && (url.includes('youtube.com') || url.includes('youtu.be'))
}

function getYouTubeEmbed(url) {
  if (!url) return ''
  let videoId = ''

  if (url.includes('youtu.be')) {
    videoId = url.split('youtu.be/')[1].split('?')[0]
  } else if (url.includes('watch?v=')) {
    videoId = url.split('watch?v=')[1].split('&')[0]
  }

  return `https://www.youtube.com/embed/${videoId}`
}
</script>

<template>
  <div class="bg-gradient-to-b from-gray-50 to-gray-100 min-h-screen py-20">
    <div class="max-w-7xl 2xl:max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8">

      <!-- HEADER -->
      <div class="text-center mb-16">
        <h1 class="text-4xl md:text-5xl font-extrabold text-[#07203a] tracking-tight mb-4">
          Co-Curricular Activities
        </h1>

        <div class="w-24 h-1 bg-blue-600 mx-auto rounded-full mb-6"></div>

        <p class="text-gray-600 max-w-3xl mx-auto text-base md:text-lg leading-relaxed">
          At St Mark’s College Namagoma, co-curricular activities nurture leadership,
          creativity, teamwork and holistic development.
        </p>
      </div>

      <!-- CARDS -->
      <div v-if="items.length" class="space-y-16">

        <div
          v-for="item in items"
          :key="item.id"
          class="group bg-white/80 backdrop-blur-lg border border-gray-100 shadow-md hover:shadow-2xl transition-all duration-500 rounded-3xl overflow-hidden"
        >
          <div class="flex flex-col lg:flex-row-reverse">

            <!-- MEDIA -->
            <div class="w-full lg:w-1/2 relative overflow-hidden">

              <!-- IMAGE -->
              <img
                v-if="item.image && !item.video"
                :src="`/storage/${item.image}`"
                :alt="item.title"
                class="w-full h-64 sm:h-80 lg:h-full object-cover transform group-hover:scale-105 transition duration-700"
              />

              <!-- VIDEO -->
              <div v-else class="w-full h-full">
                <iframe
                  v-if="isYouTube(item.video)"
                  :src="getYouTubeEmbed(item.video)"
                  class="w-full h-64 sm:h-80 lg:h-full"
                  allowfullscreen
                ></iframe>

                <video
                  v-else
                  :src="item.video"
                  controls
                  class="w-full h-64 sm:h-80 lg:h-full object-cover"
                ></video>
              </div>

              <!-- subtle overlay -->
           <div class="absolute inset-0 bg-black/5 group-hover:bg-black/10 transition pointer-events-none"></div>
            </div>

            <!-- CONTENT -->
            <div class="w-full lg:w-1/2 p-6 sm:p-10 lg:p-14 flex flex-col justify-center">

              <h2 class="text-2xl md:text-3xl font-bold text-[#07203a] mb-4 leading-snug">
                {{ item.title }}
              </h2>

              <div class="w-12 h-1 bg-blue-600 rounded mb-6"></div>

              <p class="text-gray-700 text-justify leading-relaxed text-base md:text-lg max-w-prose">
                {{ item.content }}
              </p>

            </div>

          </div>
        </div>

      </div>

      <!-- EMPTY -->
      <div v-else class="text-center py-24 text-gray-500 text-lg">
        No co-curricular content available yet.
      </div>

    </div>
  </div>
</template>