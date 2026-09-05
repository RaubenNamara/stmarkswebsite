<script>
import MainLayout from '@/Layouts/MainLayout.vue'

export default {
  layout: MainLayout,
}
</script>

<template>
  <div>

    <!-- ================= HERO SECTION ================= -->
    <div
      class="relative bg-cover bg-center bg-no-repeat h-[60vh] sm:h-[65vh] lg:h-[70vh] flex items-center"
      style="background-image: url('/images/complex.png');"
    >
      <!-- Overlay (FIXED) -->
      <div class="absolute inset-0 bg-black/60"></div>

      <div class="relative max-w-[900px] md:max-w-[1100px] lg:max-w-[1300px] xl:max-w-[1500px] 2xl:max-w-[1700px] mx-auto px-4 sm:px-6 text-white text-center">

        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-extrabold mb-4 tracking-wide">
          Director’s Message
        </h1>

        <p class="max-w-3xl mx-auto text-sm sm:text-base md:text-lg lg:text-xl text-gray-200 leading-relaxed">
          Leadership grounded in discipline, excellence and moral integrity.
        </p>

      </div>
    </div>

    <!-- ================= CONTENT ================= -->
    <div class="bg-gradient-to-b from-gray-100 to-white py-14 sm:py-16 lg:py-20">

      <div class="max-w-[1000px] md:max-w-[1200px] lg:max-w-[1400px] xl:max-w-[1600px] 2xl:max-w-[1800px] mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-10 xl:gap-14 items-start">

        <!-- ================= PROFILE ================= -->
        <div class="lg:col-span-4 xl:col-span-3">

          <div class="bg-white rounded-2xl shadow-xl p-6 sm:p-8 text-center border-t-4 border-blue-900 hover:shadow-2xl transition duration-300">

            <img
              src="/images/director2.jpg"
              alt="Canon Alice Ddamulira"
              class="w-40 h-40 sm:w-48 sm:h-48 lg:w-52 lg:h-52 mx-auto object-cover rounded-full border-4 border-blue-900 shadow-lg"
            />

            <h2 class="mt-6 text-xl sm:text-2xl font-bold text-blue-900">
              Canon Alice Ddamulira
            </h2>

            <p class="text-gray-600 font-medium">
              Director
            </p>

            <p class="mt-4 text-gray-700 text-sm sm:text-base leading-relaxed">
              Dedicated to nurturing discipline, excellence and strong moral values
              within the school community.
            </p>

          </div>

        </div>

        <!-- ================= MESSAGE ================= -->
        <div class="lg:col-span-8 xl:col-span-9">

          <div class="bg-white rounded-2xl shadow-xl p-6 sm:p-8 lg:p-10 xl:p-12 border-t-4 border-yellow-500 hover:shadow-2xl transition duration-300">

            <div class="max-w-3xl">

              <p class="text-gray-700 leading-relaxed mb-6 text-sm sm:text-base lg:text-lg">
                Dear Students, Parents, and Members of our School Community,
              </p>

              <p class="text-gray-700 leading-relaxed mb-6 text-sm sm:text-base lg:text-lg">
                It is an honour to serve as Director of
                <span class="font-semibold text-blue-900">
                  St. Mark’s College Namagoma.
                </span>
                Our school stands as a pillar of discipline, excellence, and moral
                uprightness, values that guide everything we do.
              </p>

              <p class="text-gray-700 leading-relaxed mb-6 text-sm sm:text-base lg:text-lg">
                We believe that education must shape not only the mind but also
                the character of every learner. Through structured discipline,
                quality teaching, and mentorship, we aim to cultivate responsible,
                confident, and morally grounded individuals.
              </p>

              <p class="text-gray-700 leading-relaxed mb-6 text-sm sm:text-base lg:text-lg">
                At St. Mark’s College, excellence is not just a goal — it is a culture.
                We encourage our students to work diligently, respect authority,
                embrace teamwork, and develop their talents to the fullest.
              </p>

              <p class="text-gray-700 leading-relaxed mb-6 text-sm sm:text-base lg:text-lg">
                With the support of our committed staff and valued parents,
                we continue to build a learning environment that prepares our
                students for both academic success and life beyond school.
              </p>

              <p class="text-gray-700 leading-relaxed font-semibold text-blue-900 text-sm sm:text-base lg:text-lg">
                Together, let us continue building a future rooted in integrity and excellence.
              </p>

              <!-- Signature -->
              <div class="mt-10">
                <p class="font-bold text-blue-900 text-base sm:text-lg">
                  Canon Alice Ddamulira
                </p>
                <p class="text-gray-600 text-sm sm:text-base">
                  Director, St. Mark’s College Namagoma
                </p>
              </div>

            </div>

          </div>

        </div>

      </div>

    </div>

  </div>
</template>

<style scoped>
/* Ultra-wide readability improvement */
@media (min-width: 1536px) {
  .max-w-3xl {
    max-width: 850px;
  }
}
</style>