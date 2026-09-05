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
      <!-- Overlay -->
      <div class="absolute inset-0 bg-black/60"></div>

      <div class="relative max-w-[900px] md:max-w-[1100px] lg:max-w-[1300px] xl:max-w-[1500px] 2xl:max-w-[1700px] mx-auto px-4 sm:px-6 text-white text-center">

        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-extrabold mb-4 tracking-wide">
          Director’s Message
        </h1>

        <p class="max-w-3xl mx-auto text-sm sm:text-base md:text-lg lg:text-xl text-gray-200 leading-relaxed">
          Leadership, Vision and Excellence at St. Mark’s College Namagoma
        </p>

      </div>
    </div>

    <!-- ================= CONTENT SECTION ================= -->
    <div class="bg-gradient-to-b from-gray-100 to-white py-14 sm:py-16 lg:py-20">

      <div class="max-w-[1000px] md:max-w-[1200px] lg:max-w-[1400px] xl:max-w-[1600px] 2xl:max-w-[1800px] mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-10 xl:gap-14 items-start">

        <!-- ================= DIRECTOR PROFILE ================= -->
        <div class="lg:col-span-4 xl:col-span-3">

          <div class="bg-white rounded-2xl shadow-xl p-6 sm:p-8 text-center border-t-4 border-blue-900 hover:shadow-2xl transition duration-300">

            <img
              src="/images/director1.jpg"
              alt="Owek Ddamulira Daniel"
              class="w-40 h-40 sm:w-48 sm:h-48 lg:w-52 lg:h-52 mx-auto object-cover rounded-full border-4 border-blue-900 shadow-lg"
            />

            <h2 class="mt-6 text-xl sm:text-2xl font-bold text-blue-900">
              Owek Ddamulira Daniel
            </h2>

            <p class="text-gray-600 font-medium">
              Director
            </p>

            <p class="mt-4 text-gray-700 text-sm sm:text-base leading-relaxed">
              A visionary education leader committed to institutional growth,
              integrity and academic excellence.
            </p>

          </div>

        </div>

        <!-- ================= MESSAGE ================= -->
        <div class="lg:col-span-8 xl:col-span-9">

          <div class="bg-white rounded-2xl shadow-xl p-6 sm:p-8 lg:p-10 xl:p-12 border-t-4 border-yellow-500 hover:shadow-2xl transition duration-300">

            <div class="max-w-3xl">

              <p class="text-gray-700 leading-relaxed mb-6 text-sm sm:text-base lg:text-lg">
                Dear Students, Parents, Staff, and Esteemed Visitors,
              </p>

              <p class="text-gray-700 leading-relaxed mb-6 text-sm sm:text-base lg:text-lg">
                It is my great pleasure to welcome you to
                <span class="font-semibold text-blue-900">
                  St. Mark’s College Namagoma — The Higher Achiever’s College.
                </span>
              </p>

              <p class="text-gray-700 leading-relaxed mb-6 text-sm sm:text-base lg:text-lg">
                At St. Mark’s College, we believe that education is the foundation
                of transformation. Our mission is to nurture disciplined, confident,
                and competent learners who are prepared not only for examinations,
                but for life.
              </p>

              <p class="text-gray-700 leading-relaxed mb-6 text-sm sm:text-base lg:text-lg">
                We are committed to fostering academic excellence, moral integrity,
                leadership, and the development of individual talents. Through a
                dedicated team of qualified staff, a structured learning environment,
                and a strong value system rooted in faith and discipline, we strive
                to empower every learner to achieve their highest potential.
              </p>

              <p class="text-gray-700 leading-relaxed mb-6 text-sm sm:text-base lg:text-lg">
                Our focus remains on producing responsible citizens who will
                contribute positively to their communities and to the development
                of our motherland.
              </p>

              <p class="text-gray-700 leading-relaxed font-semibold text-blue-900 text-sm sm:text-base lg:text-lg">
                Together, let us continue striving for excellence.
              </p>

              <!-- Signature -->
              <div class="mt-10">
                <p class="font-bold text-blue-900 text-base sm:text-lg">
                  Owek Ddamulira Daniel
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
/* Slight polish for ultra-wide screens */
@media (min-width: 1536px) {
  .max-w-3xl {
    max-width: 850px;
  }
}
</style>