<script setup lang="ts">
defineProps<{ title: string; subtitle?: string | null }>()
</script>

<template>
  <section class="bg-gradient-to-b from-brand-navy to-brand-navy-dark px-6 py-14 text-center text-white sm:py-16">
    <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">{{ title }}</h1>
    <p v-if="subtitle" class="mx-auto mt-3 max-w-2xl text-blue-100">{{ subtitle }}</p>
  </section>
</template>
