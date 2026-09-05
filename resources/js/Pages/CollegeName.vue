<script>
import MainLayout from '@/Layouts/MainLayout.vue'

export default {
  layout: MainLayout,
}
</script>

<template>
  <div class="min-h-screen bg-slate-50">

    <!-- ================= HERO ================= -->
    <section
      class="relative bg-cover bg-center bg-no-repeat 
             h-[60vh] sm:h-[65vh] lg:h-[75vh] flex items-center"
      style="background-image: url('/images/complex.png');"
    >
      <!-- Overlay (FIXED) -->
      <div class="absolute inset-0 bg-black/60"></div>

      <div class="relative w-full 
                  max-w-[900px] md:max-w-[1100px] lg:max-w-[1300px] 
                  xl:max-w-[1500px] 2xl:max-w-[1700px] 
                  mx-auto px-4 sm:px-6 text-white text-center">

        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl 
                   font-extrabold mb-6 tracking-wide">
          The College Name
        </h1>

        <p class="max-w-3xl mx-auto 
                  text-sm sm:text-base md:text-lg lg:text-xl 
                  leading-relaxed text-gray-200">
          Deciding on the name of an institution can be very difficult,
          especially for a school whose name many people will have strong
          attachment throughout their lives.
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

          <!-- CARD 1 -->
          <div class="card border-blue-900">
            <h2 class="card-title">
              1. God at the Center
            </h2>

            <div class="card-content">
              <p>
                The name immediately relates to Christianity and hence God.
                It is a beautiful idea to have God at the center of the College.
              </p>

              <p>
                St. Mark’s style of writing in the bible emphasized Jesus’ actions
                as opposed to words. This is a good guide on how to work.
              </p>

              <p class="highlight">
                “Action not words determine our destiny”.
              </p>
            </div>
          </div>

          <!-- CARD 2 -->
          <div class="card border-yellow-500">
            <h2 class="card-title">
              2. Cultural & Identity Value
            </h2>

            <div class="card-content">
              <p>
                It is a standard way of naming schools and colleges in Uganda.
                This makes it generally acceptable or would receive limited criticism.
              </p>

              <p>
                The name is generally easy to remember.
                Ugandans and non-Ugandans can easily remember and pronounce St. Mark.
              </p>
            </div>
          </div>

          <!-- CARD 3 -->
          <div class="card border-blue-700">
            <h2 class="card-title">
              3. Location & Uniqueness
            </h2>

            <div class="card-content">
              <p>
                The addition of Namagoma gives it the location address
                to avoid any possible confusion with any other St. Mark’s
                within and outside Uganda.
              </p>

              <p>
                There are not many schools in Uganda with that name.
                Only one Secondary School.
              </p>

              <p class="highlight">
                This makes it different and Unique.
              </p>
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
  @apply text-xl sm:text-2xl font-bold text-blue-900 mb-6;
}

/* ================= CONTENT ================= */
.card-content {
  @apply space-y-4 text-gray-700 leading-relaxed text-sm sm:text-base;
}

/* ================= HIGHLIGHT ================= */
.highlight {
  @apply font-semibold text-blue-900 text-base sm:text-lg;
}

/* Ultra-wide readability */
@media (min-width: 1536px) {
  .max-w-3xl {
    max-width: 850px;
  }
}

</style>