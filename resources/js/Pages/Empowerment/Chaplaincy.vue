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

function embedUrl(link) {
  if (!link) return ''

  if (link.includes('youtube.com/watch')) {
    return link.replace('watch?v=', 'embed/')
  }

  if (link.includes('youtu.be/')) {
    const id = link.split('youtu.be/')[1].split(/[?&]/)[0]
    return `https://www.youtube.com/embed/${id}`
  }

  return link
}
</script>

<template>
  <div class="bg-gray-50 min-h-screen py-16 md:py-20 2xl:py-28">

    <!-- CONTAINER -->
    <div class="max-w-[1400px] 2xl:max-w-[1700px] 3xl:max-w-[1900px] mx-auto px-6 md:px-10 2xl:px-16">

      <!-- HEADER -->
      <header class="text-center mb-16 2xl:mb-24">
        <h1
          class="text-4xl lg:text-5xl 2xl:text-6xl font-extrabold bg-gradient-to-r from-blue-700 via-indigo-600 to-purple-600 bg-clip-text text-transparent"
        >
          Chaplaincy
        </h1>

        <p class="mt-4 text-gray-600 text-lg 2xl:text-xl max-w-2xl 2xl:max-w-3xl mx-auto">
          Spiritual life and worship at St. Mark's College Namagoma
        </p>
      </header>

      <!-- ITEMS -->
      <div
        v-for="item in props.items"
        :key="item.id"
        class="bg-white p-6 md:p-10 2xl:p-14 rounded-2xl shadow-xl mb-12 2xl:mb-16 border border-gray-200 hover:shadow-2xl transition"
      >

        <!-- GRID -->
        <div class="grid gap-10 items-center lg:grid-cols-2 2xl:grid-cols-3">

          <!-- MEDIA -->
          <div class="w-full">

            <img
              v-if="item.image"
              :src="`/storage/chaplaincy/${item.image}`"
              class="w-full h-72 md:h-80 2xl:h-96 object-cover rounded-xl shadow-md"
              :alt="item.title"
            />

            <video
              v-else-if="item.video"
              controls
              class="w-full h-72 md:h-80 2xl:h-96 object-cover rounded-xl shadow-md"
            >
              <source :src="`/storage/chaplaincy/${item.video}`" type="video/mp4">
            </video>

            <iframe
              v-else-if="item.video_link"
              :src="embedUrl(item.video_link)"
              class="w-full h-72 md:h-80 2xl:h-96 rounded-xl shadow-md"
              frameborder="0"
              allowfullscreen
            ></iframe>

          </div>

          <!-- CONTENT -->
          <div class="lg:col-span-1 2xl:col-span-2">

            <h2 class="text-2xl md:text-3xl 2xl:text-4xl font-bold text-indigo-900 mb-5">
              {{ item.title }}
            </h2>

            <p class="text-gray-700 text-lg 2xl:text-xl leading-relaxed text-justify">
              {{ item.content }}
            </p>

          </div>

        </div>

      </div>

      <!-- EMPTY STATE -->
      <div
        v-if="!props.items.length"
        class="text-center text-gray-500 text-lg py-20"
      >
        No chaplaincy posts available yet.
      </div>

    </div>
  </div>
</template>

<style scoped>
</style>