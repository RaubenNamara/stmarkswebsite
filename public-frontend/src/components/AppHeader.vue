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
    ],
  },
  {
    label: 'About',
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
      ['https://stmark.sc.ug/elearning/', 'eLearning'],
      ['https://stmark.sc.ug/votesystem/index.php', 'eVoting System'],
      ['https://stmark.sc.ug/laptop_tracking_system/auth/login.php', 'Cyber Monitor'],
      ['https://stmark.sc.ug/concerting/', 'eConcerting'],
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
  { label: 'eVoices', to: '/campus-voices' },
  { label: 'Contact', to: '/contact' },
]

import { ref } from 'vue'
const isOpen = ref(false)
const openMobileSection = ref<string | null>(null)
const logoFailed = ref(false)

function toggleMobileSection(label: string) {
  openMobileSection.value = openMobileSection.value === label ? null : label
}

function closeMenu() {
  isOpen.value = false
}

// A few eCampus links point at real external systems (separate apps under stmark.sc.ug, not
// routes in this SPA) rather than the internal pages every other menu item uses.
function isExternal(href: string): boolean {
  return href.startsWith('http://') || href.startsWith('https://')
}
</script>

<template>
  <TopBar />

  <header class="sticky top-0 z-50 border-b border-black/5 bg-brand-cream shadow-sm">
    <nav class="container-chrome flex items-center justify-between gap-4 py-3">
      <router-link to="/" class="flex min-w-0 shrink items-center gap-2 sm:gap-3 xl:gap-3 2xl:gap-4">
        <img v-if="!logoFailed" :src="staticAsset('images/logo.png')" alt="St Mark's College Namagoma crest" class="h-10 w-10 shrink-0 rounded-full border-2 border-brand-navy bg-white object-contain shadow-sm sm:h-14 sm:w-14 xl:h-14 xl:w-14 2xl:h-16 2xl:w-16" @error="logoFailed = true">
        <span v-else class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 border-brand-navy bg-white font-display text-sm font-extrabold text-brand-navy shadow-sm sm:h-14 sm:w-14 sm:text-lg xl:h-14 xl:w-14 2xl:h-16 2xl:w-16 2xl:text-xl">SM</span>
        <span class="min-w-0 leading-tight">
          <span class="block truncate font-display text-sm font-extrabold text-brand-navy sm:text-base 2xl:text-lg">St Mark's College Namagoma</span>
          <span class="hidden truncate text-xs italic text-brand-navy/70 sm:block 2xl:text-sm">The High Achiever's College</span>
        </span>
      </router-link>

      <ul class="hidden items-center gap-0.5 xl:flex 2xl:gap-2">
        <li v-for="item in menu" :key="item.label" class="group relative pb-2">
          <router-link
            v-if="item.to"
            :to="item.to"
            class="flex items-center gap-1 rounded-md px-2.5 py-2 text-sm font-semibold text-brand-navy transition group-hover:bg-brand-gold/20 2xl:px-4 2xl:py-2.5 2xl:text-base"
          >
            {{ item.label }}
            <svg v-if="item.children" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition group-hover:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
          </router-link>
          <button
            v-else
            type="button"
            class="flex items-center gap-1 rounded-md px-2.5 py-2 text-sm font-semibold text-brand-navy transition group-hover:bg-brand-gold/20 2xl:px-4 2xl:py-2.5 2xl:text-base"
          >
            {{ item.label }}
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition group-hover:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
          </button>

          <div
            v-if="item.children"
            class="invisible absolute left-0 top-full z-10 w-64 -translate-y-1 overflow-hidden rounded-2xl bg-brand-cream opacity-0 shadow-xl ring-1 ring-black/5 transition duration-200 ease-out group-hover:visible group-hover:translate-y-0 group-hover:opacity-100"
          >
            <div class="h-1 bg-gradient-to-r from-brand-gold to-brand-gold/40" />
            <ul class="p-2">
              <li v-for="[href, label] in item.children" :key="href">
                <component
                  :is="isExternal(href) ? 'a' : 'router-link'"
                  v-bind="isExternal(href) ? { href, target: '_blank', rel: 'noopener' } : { to: href }"
                  class="group/item flex items-center justify-between gap-2 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-700 transition-colors duration-150 hover:bg-brand-navy/5 hover:text-brand-navy"
                >
                  {{ label }}
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 text-brand-navy/40 opacity-0 transition group-hover/item:translate-x-0.5 group-hover/item:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                </component>
              </li>
            </ul>
          </div>
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

    <!-- Full-screen popup menu: fixed over the whole viewport with a solid (non-transparent)
         background, so no page content shows behind it at all - only the header itself (higher
         z-index) stays visible on top, since it's already opaque anyway. pt-16 clears the header's
         own height so the menu's contents start right below it instead of hiding underneath. -->
    <div v-if="isOpen" class="fixed inset-0 z-40 overflow-y-auto border-t border-white/10 bg-brand-navy-dark pt-16 shadow-2xl xl:hidden">
      <div class="flex items-center justify-end px-4 py-2.5">
        <button type="button" aria-label="Close menu" class="rounded-md p-2 text-white/80 transition hover:bg-white/10 hover:text-white" @click="closeMenu">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <nav class="flex flex-col divide-y divide-white/10 border-t border-white/10">
        <template v-for="item in menu" :key="item.label">
          <div v-if="item.children">
            <button type="button" class="flex w-full items-center justify-between px-5 py-4 text-left text-base font-semibold text-white" @click="toggleMobileSection(item.label)">
              {{ item.label }}
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 transition" :class="{ 'rotate-90': openMobileSection === item.label }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
            </button>
            <div v-if="openMobileSection === item.label" class="flex flex-col bg-black/10 pb-2">
              <component
                :is="isExternal(href) ? 'a' : 'router-link'"
                v-for="[href, label] in item.children"
                :key="href"
                v-bind="isExternal(href) ? { href, target: '_blank', rel: 'noopener' } : { to: href }"
                class="px-8 py-2.5 text-sm text-blue-100 hover:text-white"
                @click="closeMenu"
              >{{ label }}</component>
            </div>
          </div>
          <router-link v-else :to="item.to!" class="block px-5 py-4 text-base font-semibold text-white" @click="closeMenu">{{ item.label }}</router-link>
        </template>
      </nav>

      <div class="p-4">
        <a href="https://stmark.sc.ug/stmarks_admission/" target="_blank" rel="noopener" class="btn btn-gold block w-full text-center" @click="closeMenu">Apply Now</a>
      </div>
    </div>
  </header>
</template>
