<script setup lang="ts">
import { useHead } from '@unhead/vue'
import PageHeader from '../../components/PageHeader.vue'
import { staticAsset } from '../../utils/staticAsset'

useHead({ title: 'Curriculum' })

const oLevelGroups = [
  { title: 'Sciences', icon: 'flask', items: ['Mathematics', 'Chemistry', 'Biology', 'Physics'] },
  { title: 'Vocational Subjects', icon: 'tool', items: ['Entrepreneurship', 'ICT', 'Art and Design', 'Technical Drawing', 'Physical Education'] },
  { title: 'Languages', icon: 'chat', items: ['Kiswahili', 'Luganda', 'Literature', 'English'] },
  { title: 'Humanities', icon: 'globe', items: ['Religious Education', 'Geography', 'History', 'Islamic Religious Education (IRE)'] },
]

const aLevelSubjects = [
  'Physics', 'Chemistry', 'Biology', 'Mathematics', 'Geography', 'History', 'CRE / Divinity',
  'Technical Drawing', 'Kiswahili', 'Luganda', 'Entrepreneurship', 'Literature', 'Fine Art', 'Economics',
]
</script>

<template>
  <PageHeader
    title="Our Curriculum"
    subtitle="Delivered under the Competency-Based Curriculum, designed to develop knowledge, practical skills, values, creativity and critical thinking for academic excellence and lifelong success."
    :bg-image="staticAsset('storage/images/smacon.jpg')"
  />

  <!-- O-Level -->
  <section class="container-wide py-14">
    <div v-reveal class="mx-auto max-w-2xl text-center">
      <span class="eyebrow">Ordinary Level</span>
      <h2 class="mt-2 font-display text-2xl font-bold text-brand-navy sm:text-3xl">O-Level Subjects</h2>
      <p class="mt-3 text-gray-600">A broad, balanced foundation across sciences, languages, humanities and vocational subjects.</p>
    </div>

    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
      <div v-for="group in oLevelGroups" :key="group.title" v-reveal class="card card-interactive">
        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-gold/10 text-brand-navy">
          <svg v-if="group.icon === 'flask'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3h6M10 3v5.5L4.8 17a1.5 1.5 0 001.3 2.3h11.8a1.5 1.5 0 001.3-2.3L14 8.5V3" /></svg>
          <svg v-else-if="group.icon === 'tool'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M14.7 6.3a4 4 0 00-5.4 5.4L3 18v3h3l6.3-6.3a4 4 0 005.4-5.4l-2.5 2.5-2-2 2.5-2.5z" /></svg>
          <svg v-else-if="group.icon === 'chat'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.4-4 8-9 8a9.7 9.7 0 01-4-.8L3 20l1-4a7.7 7.7 0 01-1-4c0-4.4 4-8 9-8s9 3.6 9 8z" /></svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18zM3.6 9h16.8M3.6 15h16.8M12 3a13.7 13.7 0 010 18M12 3a13.7 13.7 0 000 18" /></svg>
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

  <!-- A-Level -->
  <section class="bg-gray-50 py-14">
    <div class="container-wide">
      <div v-reveal class="mx-auto max-w-2xl text-center">
        <span class="eyebrow">Advanced Level</span>
        <h2 class="mt-2 font-display text-2xl font-bold text-brand-navy sm:text-3xl">A-Level Subject Combinations</h2>
        <p class="mt-3 text-gray-600">Learners choose from a wide range of subjects to build their preferred combination.</p>
      </div>

      <div v-reveal class="mt-10 rounded-3xl bg-white p-8 shadow-card ring-1 ring-black/5 sm:p-10">
        <div class="flex flex-wrap justify-center gap-2.5">
          <span v-for="subject in aLevelSubjects" :key="subject" class="rounded-full bg-brand-navy/5 px-4 py-2 text-sm font-semibold text-brand-navy ring-1 ring-brand-navy/10">
            {{ subject }}
          </span>
        </div>
      </div>
    </div>
  </section>
</template>
