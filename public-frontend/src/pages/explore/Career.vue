<script setup lang="ts">
import { useHead } from '@unhead/vue'
import PageHeader from '../../components/PageHeader.vue'
import { staticAsset } from '../../utils/staticAsset'

useHead({ title: 'Career Guidance' })

const perks = [
  {
    title: 'Professional Growth',
    text: 'Workshops, mentorship and clear career progression opportunities for every member of staff.',
    icon: 'growth',
  },
  {
    title: 'Inclusive Culture',
    text: 'A respectful, collaborative environment where every voice and background is valued.',
    icon: 'people',
  },
  {
    title: 'Community Impact',
    text: 'Help shape young minds, build character and create lasting change beyond the classroom.',
    icon: 'heart',
  },
  {
    title: 'Wellbeing First',
    text: 'A supportive workplace that prioritizes the health and balance of every team member.',
    icon: 'shield',
  },
]

const steps = [
  { title: 'Prepare your documents', text: 'Have your CV (PDF, max 5MB) and contact details ready.' },
  { title: 'Fill the application form', text: 'Tell us about yourself and the position you\'re applying for.' },
  { title: 'Submit online', text: 'Send your application in a few minutes — no paperwork required.' },
  { title: 'We review & respond', text: 'Our team reviews every application and reaches out if there\'s a match.' },
]
</script>

<template>
  <PageHeader
    title="Join our team — Shape the future with St Mark's College"
    subtitle="Are you a dedicated professional who loves education and community development? We invite passionate teachers and support staff to apply and become part of a team that nurtures excellence."
    :bg-image="staticAsset('storage/images/smacon.jpg')"
  />

  <!-- Sticky-feel CTA strip -->
  <section class="border-b border-black/5 bg-brand-cream/60">
    <div class="container-wide flex flex-col items-center justify-between gap-4 py-6 sm:flex-row">
      <p class="text-center text-sm font-semibold text-brand-navy sm:text-left">Ready to bring your talent to SMACON? It only takes a few minutes to apply.</p>
      <router-link to="/apply" class="btn btn-gold shrink-0">Continue to application form &rarr;</router-link>
    </div>
  </section>

  <!-- Why work with us -->
  <section class="container-wide py-14">
    <div v-reveal class="mx-auto max-w-2xl text-center">
      <span class="eyebrow">Why Work With Us</span>
      <h2 class="mt-2 font-display text-2xl font-bold text-brand-navy sm:text-3xl">A place to grow, teach and belong</h2>
      <p class="mt-3 text-gray-600">At St Mark's College Namagoma we believe in empowering staff to deliver high-quality education, backed by continuous learning, wellbeing and collaboration.</p>
    </div>

    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
      <div v-for="perk in perks" :key="perk.title" v-reveal class="card card-interactive text-center">
        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand-gold/10 text-brand-navy">
          <svg v-if="perk.icon === 'growth'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 8-8M21 7v6M21 7h-6" /></svg>
          <svg v-else-if="perk.icon === 'people'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m5-2.13a4 4 0 100-8 4 4 0 000 8zm6 1a4 4 0 100-8" /></svg>
          <svg v-else-if="perk.icon === 'heart'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M20.8 4.6a5.5 5.5 0 00-7.8 0L12 5.6l-1-1a5.5 5.5 0 00-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 000-7.8z" /></svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v6c0 4.5-3 8-7 9-4-1-7-4.5-7-9V6l7-3z" /></svg>
        </span>
        <h3 class="mt-4 font-display text-base font-bold text-gray-900">{{ perk.title }}</h3>
        <p class="mt-2 text-sm text-gray-600">{{ perk.text }}</p>
      </div>
    </div>
  </section>

  <!-- How to apply -->
  <section class="bg-gray-50 py-14">
    <div class="container-wide">
      <div v-reveal class="mx-auto max-w-2xl text-center">
        <span class="eyebrow">The Process</span>
        <h2 class="mt-2 font-display text-2xl font-bold text-brand-navy sm:text-3xl">How to apply</h2>
      </div>

      <div class="relative mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
        <div v-for="(step, i) in steps" :key="step.title" v-reveal class="relative">
          <div class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-navy font-display text-lg font-bold text-white">{{ i + 1 }}</div>
          <h3 class="mt-4 font-display text-base font-bold text-gray-900">{{ step.title }}</h3>
          <p class="mt-2 text-sm text-gray-600">{{ step.text }}</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Closing CTA -->
  <section class="relative overflow-hidden border-t border-black/5 bg-white px-6 py-14 text-center">
    <div class="pointer-events-none absolute -right-24 -top-24 h-64 w-64 rounded-full bg-brand-gold/10 blur-3xl" />
    <div class="pointer-events-none absolute -bottom-24 -left-24 h-64 w-64 rounded-full bg-brand-navy/5 blur-3xl" />
    <div v-reveal class="relative mx-auto max-w-2xl">
      <h2 class="font-display text-2xl font-bold text-brand-navy sm:text-3xl">Ready to make an impact?</h2>
      <p class="mx-auto mt-3 max-w-lg text-gray-600">Submit your application today and take the first step towards joining the SMACON family.</p>
      <router-link to="/apply" class="btn btn-gold mt-7">Continue to application form &rarr;</router-link>
    </div>
  </section>
</template>
