<script setup>
import { computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'

defineOptions({
  layout: MainLayout
})

const props = defineProps({
  clubs: {
    type: [Array, Object],
    default: () => ([]),
  }
})

const page = usePage()

/* PAGE TITLE */
const pageTitle = computed(() => page.props.pageTitle ?? 'Our Clubs')

if (typeof document !== 'undefined') {
  document.title = pageTitle.value
}

/* HANDLE NORMAL + PAGINATED DATA */
const clubsList = computed(() => {
  if (Array.isArray(props.clubs)) return props.clubs
  if (props.clubs && Array.isArray(props.clubs.data)) return props.clubs.data
  return []
})

const isPaginated = computed(() =>
  !!(props.clubs && props.clubs.data &&
     (props.clubs.next_page_url || props.clubs.prev_page_url))
)

const nextPage = computed(() => props.clubs?.next_page_url || null)
const prevPage = computed(() => props.clubs?.prev_page_url || null)

/* TEXT EXCERPT */
function excerpt(html, max = 150) {
  if (!html) return ''
  const text = String(html).replace(/<[^>]*>?/gm, '')
  return text.length > max ? text.slice(0, max).trim() + '…' : text
}

/* PAGINATION HANDLER */
function visitPage(url) {
  if (!url) return
  router.get(url)
}
</script>

<template>
  <div class="bg-gray-50 min-h-screen py-16 md:py-20 2xl:py-28">

    <!-- CONTAINER -->
    <div class="max-w-[1400px] 2xl:max-w-[1700px] 3xl:max-w-[1900px] mx-auto px-6 md:px-10 2xl:px-16">

      <!-- HEADER -->
      <div class="text-center mb-16 2xl:mb-24">
        <h1 class="text-4xl md:text-5xl 2xl:text-6xl font-bold text-[#0b2f56] mb-6">
          Our Clubs
        </h1>

        <p class="text-gray-600 text-lg 2xl:text-xl max-w-2xl 2xl:max-w-3xl mx-auto leading-relaxed">
          Discover the vibrant clubs and co-curricular activities that shape leadership,
          innovation, and character at our school.
        </p>
      </div>

      <!-- CLUB GRID -->
      <div
        v-if="clubsList.length"
        class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 3xl:grid-cols-6"
      >

        <article
          v-for="club in clubsList"
          :key="club.id"
          class="group bg-white rounded-2xl shadow-md hover:shadow-2xl transition duration-500 overflow-hidden flex flex-col border border-gray-100"
        >

          <!-- IMAGE -->
          <div class="h-48 sm:h-52 md:h-56 2xl:h-64 overflow-hidden bg-gray-100">

            <img
              v-if="club.images && club.images.length"
              :src="club.images[0].url || `/storage/${club.images[0].image_path}`"
              :alt="club.title"
              class="w-full h-full object-cover group-hover:scale-110 transition duration-700"
            />

            <div
              v-else
              class="w-full h-full flex items-center justify-center text-gray-400 text-sm"
            >
              No image available
            </div>

          </div>

          <!-- CONTENT -->
          <div class="p-5 2xl:p-6 flex flex-col flex-1">

            <h2 class="text-lg md:text-xl 2xl:text-2xl font-semibold text-[#0b2f56] mb-3 line-clamp-2">
              {{ club.title }}
            </h2>

            <p class="text-gray-600 text-sm 2xl:text-base leading-relaxed mb-5 flex-1 line-clamp-3">
              {{ excerpt(club.content) }}
            </p>

            <!-- BUTTON -->
            <Link
              :href="route('clubs.show', club.slug)"
              class="mt-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-[#0b2f56] text-white text-sm font-medium hover:bg-[#07203a] transition duration-300"
            >
              View Club

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

        </article>

      </div>

      <!-- EMPTY STATE -->
      <div v-else class="text-center text-gray-500 py-24 text-lg">
        No clubs available at the moment.
      </div>

      <!-- PAGINATION -->
      <div v-if="isPaginated" class="mt-16 flex justify-center">
        <div class="flex gap-4">

          <button
            v-if="prevPage"
            @click="visitPage(prevPage)"
            class="px-5 py-2 border rounded-lg text-gray-700 hover:bg-gray-100 transition"
          >
            ← Previous
          </button>

          <button
            v-if="nextPage"
            @click="visitPage(nextPage)"
            class="px-5 py-2 border rounded-lg text-gray-700 hover:bg-gray-100 transition"
          >
            Next →
          </button>

        </div>
      </div>

    </div>
  </div>
</template>