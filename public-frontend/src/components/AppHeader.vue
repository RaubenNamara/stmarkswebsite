<script setup lang="ts">
import { ref } from 'vue'

const primaryLinks: Array<[string, string]> = [
  ['/about', 'About'],
  ['/academics', 'Academics'],
  ['/admissions', 'Admissions'],
  ['/news', 'News'],
  ['/staff', 'Staff'],
]

const moreLinks: Array<[string, string]> = [
  ['/explore/gallery', 'Gallery'],
  ['/clubs', 'Clubs'],
  ['/campus-voices', 'Campus Voices'],
  ['/empowerment-programmes', 'Empowerment'],
  ['/fee-structures', 'Fees'],
]

const allLinks = [...primaryLinks, ...moreLinks, ['/contact', 'Contact']] as Array<[string, string]>

const isOpen = ref(false)
</script>

<template>
  <header class="sticky top-0 z-50 bg-brand-navy/95 shadow-lg backdrop-blur">
    <nav class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-6 py-3.5">
      <router-link to="/" class="flex shrink-0 items-center gap-2.5">
        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-gold font-display text-sm font-extrabold text-brand-navy-dark">SM</span>
        <span class="font-display text-base font-bold leading-tight text-white sm:text-lg">St Mark's College<br class="hidden sm:block"> <span class="font-medium text-blue-200">Namagoma</span></span>
      </router-link>

      <ul class="hidden items-center gap-x-1 lg:flex">
        <li v-for="[href, label] in primaryLinks" :key="href">
          <router-link :to="href" class="rounded-md px-3 py-2 text-sm font-medium text-blue-100 transition hover:bg-white/10 hover:text-white">{{ label }}</router-link>
        </li>
        <li class="group relative">
          <button type="button" class="flex items-center gap-1 rounded-md px-3 py-2 text-sm font-medium text-blue-100 transition hover:bg-white/10 hover:text-white">
            More
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
          </button>
          <ul class="invisible absolute left-0 top-full mt-1 w-56 rounded-xl bg-white p-2 opacity-0 shadow-card ring-1 ring-black/5 transition group-hover:visible group-hover:opacity-100">
            <li v-for="[href, label] in moreLinks" :key="href">
              <router-link :to="href" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 hover:text-brand-navy">{{ label }}</router-link>
            </li>
          </ul>
        </li>
        <li><router-link to="/contact" class="rounded-md px-3 py-2 text-sm font-medium text-blue-100 transition hover:bg-white/10 hover:text-white">Contact</router-link></li>
      </ul>

      <router-link to="/apply" class="hidden shrink-0 rounded-lg bg-brand-gold px-4 py-2 text-sm font-bold text-brand-navy-dark shadow-md transition hover:-translate-y-0.5 hover:bg-yellow-400 hover:shadow-lg lg:inline-flex">
        Apply Now
      </router-link>

      <button
        type="button"
        :aria-expanded="isOpen"
        aria-label="Toggle menu"
        class="rounded-md p-2 text-blue-100 hover:bg-white/10 hover:text-white lg:hidden"
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

    <ul v-if="isOpen" class="flex flex-col gap-1 border-t border-white/10 px-6 pb-4 pt-2 lg:hidden">
      <li v-for="[href, label] in allLinks" :key="href">
        <router-link :to="href" class="block rounded-md px-2 py-2 text-sm font-medium text-blue-100 hover:bg-white/10 hover:text-white" @click="isOpen = false">{{ label }}</router-link>
      </li>
      <li class="pt-2">
        <router-link to="/apply" class="block rounded-lg bg-brand-gold px-4 py-2.5 text-center text-sm font-bold text-brand-navy-dark shadow-md" @click="isOpen = false">Apply Now</router-link>
      </li>
    </ul>
  </header>
</template>
