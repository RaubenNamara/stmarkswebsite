<script setup lang="ts">
import { useHead } from '@unhead/vue'
import PageHeader from '../components/PageHeader.vue'

useHead({ title: 'Core Values' })
</script>

<template>
  <PageHeader title="Core Values — GREET" />

  <section class="mx-auto max-w-6xl space-y-6 px-6 py-14">
    <div class="grid gap-6 sm:grid-cols-3 lg:grid-cols-5">
      <div class="card border-t-4 border-brand-navy text-center"><h3 class="font-semibold text-gray-900">Godliness</h3></div>
      <div class="card border-t-4 border-brand-gold text-center"><h3 class="font-semibold text-gray-900">Reliability</h3></div>
      <div class="card border-t-4 border-brand-navy text-center"><h3 class="font-semibold text-gray-900">Ethics</h3></div>
      <div class="card border-t-4 border-brand-gold text-center"><h3 class="font-semibold text-gray-900">Excellence</h3></div>
      <div class="card border-t-4 border-brand-navy text-center"><h3 class="font-semibold text-gray-900">Team Work</h3></div>
    </div>
    <div class="card">
      <p class="text-gray-600">These five values guide every member of the St. Mark's College Namagoma community — students, staff, and leadership alike — in pursuit of our motto, <strong>"To Be Not To Seem."</strong></p>
    </div>
  </section>
</template>
