<template>
  <div class="min-h-screen bg-gray-50 text-gray-800">
    <main class="max-w-[1400px] xl:max-w-[1600px] 2xl:max-w-[1800px] mx-auto px-4 sm:px-6 lg:px-10 py-12">

      <!-- GRID -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 xl:gap-16 items-center">

        <!-- LEFT CONTENT -->
        <section class="lg:col-span-7 xl:col-span-6 space-y-6">
          <h1 class="text-3xl sm:text-4xl lg:text-5xl xl:text-6xl font-extrabold text-blue-900 leading-tight">
            Join our team - Shape the future with St Mark's College
          </h1>

          <p class="text-gray-700 text-base sm:text-lg lg:text-xl max-w-2xl">
            Are you a dedicated professional who loves education and community development?
            We invite passionate teachers and support staff to apply and become part of a team
            that nurtures excellence. Click <strong>Continue</strong> to access the application form
            and submit your interest.
          </p>

          <!-- BUTTON -->
          <div class="mt-6">
            <Link
              :href="route('apply')"
              class="inline-flex items-center justify-center px-6 py-3 rounded-md bg-yellow-400 text-blue-950 font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition"
            >
              Continue to application form
              <svg class="w-4 h-4 ml-3" viewBox="0 0 20 20" fill="none">
                <path d="M7 4l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
            </Link>
          </div>

          <!-- FEATURES -->
          <ul class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl">
            <li class="flex items-start gap-3">
              <div class="h-10 w-10 flex items-center justify-center rounded-full bg-blue-900 text-white">✓</div>
              <div>
                <div class="font-semibold text-blue-900">Supportive environment</div>
                <div class="text-sm text-gray-600">Professional development and mentorship.</div>
              </div>
            </li>

            <li class="flex items-start gap-3">
              <div class="h-10 w-10 flex items-center justify-center rounded-full bg-blue-900 text-white">★</div>
              <div>
                <div class="font-semibold text-blue-900">Competitive benefits</div>
                <div class="text-sm text-gray-600">Fair pay and a friendly workplace.</div>
              </div>
            </li>
          </ul>
        </section>

        <!-- RIGHT PANEL -->
        <aside class="lg:col-span-5 xl:col-span-6 flex justify-center">
          <div class="w-full max-w-md xl:max-w-lg 2xl:max-w-xl 
                      rounded-2xl shadow-xl bg-white 
                      p-8 sm:p-10 xl:p-12 
                      flex flex-col justify-center items-center text-center 
                      border border-blue-100">

            <div class="bg-blue-100 p-5 sm:p-6 rounded-full mb-6">
              <svg class="w-12 h-12 sm:w-14 sm:h-14 text-blue-900"
                   viewBox="0 0 24 24"
                   fill="none"
                   stroke="currentColor"
                   stroke-width="1.5">
                <path d="M12 2l4 4-4 4-4-4 4-4z"/>
                <path d="M4 20v-2a4 4 0 014-4h8a4 4 0 014 4v2"/>
              </svg>
            </div>

            <h3 class="text-2xl sm:text-3xl xl:text-4xl font-bold text-blue-900">
              Build your career with us
            </h3>

            <p class="mt-4 text-blue-800 text-base sm:text-lg max-w-sm">
              A professional environment committed to excellence,
              integrity, and growth.
            </p>

          </div>
        </aside>
      </div>

      <!-- WHY JOIN -->
      <section id="why" class="mt-16">
        <div class="bg-white rounded-2xl shadow px-6 py-8 xl:px-10 xl:py-10">
          <h2 class="text-2xl sm:text-3xl font-extrabold text-blue-900">
            Why work with us?
          </h2>

          <p class="mt-3 text-gray-700 max-w-3xl">
            At St Mark's College Namagoma we believe in empowering staff to deliver high-quality education.
            We prioritize continuous learning, wellbeing, and collaboration.
          </p>

          <div class="mt-8 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <div class="p-5 border rounded-lg hover:shadow-md transition">
              <div class="font-semibold text-blue-900">Professional Growth</div>
              <div class="text-sm text-gray-600 mt-1">
                Workshops and career progression opportunities.
              </div>
            </div>

            <div class="p-5 border rounded-lg hover:shadow-md transition">
              <div class="font-semibold text-blue-900">Inclusive Culture</div>
              <div class="text-sm text-gray-600 mt-1">
                A respectful and collaborative environment.
              </div>
            </div>

            <div class="p-5 border rounded-lg hover:shadow-md transition">
              <div class="font-semibold text-blue-900">Community Impact</div>
              <div class="text-sm text-gray-600 mt-1">
                Help shape young minds and create change.
              </div>
            </div>
          </div>
        </div>
      </section>

    </main>
  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const pageTitle = computed(() => page.props.pageTitle ?? 'Career Guidance')

if (typeof document !== 'undefined') {
  document.title = pageTitle.value
}
</script>

<script>
import MainLayout from '@/Layouts/MainLayout.vue'

export default {
  layout: MainLayout
}
</script>

<style scoped>
/* Optional subtle enhancement */
@media (min-width: 1536px) {
  main {
    padding-left: 4rem;
    padding-right: 4rem;
  }
}
</style>