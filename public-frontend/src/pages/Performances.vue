<script setup lang="ts">
import { useHead } from '@unhead/vue'
import { api } from '../services/api'
import PageHeader from '../components/PageHeader.vue'
import EmptyState from '../components/EmptyState.vue'

useHead({ title: 'Performance & Circulars' })

const apiPublicBase = import.meta.env.BASE_URL.replace(/\/?$/, '') + '/api/public'

const { data } = await api.get('/performances')
const items = data.data.items as Array<Record<string, any>>
</script>

<template>
  <PageHeader title="Performance & Circulars" />

  <section class="mx-auto max-w-3xl px-6 py-14">
    <EmptyState v-if="!items.length" message="No documents available yet." />
    <div v-else class="card divide-y divide-gray-100">
      <div v-for="item in items" :key="item.id" class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0">
        <span class="text-gray-800">📄 {{ item.title }}</span>
        <a
          :href="item.pdf?.startsWith('/uploads/') ? `${apiPublicBase}/performances/${item.id}/pdf` : item.file_url"
          target="_blank"
          class="shrink-0 font-semibold text-brand-navy hover:underline"
        >View PDF</a>
      </div>
    </div>
  </section>
</template>
