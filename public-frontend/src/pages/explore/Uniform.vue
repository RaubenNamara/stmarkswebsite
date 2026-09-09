<script setup lang="ts">
import { useHead } from '@unhead/vue'
import PageHeader from '../../components/PageHeader.vue'
import { staticAsset } from '../../utils/staticAsset'

useHead({ title: 'Uniform' })

const academicUniform = [
  { title: 'O-Level Students', items: ['Cream Shirts / Blouses', 'Navy Blue Trousers / Skirts'] },
  { title: 'A-Level Students', items: ['Blue Shirts / Blouses', 'Navy Blue Trousers / Skirts'] },
  { title: 'School Sweater', items: ['Navy Blue Sweater', 'Light Blue Stripes at the Neck and Sleeves'] },
]

const outOfClass = [
  'Plain Red T-Shirts (different colors per class)',
  'Black Skirts / Trousers',
  'Skirts/trousers only',
]

const uniformPackage = [
  'School Sweater',
  '2 Shirts / Blouses',
  '2 Trousers / Skirts',
  'Sunday Wear',
  'Sports Wear',
  '3 T-Shirts for Out-of-Class Wear',
]
</script>

<template>
  <PageHeader title="School Uniform" subtitle="Our uniform reflects discipline, identity, and excellence." :bg-image="staticAsset('storage/images/smacon.jpg')" />

  <!-- Academic Uniform -->
  <section class="container-wide py-14">
    <div v-reveal class="mx-auto max-w-2xl text-center">
      <span class="eyebrow">What to Wear</span>
      <h2 class="mt-2 font-display text-2xl font-bold text-brand-navy sm:text-3xl">Academic Uniform</h2>
      <p class="mt-3 text-gray-600">Every learner is expected to be in full, correct uniform at all times during the school day.</p>
    </div>

    <div class="mt-10 grid gap-6 sm:grid-cols-3">
      <div v-for="group in academicUniform" :key="group.title" v-reveal class="card card-interactive">
        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-gold/10 text-brand-navy">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M8 3l4 2 4-2 3 4-3 2v11a1 1 0 01-1 1H9a1 1 0 01-1-1V9L5 7l3-4z" /></svg>
        </span>
        <h3 class="mt-4 font-display text-base font-bold text-gray-900">{{ group.title }}</h3>
        <ul class="mt-3 space-y-1.5">
          <li v-for="item in group.items" :key="item" class="flex items-start gap-2 text-sm text-gray-600">
            <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-gold" />
            {{ item }}
          </li>
        </ul>
      </div>
    </div>
  </section>

  <!-- Out-of-class + notice -->
  <section class="bg-gray-50 py-14">
    <div class="container-wide grid gap-6 lg:grid-cols-2">
      <div v-reveal class="card card-interactive">
        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-gold/10 text-brand-navy">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4h16v4H4zM6 8v12h12V8" /></svg>
        </span>
        <h2 class="mt-4 font-display text-lg font-bold text-gray-900">Out-of-Class Uniform</h2>
        <ul class="mt-3 space-y-1.5">
          <li v-for="item in outOfClass" :key="item" class="flex items-start gap-2 text-sm text-gray-600">
            <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-gold" />
            {{ item }}
          </li>
        </ul>
      </div>

      <div v-reveal class="flex flex-col justify-center rounded-2xl bg-red-50 p-6 ring-1 ring-red-600/15 sm:p-8">
        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a1 1 0 00.87 1.5h18.62a1 1 0 00.87-1.5L13.71 3.86a1 1 0 00-1.72 0z" /></svg>
        </span>
        <h2 class="mt-4 font-display text-lg font-bold text-red-800">Important Notice</h2>
        <p class="mt-2 text-sm leading-relaxed text-red-700">No other form of dressing is allowed in school apart from that indicated on this page.</p>
      </div>
    </div>
  </section>

  <!-- Uniform Package -->
  <section class="container-wide py-14">
    <div v-reveal class="mx-auto max-w-2xl text-center">
      <span class="eyebrow">Provided at School</span>
      <h2 class="mt-2 font-display text-2xl font-bold text-brand-navy sm:text-3xl">Uniform Package</h2>
      <p class="mt-3 text-gray-600">Every learner's uniform package is available for purchase directly at school.</p>
    </div>

    <div v-reveal class="mt-10 rounded-3xl bg-white p-8 shadow-card ring-1 ring-black/5 sm:p-10">
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="item in uniformPackage" :key="item" class="flex items-center gap-3 rounded-xl bg-gray-50 px-4 py-3.5 ring-1 ring-black/5">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-brand-navy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
          <span class="text-sm font-medium text-gray-800">{{ item }}</span>
        </div>
      </div>
    </div>
  </section>
</template>
