<script setup lang="ts">
defineProps<{ title: string; subtitle?: string | null }>()
</script>

<template>
  <section class="relative overflow-hidden bg-gradient-to-br from-brand-navy via-brand-navy to-brand-navy-dark px-6 py-16 text-center text-white sm:py-20">
    <div class="pointer-events-none absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, white 1px, transparent 1px); background-size: 28px 28px;" />
    <div class="pointer-events-none absolute -right-24 -top-24 h-64 w-64 rounded-full bg-brand-gold/10 blur-3xl" />
    <div class="pointer-events-none absolute -bottom-24 -left-24 h-64 w-64 rounded-full bg-white/5 blur-3xl" />
    <div class="relative mx-auto max-w-3xl">
      <div class="mx-auto mb-4 h-1 w-16 rounded-full bg-brand-gold" />
      <h1 class="font-display text-3xl font-bold tracking-tight sm:text-4xl">{{ title }}</h1>
      <p v-if="subtitle" class="mx-auto mt-4 max-w-2xl text-blue-100">{{ subtitle }}</p>
    </div>
  </section>
</template>
