<script setup lang="ts">
import { useHead } from '@unhead/vue'
import PageHeader from '../components/PageHeader.vue'

useHead({ title: "Director's Message" })
</script>

<template>
  <PageHeader title="Director's Message" />

  <section class="mx-auto grid max-w-6xl gap-6 px-6 py-14 md:grid-cols-[280px_1fr] md:items-start">
    <div class="card border-t-4 border-brand-navy text-center">
      <img :src="'/images/director2.jpg'" alt="Canon Alice Ddamulira" class="mx-auto mb-4 h-40 w-40 rounded-full border-4 border-brand-navy object-cover">
      <h2 class="text-lg font-bold text-brand-navy">Canon Alice Ddamulira</h2>
      <p class="font-semibold text-gray-600">Director</p>
      <p class="mt-2 text-sm text-gray-600">Dedicated to nurturing discipline, excellence and strong moral values within the school community.</p>
    </div>

    <div class="card border-t-4 border-brand-gold">
      <p class="text-gray-600">Dear Students, Parents, and Members of our School Community,</p>
      <p class="mt-3 text-gray-600">It is an honour to serve as Director of <strong>St. Mark's College Namagoma.</strong> Our school stands as a pillar of discipline, excellence, and moral uprightness, values that guide everything we do.</p>
      <p class="mt-3 text-gray-600">We believe that education must shape not only the mind but also the character of every learner. Through structured discipline, quality teaching, and mentorship, we aim to cultivate responsible, confident, and morally grounded individuals.</p>
      <p class="mt-3 text-gray-600">At St. Mark's College, excellence is not just a goal — it is a culture. We encourage our students to work diligently, respect authority, embrace teamwork, and develop their talents to the fullest.</p>
      <p class="mt-3 text-gray-600">With the support of our committed staff and valued parents, we continue to build a learning environment that prepares our students for both academic success and life beyond school.</p>
      <p class="mt-3 font-semibold text-gray-800">Together, let us continue building a future rooted in integrity and excellence.</p>
      <div class="mt-6 font-bold text-brand-navy">
        Canon Alice Ddamulira<br>
        <span class="font-normal text-gray-600">Director, St. Mark's College Namagoma</span>
      </div>
    </div>
  </section>
</template>
