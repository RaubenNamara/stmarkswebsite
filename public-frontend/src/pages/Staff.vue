<script setup lang="ts">
import { useHead } from '@unhead/vue'
import { api } from '../services/api'
import PageHeader from '../components/PageHeader.vue'
import EmptyState from '../components/EmptyState.vue'
import AvatarImage from '../components/AvatarImage.vue'

useHead({ title: 'Our Staff' })

const { data } = await api.get('/staff')
const groups = data.data.groups as Record<string, Array<Record<string, any>>>
const categories = Object.keys(groups)
</script>

<template>
  <PageHeader title="Our Staff" />

  <section class="mx-auto max-w-7xl px-6 py-14 2xl:max-w-[1600px]">
    <div v-for="category in categories" :key="category" class="mb-12">
      <h2 class="border-b-2 border-brand-gold pb-2 text-xl font-bold text-brand-navy">{{ category }}</h2>
      <div class="mt-6 grid grid-cols-2 gap-5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8">
        <div v-for="member in groups[category]" :key="member.id" class="text-center">
          <AvatarImage :src="member.photo_url" :alt="member.name" />
          <div class="text-sm font-semibold text-gray-900">{{ member.name }}</div>
          <div class="text-xs text-gray-500">{{ member.department }}</div>
        </div>
      </div>
    </div>

    <EmptyState v-if="!categories.length" message="Staff directory coming soon." />
  </section>
</template>
