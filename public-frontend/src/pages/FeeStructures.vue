<script setup lang="ts">
import { useHead } from '@unhead/vue'
import { api } from '../services/api'
import PageHeader from '../components/PageHeader.vue'
import EmptyState from '../components/EmptyState.vue'
import { staticAsset } from '../utils/staticAsset'

useHead({ title: 'Fee Structures, Personal Needs, School Rules & Calendar' })

const apiPublicBase = import.meta.env.BASE_URL.replace(/\/?$/, '') + '/api/public'

const { data } = await api.get('/fee-structures')
const items = data.data.items as Array<Record<string, any>>

function pdfUrl(item: Record<string, any>) {
  return item.file_path?.startsWith('/uploads/') ? `${apiPublicBase}/fee-structures/${item.id}/pdf` : item.file_url
}
</script>

<template>
  <PageHeader
    title="Fee Structures, Personal Needs, School Rules & Calendar"
    subtitle="Browse and view every published fee structure, personal needs list, school rule book and term calendar — opens right in your browser, no download required."
    :bg-image="staticAsset('storage/images/smacon.jpg')"
  />

  <section class="relative overflow-hidden bg-white py-14">
    <div class="pointer-events-none absolute -left-10 top-0 h-96 w-96 rounded-full bg-brand-gold/40 blur-3xl" />
    <div class="pointer-events-none absolute right-0 top-1/3 h-96 w-96 rounded-full bg-brand-navy/30 blur-3xl" />
    <div class="pointer-events-none absolute bottom-0 left-1/3 h-96 w-96 rounded-full bg-sky-400/25 blur-3xl" />

    <div class="container-wide relative">
      <EmptyState v-if="!items.length" message="No documents available yet." />

      <div v-else class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <a
          v-for="item in items"
          :key="item.id"
          :href="pdfUrl(item)"
          target="_blank"
          rel="noopener"
          class="group flex items-start gap-4 rounded-2xl border border-white/60 bg-white/30 p-6 shadow-xl backdrop-blur-xl transition duration-300 hover:-translate-y-1 hover:bg-white/50 sm:p-8"
        >
          <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/50 text-red-500 ring-1 ring-white/60">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M9 8h1M6 3h9l5 5v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5a2 2 0 012-2z" /></svg>
          </span>
          <span class="min-w-0 flex-1">
            <span class="block break-words font-display text-sm font-bold text-gray-900">{{ item.title }}</span>
            <span class="mt-1.5 inline-flex items-center gap-1 text-sm font-semibold text-brand-navy">
              View PDF
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 opacity-0 transition group-hover:translate-x-0.5 group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
            </span>
          </span>
        </a>
      </div>
    </div>
  </section>
</template>
