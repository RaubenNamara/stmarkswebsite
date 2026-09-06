<script setup lang="ts">
import { useHead } from '@unhead/vue'
import PageHeader from '../../components/PageHeader.vue'

useHead({ title: 'Career Guidance' })
</script>

<template>
  <PageHeader
    title="Join our team — Shape the future with St Mark's College"
    subtitle="Are you a dedicated professional who loves education and community development? We invite passionate teachers and support staff to apply and become part of a team that nurtures excellence."
  />

  <section class="mx-auto max-w-7xl px-6 py-14">
    <router-link to="/apply" class="btn btn-gold">Continue to application form &rarr;</router-link>

    <div class="card mt-8">
      <h2 class="text-xl font-bold text-gray-900">Why work with us?</h2>
      <p class="mt-3 text-gray-600">At St Mark's College Namagoma we believe in empowering staff to deliver high-quality education. We prioritize continuous learning, wellbeing, and collaboration.</p>
      <div class="mt-6 grid gap-6 sm:grid-cols-3">
        <div><strong class="text-gray-900">Professional Growth</strong><p class="mt-1 text-sm text-gray-600">Workshops and career progression opportunities.</p></div>
        <div><strong class="text-gray-900">Inclusive Culture</strong><p class="mt-1 text-sm text-gray-600">A respectful and collaborative environment.</p></div>
        <div><strong class="text-gray-900">Community Impact</strong><p class="mt-1 text-sm text-gray-600">Help shape young minds and create change.</p></div>
      </div>
    </div>
  </section>
</template>
