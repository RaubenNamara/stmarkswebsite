<script setup lang="ts">
import TopBar from './TopBar.vue'
import { staticAsset } from '../utils/staticAsset'

type LinkItem = [string, string]
interface MenuItem { label: string; to?: string; children?: LinkItem[] }

const menu: MenuItem[] = [
  { label: 'Home', to: '/' },
  {
    label: 'Explore',
    children: [
      ['/explore/career', 'Career'],
      ['/fee-structures', 'Fees, Rules & Calendar'],
      ['/explore/student-leadership', 'Student Leadership'],
      ['/explore/uniform', 'Uniform'],
      ['/explore/gallery', 'Gallery'],
      ['/campus-voices', 'Campus Voices'],
    ],
  },
  {
    label: 'About',
    to: '/about',
    children: [
      ['/staff', 'Staff'],
      ['/board-members', 'Board Members'],
      ['/school-anthem', 'School Anthem'],
      ['/college-name', 'College Name'],
      ['/clubs', 'Clubs'],
    ],
  },
  {
    label: 'eCampus',
    children: [
      ['/elearning', 'eLearning'],
      ['/evoting', 'eVoting System'],
      ['/cybermonitor', 'Cyber Monitor'],
      ['/econcerting', 'eConcerting'],
    ],
  },
  {
    label: 'Academics',
    to: '/academics',
    children: [
      ['/academics/curriculum', 'Curriculum'],
      ['/academics/co-curricular', 'Co-Curricular'],
      ['/performance', 'Performance & Circulars'],
      ['/academics/high-achievers', "High Achiever's Page"],
    ],
  },
  {
    label: 'Empowerment',
    to: '/empowerment-programmes',
    children: [
      ['/empowerment/chaplaincy', 'Chaplaincy'],
      ['/empowerment/mentorship', 'Mentorship Programme'],
      ['/empowerment/girl-boy-talk', 'Girl-Boy Talk'],
      ['/empowerment/inspiration-night', 'Inspiration Night'],
      ['/empowerment/smosa-alumni', 'SMOSA Alumni'],
      ['/smosa-feedback', 'SMOSA Feedback'],
      ['/empowerment/christmas-cantata', 'Christmas Cantata'],
    ],
  },
  { label: 'Contact', to: '/contact' },
]

import { ref, watch } from 'vue'
const isOpen = ref(false)
const openMobileSection = ref<string | null>(null)
const logoFailed = ref(false)

function toggleMobileSection(label: string) {
  openMobileSection.value = openMobileSection.value === label ? null : label
}

function closeDrawer() {
  isOpen.value = false
}

// Lock page scroll behind the drawer while it's open. Guarded for SSG, where this file also
// runs server-side and `document` doesn't exist.
watch(isOpen, (open) => {
  if (typeof document !== 'undefined') {
    document.body.style.overflow = open ? 'hidden' : ''
  }
})
</script>

<template>
  <TopBar />

  <header class="sticky top-0 z-50 border-b border-black/5 bg-amber-50 shadow-sm">
    <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-6 py-3">
      <router-link to="/" class="flex min-w-0 shrink items-center gap-2 sm:gap-3">
        <img v-if="!logoFailed" :src="staticAsset('images/logo.png')" alt="St Mark's College Namagoma crest" class="h-10 w-10 shrink-0 rounded-full border-2 border-brand-navy bg-white object-contain shadow-sm sm:h-14 sm:w-14" @error="logoFailed = true">
        <span v-else class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 border-brand-navy bg-white font-display text-sm font-extrabold text-brand-navy shadow-sm sm:h-14 sm:w-14 sm:text-lg">SM</span>
        <span class="min-w-0 leading-tight">
          <span class="block truncate font-display text-sm font-extrabold text-brand-navy sm:text-base">St Mark's College Namagoma</span>
          <span class="hidden truncate text-xs italic text-brand-navy/70 sm:block">The Higher Achiever's College</span>
        </span>
      </router-link>

      <ul class="hidden items-center gap-1 xl:flex">
        <li v-for="item in menu" :key="item.label" class="group relative">
          <router-link
            v-if="item.to"
            :to="item.to"
            class="flex items-center gap-1 rounded-md px-3 py-2 text-sm font-semibold text-brand-navy transition group-hover:bg-brand-gold/20"
          >
            {{ item.label }}
            <svg v-if="item.children" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition group-hover:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
          </router-link>
          <button
            v-else
            type="button"
            class="flex items-center gap-1 rounded-md px-3 py-2 text-sm font-semibold text-brand-navy transition group-hover:bg-brand-gold/20"
          >
            {{ item.label }}
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition group-hover:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
          </button>

          <ul v-if="item.children" class="invisible absolute left-0 top-full mt-1 w-60 rounded-xl bg-white p-2 opacity-0 shadow-card ring-1 ring-black/5 transition group-hover:visible group-hover:opacity-100">
            <li v-for="[href, label] in item.children" :key="href">
              <router-link :to="href" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-brand-navy/5 hover:text-brand-navy">{{ label }}</router-link>
            </li>
          </ul>
        </li>
      </ul>

      <button
        type="button"
        :aria-expanded="isOpen"
        aria-label="Open menu"
        class="rounded-md p-2 text-brand-navy hover:bg-black/5 xl:hidden"
        @click="isOpen = true"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </nav>
  </header>

  <!-- Off-canvas mobile menu: backdrop + drawer sliding in from the right. Both stay in the DOM
       (rather than v-if) so the slide/fade is an actual transition, not a hard cut. -->
  <div
    class="fixed inset-0 z-[60] bg-black/50 transition-opacity duration-300 xl:hidden"
    :class="isOpen ? 'opacity-100' : 'pointer-events-none opacity-0'"
    aria-hidden="true"
    @click="closeDrawer"
  />
  <aside
    class="fixed inset-y-0 right-0 z-[70] flex w-80 max-w-[85vw] flex-col overflow-hidden rounded-3xl bg-gradient-to-b from-white via-white to-gray-50 shadow-2xl ring-1 ring-black/5 transition-transform duration-300 ease-out xl:hidden"
    :class="isOpen ? 'translate-x-0' : 'translate-x-full'"
    role="dialog"
    aria-modal="true"
    aria-label="Site menu"
  >
    <div class="relative shrink-0 overflow-hidden bg-gradient-to-br from-brand-navy via-brand-navy to-brand-navy-dark px-5 py-5">
      <div class="pointer-events-none absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 85% 15%, white 1px, transparent 1px); background-size: 20px 20px;" />
      <div class="relative flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <img v-if="!logoFailed" :src="staticAsset('images/logo.png')" alt="" class="h-9 w-9 rounded-full border-2 border-white/80 bg-white object-contain">
          <span class="font-display text-sm font-bold leading-tight text-white">St Mark's College<br><span class="font-normal text-blue-200">Namagoma</span></span>
        </div>
        <button type="button" aria-label="Close menu" class="rounded-full p-1.5 text-white/80 transition hover:bg-white/10 hover:text-white" @click="closeDrawer">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <nav class="flex flex-1 flex-col gap-1 overflow-y-auto p-4">
      <template v-for="item in menu" :key="item.label">
        <div v-if="item.children">
          <button type="button" class="flex w-full items-center justify-between rounded-md px-2 py-2 text-sm font-semibold text-brand-navy" @click="toggleMobileSection(item.label)">
            {{ item.label }}
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition" :class="{ 'rotate-180': openMobileSection === item.label }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
          </button>
          <div v-if="openMobileSection === item.label" class="ml-3 flex flex-col gap-0.5 border-l-2 border-brand-gold/40 pl-3">
            <router-link
              v-for="[href, label] in item.children"
              :key="href"
              :to="href"
              class="block rounded-md px-2 py-1.5 text-sm text-gray-600 hover:bg-black/5"
              @click="closeDrawer"
            >{{ label }}</router-link>
          </div>
        </div>
        <router-link v-else :to="item.to!" class="block rounded-md px-2 py-2 text-sm font-semibold text-brand-navy hover:bg-black/5" @click="closeDrawer">{{ item.label }}</router-link>
      </template>
    </nav>

    <div class="border-t border-gray-100 p-4">
      <router-link to="/apply" class="btn btn-gold block w-full text-center" @click="closeDrawer">Apply Now</router-link>
    </div>
  </aside>
</template>
