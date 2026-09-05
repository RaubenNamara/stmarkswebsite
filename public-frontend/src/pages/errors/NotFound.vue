<script setup lang="ts">
import { useHead } from '@unhead/vue'

withDefaults(defineProps<{ heading?: string; message?: string }>(), {
  heading: 'Page not found',
  message: "The page you're looking for doesn't exist.",
})

useHead({ title: 'Page not found' })
</script>

<template>
  <section class="mx-auto flex min-h-[60vh] max-w-xl flex-col items-center justify-center px-6 py-24 text-center">
    <p class="font-display text-7xl font-extrabold text-brand-navy/10">404</p>
    <h1 class="-mt-6 font-display text-2xl font-bold text-gray-900">{{ heading }}</h1>
    <p class="mt-2 text-gray-600">{{ message }}</p>
    <router-link to="/" class="btn mt-8">Return Home</router-link>
  </section>
</template>
