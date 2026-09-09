<script setup lang="ts">
import { useHead } from '@unhead/vue'
import PageHeader from '../../components/PageHeader.vue'
import { staticAsset } from '../../utils/staticAsset'

useHead({ title: 'UNEB Results' })
</script>

<template>
  <PageHeader
    title="UNEB Results"
    subtitle="St. Mark's College Namagoma is proud of its consistent academic performance in national examinations."
    :bg-image="staticAsset('storage/images/smacon.jpg')"
  />

  <section class="mx-auto max-w-3xl px-6 py-14">
    <div class="card">
      <p class="text-gray-600">Our students consistently achieve strong results in the Uganda National Examinations Board (UNEB) assessments, a reflection of our commitment to academic excellence and dedicated teaching staff.</p>
      <p class="mt-3 text-gray-600">For official results releases and detailed performance breakdowns, please contact the school administration directly, or see our <router-link to="/academics/high-achievers" class="text-brand-navy hover:underline">High Achievers</router-link> page for individual student recognitions.</p>
    </div>
  </section>
</template>
