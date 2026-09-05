<script setup lang="ts">
import { useHead } from '@unhead/vue'
import PageHeader from '../../components/PageHeader.vue'

useHead({ title: 'School Calendar' })
</script>

<template>
  <PageHeader title="School Calendar" subtitle="Key dates for the academic year." />

  <section class="mx-auto max-w-3xl px-6 py-14">
    <div class="card">
      <p class="text-gray-600">The school calendar, including term dates, holidays and key academic events, is shared with parents and guardians at the start of each term.</p>
      <p class="mt-3 text-gray-600">For the current term's calendar, please <router-link to="/contact" class="text-brand-navy hover:underline">contact the school office</router-link>.</p>
    </div>
  </section>
</template>
