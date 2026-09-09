<script setup lang="ts">
import { useHead } from '@unhead/vue'
import { api } from '../services/api'
import PageHeader from '../components/PageHeader.vue'
import EmptyState from '../components/EmptyState.vue'
import AvatarImage from '../components/AvatarImage.vue'
import { staticAsset } from '../utils/staticAsset'

useHead({ title: 'Our Staff' })

const { data } = await api.get('/staff')
const groups = data.data.groups as Record<string, Array<Record<string, any>>>
const categories = Object.keys(groups)

const categoryLabels: Record<string, string> = {
  'Support Staff': 'Non Teaching Staff',
}
function categoryLabel(category: string): string {
  return categoryLabels[category] ?? category
}
</script>

<template>
  <PageHeader title="Our Staff" :bg-image="staticAsset('storage/images/smacon.jpg')" />

  <section class="container-wide py-14">
    <div v-for="category in categories" :key="category" class="mb-14">
      <template v-if="category === 'Administrator' || category === 'Head of Department'">
        <div class="rounded-3xl bg-white p-8 shadow-card ring-1 ring-brand-gold/25 sm:p-10">
          <h2 class="font-display text-2xl font-bold text-brand-navy sm:text-3xl">{{ categoryLabel(category) }}</h2>

          <div class="mt-8 grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            <div
              v-for="member in groups[category]"
              :key="member.id"
              class="card-interactive rounded-2xl bg-gray-50 p-6 text-center ring-1 ring-black/5"
            >
              <AvatarImage :src="member.photo_url" :alt="member.name" size="lg" />
              <div class="mt-4 font-display text-base font-bold text-gray-900">{{ member.name }}</div>
              <div class="mt-1 inline-block rounded-full bg-brand-gold/10 px-3 py-1 text-xs font-bold uppercase tracking-wide text-brand-navy">{{ member.department }}</div>
            </div>
          </div>
        </div>
      </template>

      <template v-else>
        <div class="rounded-3xl bg-gray-50 p-8 sm:p-10">
          <h2 class="font-display text-2xl font-bold text-brand-navy sm:text-3xl">{{ categoryLabel(category) }}</h2>

          <div class="mt-8 grid grid-cols-2 gap-5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-7">
            <div
              v-for="member in groups[category]"
              :key="member.id"
              class="card-interactive rounded-xl bg-white p-4 text-center shadow-card ring-1 ring-black/5"
            >
              <AvatarImage :src="member.photo_url" :alt="member.name" />
              <div class="mt-2 text-sm font-semibold text-gray-900">{{ member.name }}</div>
              <div class="text-xs text-gray-500">{{ member.department }}</div>
            </div>
          </div>
        </div>
      </template>
    </div>

    <EmptyState v-if="!categories.length" message="Staff directory coming soon." />
  </section>
</template>
