<script>
import MainLayout from '@/Layouts/MainLayout.vue'

export default {
  layout: MainLayout,
}
</script>

<template>
  <div class="min-h-screen bg-slate-50">

    <!-- ================= HERO SECTION ================= -->
    <section
      class="relative bg-cover bg-center bg-no-repeat 
             h-[60vh] sm:h-[65vh] lg:h-[70vh] flex items-center"
      style="background-image: url('/images/complex.png');"
    >
      <!-- Overlay -->
      <div class="absolute inset-0 bg-black/60"></div>

      <!-- Content -->
      <div class="relative w-full 
                  max-w-[900px] md:max-w-[1100px] lg:max-w-[1300px] 
                  xl:max-w-[1500px] 2xl:max-w-[1700px] 
                  mx-auto px-4 sm:px-6 text-center text-white">

        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl 
                   font-extrabold tracking-wide mb-4">
          Head Teacher’s Message
        </h1>

        <p class="max-w-3xl mx-auto 
                  text-sm sm:text-base md:text-lg lg:text-xl 
                  text-gray-200 leading-relaxed">
          Academic leadership focused on holistic growth and consistent excellence.
        </p>

      </div>
    </section>

    <!-- ================= MAIN CONTENT ================= -->
    <section class="py-14 sm:py-16 lg:py-20 bg-gradient-to-b from-gray-100 to-white">

      <div class="w-full 
                  max-w-[1000px] md:max-w-[1200px] lg:max-w-[1400px] 
                  xl:max-w-[1600px] 2xl:max-w-[1800px] 
                  mx-auto px-4 sm:px-6 lg:px-8">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 xl:gap-14 items-start">

          <!-- ================= PROFILE CARD ================= -->
          <div class="lg:col-span-4 xl:col-span-3">

            <div class="bg-white rounded-2xl shadow-lg hover:shadow-2xl 
                        transition duration-300 
                        p-6 sm:p-8 text-center 
                        border-t-4 border-blue-900">

              <img
                src="/images/hm.jpg"
                alt="Wabwire Joseph"
                class="w-40 h-40 sm:w-48 sm:h-48 lg:w-52 lg:h-52 
                       mx-auto object-cover rounded-full 
                       border-4 border-blue-900 shadow-lg"
              />

              <h2 class="mt-6 text-xl sm:text-2xl font-bold text-blue-900">
                Wabwire Joseph
              </h2>

              <p class="text-gray-600 font-medium">
                Head Teacher
              </p>

              <p class="mt-4 text-gray-700 text-sm sm:text-base leading-relaxed">
                Provides strong academic leadership ensuring holistic development
                and consistent performance.
              </p>

            </div>

          </div>

          <!-- ================= MESSAGE ================= -->
          <div class="lg:col-span-8 xl:col-span-9">

            <div class="bg-white rounded-2xl shadow-lg hover:shadow-2xl 
                        transition duration-300 
                        p-6 sm:p-8 lg:p-10 xl:p-12 
                        border-t-4 border-yellow-500">

              <div class="max-w-3xl">

                <p class="text-gray-700 leading-relaxed mb-6 text-sm sm:text-base lg:text-lg">
                  Dear Students, Parents, and Members of the School Community,
                </p>

                <p class="text-gray-700 leading-relaxed mb-6 text-sm sm:text-base lg:text-lg">
                  As Head Teacher of
                  <span class="font-semibold text-blue-900">
                    St. Mark’s College Namagoma,
                  </span>
                  I am proud to lead a vibrant academic community committed to excellence,
                  discipline, and character formation.
                </p>

                <p class="text-gray-700 leading-relaxed mb-6 text-sm sm:text-base lg:text-lg">
                  Our primary focus is to ensure that every learner receives quality
                  education in a structured and supportive environment. We believe
                  that academic success must go hand in hand with moral growth,
                  leadership development, and personal responsibility.
                </p>

                <p class="text-gray-700 leading-relaxed mb-6 text-sm sm:text-base lg:text-lg">
                  Through teamwork among staff, students, and parents, we maintain
                  high academic standards while nurturing talents in co-curricular
                  activities. We strive to produce well-rounded individuals who are
                  prepared for higher education and meaningful contribution to society.
                </p>

                <p class="text-gray-700 leading-relaxed mb-6 text-sm sm:text-base lg:text-lg">
                  I encourage our students to remain disciplined, focused, and
                  determined in their pursuit of excellence. With dedication and
                  commitment, success is assured.
                </p>

                <p class="text-gray-700 leading-relaxed font-semibold text-blue-900 text-sm sm:text-base lg:text-lg">
                  Let us continue striving for consistent performance and holistic development.
                </p>

                <!-- Signature -->
                <div class="mt-10">
                  <p class="font-bold text-blue-900 text-base sm:text-lg">
                    Wabwire Joseph
                  </p>
                  <p class="text-gray-600 text-sm sm:text-base">
                    Head Teacher, St. Mark’s College Namagoma
                  </p>
                </div>

              </div>

            </div>

          </div>

        </div>

      </div>

    </section>

  </div>
</template>

<style scoped>
/* Improve readability on ultra-wide screens */
@media (min-width: 1536px) {
  .max-w-3xl {
    max-width: 850px;
  }
}
</style>