<template>
  <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">

    <!-- Page Title -->
    <h1 class="text-3xl md:text-4xl font-bold mb-8 text-gray-900">
      {{ pageTitle }}
    </h1>

    <!-- Intro Section -->
    <section class="bg-white p-8 rounded-lg shadow mb-8">
      <h2 class="text-2xl font-semibold mb-4">
        Academic Excellence
      </h2>

      <p class="text-gray-700 mb-4">
        Our academic programs are designed to develop competence and confidence in learners.
      </p>

      <p class="text-gray-700">
        We focus on building strong foundations in sciences, arts, and humanities,
        while nurturing critical thinking, creativity and leadership skills.
      </p>
    </section>

    <!-- Programs Grid -->
    <div class="grid md:grid-cols-3 gap-6">

      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-xl font-semibold mb-3">Lower Secondary</h3>
        <p class="text-gray-600">
          A strong academic foundation with emphasis on discipline,
          character formation and academic excellence.
        </p>
      </div>

      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-xl font-semibold mb-3">Upper Secondary</h3>
        <p class="text-gray-600">
          Advanced learning programs preparing students
          for higher education and professional careers.
        </p>
      </div>

      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-xl font-semibold mb-3">Co-Curricular Activities</h3>
        <p class="text-gray-600">
          Sports, debates, clubs and leadership programs
          that promote holistic development.
        </p>
      </div>

    </div>

  </div>
</template>

<script setup>
defineProps({
  pageTitle: String,
  canLogin: Boolean
})
</script>

<script>
import MainLayout from '@/Layouts/MainLayout.vue'

export default {
  layout: MainLayout
}
</script>