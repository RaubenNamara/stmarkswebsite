<script setup lang="ts">
import { useHead } from '@unhead/vue'

useHead({ title: 'Admissions' })
</script>

<template>
  <section class="bg-gradient-to-b from-brand-navy to-brand-navy-dark px-6 py-16 text-center text-white sm:py-20">
    <div class="text-5xl">🌟</div>
    <h1 class="mt-4 text-3xl font-bold tracking-tight sm:text-4xl">Ready to Join St Mark's College Namagoma?</h1>
    <p class="mx-auto mt-3 max-w-xl text-blue-100">Click the button below to begin your online application and take the next step toward your future.</p>
    <a href="https://stmark.sc.ug/stmarks_admission/" target="_blank" rel="noopener noreferrer" class="btn btn-gold mt-8">Apply Online &rarr;</a>
  </section>
</template>
