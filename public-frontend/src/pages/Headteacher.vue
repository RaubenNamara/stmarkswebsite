<script setup lang="ts">
import { useHead } from '@unhead/vue'
import PageHeader from '../components/PageHeader.vue'
import AvatarImage from '../components/AvatarImage.vue'
import { staticAsset } from '../utils/staticAsset'

useHead({ title: "Head Teacher's Message" })
</script>

<template>
  <PageHeader
    title="Head Teacher's Message"
    subtitle="Academic leadership focused on holistic growth and consistent excellence."
    :bg-image="staticAsset('storage/images/smacon.jpg')"
  />

  <section class="mx-auto grid max-w-7xl gap-6 px-6 py-14 md:grid-cols-[280px_1fr] md:items-start">
    <div class="card border-t-4 border-brand-navy text-center">
      <AvatarImage :src="staticAsset('images/hm.jpg')" alt="Wabwire Joseph" size="lg" />
      <h2 class="text-lg font-bold text-brand-navy">Wabwire Joseph</h2>
      <p class="font-semibold text-gray-600">Head Teacher</p>
      <p class="mt-2 text-sm text-gray-600">Provides strong academic leadership ensuring holistic development and consistent performance.</p>
    </div>

    <div class="card border-t-4 border-brand-gold">
      <p class="text-gray-600">Dear Students, Parents, and Members of the School Community,</p>
      <p class="mt-3 text-gray-600">As Head Teacher of <strong>St. Mark's College Namagoma</strong>, I am proud to lead a vibrant academic community committed to excellence, discipline, and character formation.</p>
      <p class="mt-3 text-gray-600">Our primary focus is to ensure that every learner receives quality education in a structured and supportive environment. We believe that academic success must go hand in hand with moral growth, leadership development, and personal responsibility.</p>
      <p class="mt-3 text-gray-600">Through teamwork among staff, students, and parents, we maintain high academic standards while nurturing talents in co-curricular activities. We strive to produce well-rounded individuals who are prepared for higher education and meaningful contribution to society.</p>
      <p class="mt-3 text-gray-600">I encourage our students to remain disciplined, focused, and determined in their pursuit of excellence. With dedication and commitment, success is assured.</p>
      <p class="mt-3 font-semibold text-gray-800">Let us continue striving for consistent performance and holistic development.</p>
      <div class="mt-6 font-bold text-brand-navy">
        Wabwire Joseph<br>
        <span class="font-normal text-gray-600">Head Teacher, St. Mark's College Namagoma</span>
      </div>
    </div>
  </section>
</template>
