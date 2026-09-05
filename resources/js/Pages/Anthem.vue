<script>
import MainLayout from '@/Layouts/MainLayout.vue'

export default {
  layout: MainLayout,
}
</script>

<script setup>
import { ref } from 'vue'

/* STANZAS */
const stanzas = ref([
  {
    lines: [
      "St. Mark’s College striving for excellence,",
      "May God give us strength to perform!",
      "With discipline, smartness, and the development of talents,",
      "Our future is safe and shinning blue."
    ]
  },
  {
    lines: [
      "St. Mark’s College, the high Achiever’s pride,",
      "We toil to meet and live our pledge,",
      "Of quality education, devotion, and teamwork,",
      "We shall live, Achievers we shall be."
    ]
  },
  {
    lines: [
      "St. Mark’s College so competent and strong,",
      "The staff is qualified and bold.",
      "Let us be not to seem, to develop our motherland,",
      "Uganda long, long live,",
      "Long, our founders long live."
    ]
  }
])

/* CHORUS */
const chorusLines = [
  "WE shall be not to seem,",
  "Examples of the World,",
  "On this gentle foundation,",
  "St. Mark’s College Staff,",
  "Will guide us with the pillar",
  "of light forever more…"
]

/* BORDER COLORS */
const borderClasses = [
  'border-blue-900',
  'border-yellow-500',
  'border-blue-700'
]
</script>

<template>
  <div class="min-h-screen bg-slate-50">

    <!-- ================= HERO ================= -->
    <section
      class="relative bg-cover bg-center bg-no-repeat 
             h-[60vh] sm:h-[65vh] lg:h-[75vh] flex items-center"
      style="background-image: url('/images/complex.png');"
    >
      <!-- Overlay -->
      <div class="absolute inset-0 bg-black/60"></div>

      <div class="relative w-full 
                  max-w-[900px] md:max-w-[1100px] lg:max-w-[1300px] 
                  xl:max-w-[1500px] 2xl:max-w-[1700px] 
                  mx-auto px-4 sm:px-6 text-white text-center">

        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl 
                   font-extrabold mb-4 tracking-wide">
          School Anthem
        </h1>

        <p class="max-w-3xl mx-auto 
                  text-sm sm:text-base md:text-lg lg:text-xl 
                  leading-relaxed text-gray-200">
          Our anthem - a pledge of faith, discipline, and excellence.
          Chorus repeats after every stanza.
        </p>

      </div>
    </section>

    <!-- ================= CONTENT ================= -->
    <section class="bg-gradient-to-b from-gray-100 to-white py-14 sm:py-16 lg:py-20">

      <div class="w-full 
                  max-w-[1000px] md:max-w-[1200px] lg:max-w-[1400px] 
                  xl:max-w-[1600px] 2xl:max-w-[1800px] 
                  mx-auto px-4 sm:px-6 lg:px-8">

        <!-- GRID -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-8 lg:gap-10">

          <!-- STANZA CARD -->
          <div
            v-for="(stanza, index) in stanzas"
            :key="index"
            class="card"
            :class="borderClasses[index % borderClasses.length]"
          >

            <h2 class="card-title">
              {{ index + 1 }}. Stanza
            </h2>

            <div class="card-content">
              <p
                v-for="(line, li) in stanza.lines"
                :key="li"
                class="whitespace-pre-line"
              >
                {{ line }}
              </p>
            </div>

            <hr class="my-6 border-gray-200" />

            <!-- CHORUS -->
            <div class="chorus-box">
              <span class="chorus-title">
                Chorus (repeat after every stanza)
              </span>

              <div class="mt-3 space-y-1">
                <p
                  v-for="(line, li) in chorusLines"
                  :key="li"
                >
                  {{ line }}
                </p>
              </div>
            </div>

          </div>

        </div>

      </div>

    </section>

  </div>
</template>

<style scoped>

/* ================= CARD ================= */
.card {
  @apply bg-white rounded-2xl shadow-lg hover:shadow-2xl 
         transition duration-300 p-6 sm:p-8 
         border-t-4;
}

/* ================= TITLE ================= */
.card-title {
  @apply text-xl sm:text-2xl font-bold text-blue-900 mb-5;
}

/* ================= CONTENT ================= */
.card-content {
  @apply space-y-3 text-gray-700 leading-relaxed text-sm sm:text-base;
}

/* ================= CHORUS ================= */
.chorus-box {
  @apply bg-gray-50 p-4 rounded-xl text-gray-700 text-sm sm:text-base;
}

.chorus-title {
  @apply font-semibold text-blue-800 text-sm;
}

/* Ultra-wide readability */
@media (min-width: 1536px) {
  .max-w-3xl {
    max-width: 850px;
  }
}

</style>