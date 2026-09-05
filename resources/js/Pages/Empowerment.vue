<template>
  <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">

    <!-- Page Title -->
    <h1 class="text-3xl md:text-4xl font-bold mb-8 text-gray-900">
      {{ pageTitle }}
    </h1>

    <!-- Introduction Section -->
    <section class="bg-white p-8 rounded-lg shadow mb-8">
      <h2 class="text-2xl font-semibold mb-4">
        Student Empowerment Programmes
      </h2>

      <p class="text-gray-700 mb-4">
        Empowerment programmes include leadership clubs, vocational workshops,
        and community projects designed to prepare students for life beyond school.
      </p>

      <p class="text-gray-700">
        We believe in nurturing confident, responsible and innovative individuals
        who can positively impact society.
      </p>
    </section>

    <!-- Programmes Grid -->
    <div class="grid md:grid-cols-3 gap-6">

      <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
        <h3 class="text-xl font-semibold mb-3">Leadership Clubs</h3>
        <p class="text-gray-600">
          Student leadership councils, debate clubs, and mentorship programs
          that develop confidence and communication skills.
        </p>
      </div>

      <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
        <h3 class="text-xl font-semibold mb-3">Vocational Workshops</h3>
        <p class="text-gray-600">
          Practical skills training including entrepreneurship,
          technology exposure and hands-on learning experiences.
        </p>
      </div>

      <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
        <h3 class="text-xl font-semibold mb-3">Community Projects</h3>
        <p class="text-gray-600">
          Outreach programmes and service learning activities
          that encourage responsibility and teamwork.
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