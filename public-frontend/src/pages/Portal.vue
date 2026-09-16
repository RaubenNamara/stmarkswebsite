<script setup lang="ts">
import { useHead } from '@unhead/vue'
import PageHeader from '../components/PageHeader.vue'
import { staticAsset } from '../utils/staticAsset'

useHead({
  title: 'Portal',
  meta: [{ name: 'description', content: "Every digital system built for St Mark's College Namagoma, in one place - eLearning, Admissions, eVoting, Cyber Monitor, eConcerting and Academics." }],
})

interface SystemLink {
  icon: string
  title: string
  tagline: string
  text?: string
  features?: string[]
  url: string
  external: boolean
}

// Alphabetical by title - this page is a directory, not a ranked feature list, so order stays
// predictable rather than mirroring the eCampus dock's hand-tuned reveal order on Home.vue.
const systems: SystemLink[] = [
  {
    icon: '📖',
    title: 'Academics',
    tagline: 'One platform for the whole school',
    features: ['Student records', 'Timetables & scheduling', 'Performance tracking'],
    url: 'http://stmark.sc.ug/accademics',
    external: true,
  },
  { icon: '🎓', title: 'Admissions', tagline: 'Apply in minutes', text: 'Submit and track a school application entirely online.', url: 'https://stmark.sc.ug/stmarks_admission/', external: true },
  { icon: '🛡️', title: 'Cyber Monitor', tagline: 'A safer digital campus', text: "Keeping our students safe online through the school's digital monitoring programme.", url: 'https://stmark.sc.ug/laptop_tracking_system/auth/login.php', external: true },
  { icon: '🎶', title: 'eConcerting', tagline: 'Every performance, live', text: 'Livestreamed and recorded school concerts, performances and events.', url: 'https://stmark.sc.ug/concerting/', external: true },
  { icon: '📚', title: 'eLearning', tagline: 'Learning without limits', text: 'Class notes, assignments and virtual lessons through the eSpace learning portal.', url: 'https://stmark.sc.ug/elearning/', external: true },
  { icon: '🗳️', title: 'eVoting System', tagline: 'Every voice counted', text: 'Secure digital voting for student leadership and school elections.', url: 'https://stmark.sc.ug/votesystem/index.php', external: true },
]
</script>

<template>
  <PageHeader
    title="School Systems Portal"
    subtitle="One place to reach every digital system built for St Mark's College Namagoma."
    :bg-image="staticAsset('storage/images/smacon.jpg')"
  />

  <section class="container-wide py-14">
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <component
        :is="system.external ? 'a' : 'router-link'"
        v-for="(system, i) in systems"
        :key="system.title"
        v-bind="system.external ? { href: system.url, target: '_blank', rel: 'noopener' } : { to: system.url }"
        v-reveal="i * 110"
        class="card card-interactive card-bounce group flex flex-col overflow-hidden border-2 border-gray-200 bg-gray-200 transition-all duration-300 hover:scale-[1.02] hover:border-transparent hover:bg-brand-navy active:scale-[0.98]"
      >
        <!-- Shine sweep: a soft diagonal highlight that slides across the card on hover. -->
        <div class="pointer-events-none absolute inset-0 -translate-x-full skew-x-12 bg-gradient-to-r from-transparent via-white/20 to-transparent transition-transform duration-700 ease-out group-hover:translate-x-full" />

        <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-brand-navy via-brand-gold to-brand-navy opacity-60 transition duration-300 group-hover:opacity-100" />
        <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-brand-gold/0 blur-2xl transition duration-500 group-hover:bg-brand-gold/20" />
        <span class="relative flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand-navy/5 text-2xl transition duration-300 group-hover:-rotate-6 group-hover:scale-110 group-hover:bg-white/10">{{ system.icon }}</span>

        <h3 class="relative mt-4 font-display text-lg font-bold text-brand-navy transition-colors duration-300 group-hover:text-white">{{ system.title }}</h3>
        <p class="relative mt-0.5 text-xs font-semibold uppercase tracking-wide text-brand-gold">{{ system.tagline }}</p>
        <p v-if="system.text" class="relative mt-2 text-sm text-gray-600 transition-colors duration-300 group-hover:text-blue-100">{{ system.text }}</p>

        <ul v-if="system.features" class="relative mt-3 space-y-1.5">
          <li v-for="feature in system.features" :key="feature" class="flex items-start gap-2 text-sm text-gray-600 transition-colors duration-300 group-hover:text-blue-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-brand-navy/60 transition-colors duration-300 group-hover:text-brand-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            {{ feature }}
          </li>
        </ul>

        <span class="relative mt-4 flex flex-1 items-end">
          <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-navy transition-colors duration-300 group-hover:text-white">
            Open System
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
          </span>
        </span>
      </component>
    </div>
  </section>
</template>

<style scoped>
/* Bounce-in entrance: overrides the site-wide .reveal ease-out with a "back" curve that
   overshoots past its final position before settling, so cards feel like they hop into place
   one after another (via the v-reveal stagger delay) instead of just fading up. */
.card-bounce.reveal {
  transform: translateY(36px) scale(0.92);
  transition-timing-function: cubic-bezier(0.34, 1.56, 0.64, 1);
  transition-duration: 0.65s;
}
.card-bounce.reveal.revealed {
  transform: none;
}
.card-bounce {
  position: relative;
}
</style>
