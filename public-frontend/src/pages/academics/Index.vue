<script setup lang="ts">
import { useHead } from '@unhead/vue'
import PageHeader from '../../components/PageHeader.vue'

useHead({ title: 'Academics' })
</script>

<template>
  <PageHeader title="Academics" />

  <section class="mx-auto max-w-6xl space-y-6 px-6 py-14">
    <div class="card">
      <h2 class="text-xl font-bold text-gray-900">Academic Excellence</h2>
      <p class="mt-3 text-gray-600">Our academic programs are designed to develop competence and confidence in learners.</p>
      <p class="mt-3 text-gray-600">We focus on building strong foundations in sciences, arts, and humanities, while nurturing critical thinking, creativity and leadership skills.</p>
    </div>

    <div class="grid gap-6 sm:grid-cols-3">
      <div class="card">
        <h3 class="font-semibold text-gray-900">Lower Secondary</h3>
        <p class="mt-2 text-sm text-gray-600">A strong academic foundation with emphasis on discipline, character formation and academic excellence.</p>
      </div>
      <div class="card">
        <h3 class="font-semibold text-gray-900">Upper Secondary</h3>
        <p class="mt-2 text-sm text-gray-600">Advanced learning programs preparing students for higher education and professional careers.</p>
      </div>
      <div class="card">
        <h3 class="font-semibold text-gray-900">Co-Curricular Activities</h3>
        <p class="mt-2 text-sm text-gray-600">Sports, debates, clubs and leadership programs that promote holistic development.</p>
      </div>
    </div>

    <div class="card">
      <h3 class="font-semibold text-gray-900">More</h3>
      <p class="mt-2 flex flex-wrap gap-x-2 gap-y-1 text-sm">
        <router-link to="/academics/curriculum" class="text-brand-navy hover:underline">Curriculum</router-link> ·
        <router-link to="/academics/uneb-results" class="text-brand-navy hover:underline">UNEB Results</router-link> ·
        <router-link to="/academics/circulars" class="text-brand-navy hover:underline">Circulars</router-link> ·
        <router-link to="/academics/school-calendar" class="text-brand-navy hover:underline">School Calendar</router-link> ·
        <router-link to="/academics/co-curricular" class="text-brand-navy hover:underline">Co-Curricular</router-link> ·
        <router-link to="/academics/high-achievers" class="text-brand-navy hover:underline">High Achievers</router-link>
      </p>
    </div>
  </section>
</template>
