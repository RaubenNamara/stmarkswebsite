<script setup lang="ts">
import { useHead } from '@unhead/vue'

useHead({ title: 'Admissions' })
</script>

<template>
  <section class="relative overflow-hidden bg-gradient-to-br from-brand-navy via-brand-navy to-brand-navy-dark px-6 py-24 text-center text-white sm:py-28">
    <div class="pointer-events-none absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, white 1px, transparent 1px); background-size: 28px 28px;" />
    <div class="pointer-events-none absolute -right-32 -top-32 h-96 w-96 rounded-full bg-brand-gold/10 blur-3xl" />
    <div class="relative mx-auto max-w-xl">
      <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-brand-gold/20 text-4xl">🌟</div>
      <h1 class="mt-6 font-display text-3xl font-extrabold tracking-tight sm:text-4xl">Ready to Join St Mark's College Namagoma?</h1>
      <p class="mx-auto mt-4 max-w-xl text-blue-100">Click the button below to begin your online application and take the next step toward your future.</p>
      <a href="https://stmark.sc.ug/stmarks_admission/" target="_blank" rel="noopener noreferrer" class="btn btn-gold mt-8">Apply Online &rarr;</a>
    </div>
  </section>
</template>
