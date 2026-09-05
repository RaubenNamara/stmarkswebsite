<script setup lang="ts">
import { ref } from 'vue'

const navLinks: Array<[string, string]> = [
  ['/about', 'About'],
  ['/academics', 'Academics'],
  ['/admissions', 'Admissions'],
  ['/news', 'News'],
  ['/staff', 'Staff'],
  ['/explore/gallery', 'Gallery'],
  ['/clubs', 'Clubs'],
  ['/campus-voices', 'Campus Voices'],
  ['/empowerment-programmes', 'Empowerment'],
  ['/fee-structures', 'Fees'],
  ['/apply', 'Careers'],
  ['/contact', 'Contact'],
]

const isOpen = ref(false)
</script>

<template>
  <header class="sticky top-0 z-50 bg-brand-navy shadow-md">
    <nav class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-6 py-4">
      <router-link to="/" class="shrink-0 text-lg font-bold text-white">St Mark's College Namagoma</router-link>

      <ul class="hidden flex-wrap items-center gap-x-6 gap-y-2 md:flex">
        <li v-for="[href, label] in navLinks" :key="href">
          <router-link :to="href" class="text-sm font-medium text-blue-100 transition hover:text-white">{{ label }}</router-link>
        </li>
      </ul>

      <button
        type="button"
        :aria-expanded="isOpen"
        aria-label="Toggle menu"
        class="rounded-md p-2 text-blue-100 hover:bg-white/10 hover:text-white md:hidden"
        @click="isOpen = !isOpen"
      >
        <svg v-if="!isOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </nav>

    <ul v-if="isOpen" class="flex flex-col gap-1 border-t border-white/10 px-6 pb-4 md:hidden">
      <li v-for="[href, label] in navLinks" :key="href">
        <router-link :to="href" class="block rounded-md px-2 py-2 text-sm font-medium text-blue-100 hover:bg-white/10 hover:text-white" @click="isOpen = false">{{ label }}</router-link>
      </li>
    </ul>
  </header>
</template>
