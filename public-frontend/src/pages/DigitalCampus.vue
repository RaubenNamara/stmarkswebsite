<script setup lang="ts">
import { useHead } from '@unhead/vue'
import PageHeader from '../components/PageHeader.vue'
import { staticAsset } from '../utils/staticAsset'

const props = defineProps<{ title: string; description: string }>()

useHead({ title: props.title })
</script>

<template>
  <PageHeader :title="title" :subtitle="description" :bg-image="staticAsset('storage/images/smacon.jpg')" />

  <section class="mx-auto max-w-3xl px-6 py-14">
    <div class="card">
      <p class="text-gray-600">This is part of St. Mark's College Namagoma's Digital Campus initiative, bringing technology into everyday school life.</p>
      <p class="mt-3 text-gray-600">For more information, please <router-link to="/contact" class="text-brand-navy hover:underline">contact the school office</router-link>.</p>
    </div>
  </section>
</template>
