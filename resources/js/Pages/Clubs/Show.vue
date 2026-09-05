<script setup>
import MainLayout from '@/Layouts/MainLayout.vue'
import { Link } from '@inertiajs/vue3'

defineOptions({
  layout: MainLayout,
})

const props = defineProps({
  club: {
    type: Object,
    default: () => ({})
  },
  relatedClubs: {
    type: Array,
    default: () => []
  }
})

/* SAFE EXCERPT */
function excerpt(text, max = 130) {
  if (!text) return ''
  const clean = text.replace(/<[^>]*>?/gm, '')
  return clean.length > max ? clean.slice(0, max) + '...' : clean
}

/* LIMIT TO 8 CLUBS */
const limitedClubs = props.relatedClubs.slice(0, 8)
</script>

<template>
<div class="bg-gray-50 min-h-screen py-16 md:py-20 2xl:py-28">

  <!-- CONTAINER -->
  <div class="max-w-[1400px] 2xl:max-w-[1700px] 3xl:max-w-[1900px] mx-auto px-6 md:px-10 2xl:px-16">

    <!-- TITLE -->
    <div class="mb-12 2xl:mb-20 text-center">
      <h1 class="text-3xl md:text-4xl lg:text-5xl 2xl:text-6xl font-bold text-[#0b2f56] leading-tight">
        {{ club.title }}
      </h1>
    </div>

    <!-- CONTENT -->
    <div class="bg-white rounded-2xl shadow-md p-6 md:p-10 2xl:p-14 mb-16 2xl:mb-24">
      <div
        class="club-content max-w-none text-justify"
        v-html="club.content"
      ></div>
    </div>

    <!-- GALLERY -->
    <div v-if="club.images && club.images.length" class="mb-20 2xl:mb-28">

      <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6">

        <div
          v-for="image in club.images"
          :key="image.id"
          class="group overflow-hidden shadow-md hover:shadow-2xl transition duration-300"
        >
          <img
            :src="image.url || `/storage/${image.image_path}`"
            :alt="club.title"
            class="w-full h-52 sm:h-56 md:h-60 lg:h-64 xl:h-72 object-cover group-hover:scale-110 transition duration-500"
          />
        </div>

      </div>

    </div>

    <!-- RELATED CLUBS -->
    <div v-if="limitedClubs.length">

      <h2 class="text-2xl md:text-3xl 2xl:text-4xl font-bold text-[#0b2f56] mb-10 text-center">
        Explore More Clubs
      </h2>

      <!-- RESPONSIVE GRID -->
      <div class="grid gap-8 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
        

        <div
          v-for="related in limitedClubs"
          :key="related.id"
          class="group bg-white rounded-2xl shadow-md hover:shadow-xl transition duration-300 overflow-hidden border border-gray-100 flex flex-col"
        >

          <div class="p-6 flex flex-col flex-1">

            <h3 class="text-lg md:text-xl font-semibold text-[#0b2f56] mb-3 line-clamp-2">
              {{ related.title }}
            </h3>

            <p class="text-gray-600 text-sm md:text-base leading-relaxed mb-5 flex-1 line-clamp-3 text-justify">
              {{ excerpt(related.content) }}
            </p>

            <Link
              :href="route('clubs.show', related.slug)"
              class="mt-auto inline-flex items-center gap-2 text-[#0b2f56] font-semibold hover:text-[#07203a] transition"
            >
              Read More

              <svg xmlns="http://www.w3.org/2000/svg"
                   class="h-4 w-4"
                   fill="none"
                   viewBox="0 0 24 24"
                   stroke="currentColor">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 5l7 7-7 7" />
              </svg>

            </Link>

          </div>

        </div>

      </div>

    </div>

  </div>
</div>
</template>

<style scoped>

/* CONTENT */
.club-content {
  font-size: 1.05rem;
  line-height: 1.9;
  color: #374151;
}

@media (min-width: 1536px) {
  .club-content {
    font-size: 1.15rem;
    line-height: 2;
  }
}

.club-content p {
  margin-bottom: 1.4rem;
}

.club-content h1,
.club-content h2,
.club-content h3 {
  color: #0b2f56;
  font-weight: 700;
  margin-top: 2rem;
  margin-bottom: 1rem;
}

.club-content ul {
  padding-left: 1.4rem;
  margin-bottom: 1.2rem;
}

.club-content li {
  margin-bottom: 0.6rem;
}

</style>