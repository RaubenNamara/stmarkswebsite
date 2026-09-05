<script setup lang="ts">
import { useHead } from '@unhead/vue'
import PageHeader from '../../components/PageHeader.vue'

useHead({ title: 'Circulars' })
</script>

<template>
  <PageHeader title="Circulars" subtitle="Official school notices and circulars." />

  <section class="mx-auto max-w-3xl px-6 py-14">
    <div class="card">
      <p class="text-gray-600">School circulars and official notices are published alongside performance reports. Visit the <router-link to="/performance" class="text-brand-navy hover:underline">Performance &amp; Circulars</router-link> page to view and download the latest documents.</p>
    </div>
  </section>
</template>
