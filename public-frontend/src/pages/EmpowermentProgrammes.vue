<script setup lang="ts">
import { useHead } from '@unhead/vue'
import PageHeader from '../components/PageHeader.vue'

useHead({ title: 'Empowerment Programmes' })
</script>

<template>
  <PageHeader
    title="Student Empowerment Programmes"
    subtitle="Empowerment programmes include leadership clubs, vocational workshops, and community projects designed to prepare students for life beyond school."
  />

  <section class="mx-auto max-w-6xl space-y-6 px-6 py-14">
    <div class="card">
      <p class="text-gray-600">We believe in nurturing confident, responsible and innovative individuals who can positively impact society.</p>
    </div>

    <div class="grid gap-6 sm:grid-cols-3">
      <div class="card">
        <h3 class="font-semibold text-gray-900">Leadership Clubs</h3>
        <p class="mt-2 text-sm text-gray-600">Student leadership councils, debate clubs, and mentorship programs that develop confidence and communication skills.</p>
      </div>
      <div class="card">
        <h3 class="font-semibold text-gray-900">Vocational Workshops</h3>
        <p class="mt-2 text-sm text-gray-600">Practical skills training including entrepreneurship, technology exposure and hands-on learning experiences.</p>
      </div>
      <div class="card">
        <h3 class="font-semibold text-gray-900">Community Projects</h3>
        <p class="mt-2 text-sm text-gray-600">Outreach programmes and service learning activities that encourage responsibility and teamwork.</p>
      </div>
    </div>

    <div class="card">
      <h3 class="font-semibold text-gray-900">Explore our programmes</h3>
      <p class="mt-2 flex flex-wrap gap-x-2 gap-y-1 text-sm">
        <router-link to="/empowerment/chaplaincy" class="text-brand-navy hover:underline">Chaplaincy</router-link> ·
        <router-link to="/empowerment/mentorship" class="text-brand-navy hover:underline">Mentorship</router-link> ·
        <router-link to="/empowerment/girl-boy-talk" class="text-brand-navy hover:underline">Girl - Boy Talk</router-link> ·
        <router-link to="/empowerment/inspiration-night" class="text-brand-navy hover:underline">Inspiration Night</router-link> ·
        <router-link to="/empowerment/smosa-alumni" class="text-brand-navy hover:underline">SMOSA Alumni</router-link> ·
        <router-link to="/empowerment/christmas-cantata" class="text-brand-navy hover:underline">Christmas Cantata</router-link> ·
        <router-link to="/smosa-feedback" class="text-brand-navy hover:underline">SMOSA Feedback</router-link>
      </p>
    </div>
  </section>
</template>
