<script setup lang="ts">
import { useHead } from '@unhead/vue'
import PageHeader from '../components/PageHeader.vue'

useHead({ title: "Director's Message" })
</script>

<template>
  <PageHeader title="Director's Message" />

  <section class="mx-auto grid max-w-6xl gap-6 px-6 py-14 md:grid-cols-[280px_1fr] md:items-start">
    <div class="card border-t-4 border-brand-navy text-center">
      <img :src="'/images/director1.jpg'" alt="Owek Ddamulira Daniel" class="mx-auto mb-4 h-40 w-40 rounded-full border-4 border-brand-navy object-cover">
      <h2 class="text-lg font-bold text-brand-navy">Owek Ddamulira Daniel</h2>
      <p class="font-semibold text-gray-600">Director</p>
      <p class="mt-2 text-sm text-gray-600">A visionary education leader committed to institutional growth, integrity and academic excellence.</p>
    </div>

    <div class="card border-t-4 border-brand-gold">
      <p class="text-gray-600">Dear Students, Parents, Staff, and Esteemed Visitors,</p>
      <p class="mt-3 text-gray-600">It is my great pleasure to welcome you to <strong>St. Mark's College Namagoma — The Higher Achiever's College.</strong></p>
      <p class="mt-3 text-gray-600">At St. Mark's College, we believe that education is the foundation of transformation. Our mission is to nurture disciplined, confident, and competent learners who are prepared not only for examinations, but for life.</p>
      <p class="mt-3 text-gray-600">We are committed to fostering academic excellence, moral integrity, leadership, and the development of individual talents. Through a dedicated team of qualified staff, a structured learning environment, and a strong value system rooted in faith and discipline, we strive to empower every learner to achieve their highest potential.</p>
      <p class="mt-3 text-gray-600">Our focus remains on producing responsible citizens who will contribute positively to their communities and to the development of our motherland.</p>
      <p class="mt-3 font-semibold text-gray-800">Together, let us continue striving for excellence.</p>
      <div class="mt-6 font-bold text-brand-navy">
        Owek Ddamulira Daniel<br>
        <span class="font-normal text-gray-600">Director, St. Mark's College Namagoma</span>
      </div>
    </div>
  </section>
</template>
