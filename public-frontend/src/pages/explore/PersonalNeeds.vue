<script setup lang="ts">
import { useHead } from '@unhead/vue'
import PageHeader from '../../components/PageHeader.vue'

useHead({ title: 'Personal Needs' })
</script>

<template>
  <PageHeader title="Personal Needs" />

  <section class="mx-auto max-w-3xl px-6 py-14">
    <div class="card">
      <p class="text-gray-600">Students are expected to bring basic personal effects and toiletries for the term. A detailed personal needs list is provided to parents and guardians at the start of each term alongside the school calendar.</p>
      <p class="mt-3 text-gray-600">For the current list, please <router-link to="/contact" class="text-brand-navy hover:underline">contact the school office</router-link>.</p>
    </div>
  </section>
</template>
