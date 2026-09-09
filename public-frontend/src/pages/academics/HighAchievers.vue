<script setup lang="ts">
import { reactive } from 'vue'
import { useHead } from '@unhead/vue'
import { api } from '../../services/api'
import PageHeader from '../../components/PageHeader.vue'
import EmptyState from '../../components/EmptyState.vue'
import { staticAsset } from '../../utils/staticAsset'

useHead({ title: 'High Achievers' })

const { data } = await api.get('/high-achievers')
const achievers = data.data.high_achievers as Array<Record<string, any>>

const photoFailed = reactive<Record<number, boolean>>({})
</script>

<template>
  <PageHeader title="High Achievers" subtitle="Celebrating the students whose results set the standard for excellence at St Mark's." :bg-image="staticAsset('storage/images/smacon.jpg')" />

  <section class="container-wide py-14">
    <EmptyState v-if="!achievers.length" message="Nothing here yet." />

    <div v-else class="grid gap-x-6 gap-y-14 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <article
        v-for="member in achievers"
        :key="member.id"
        class="card-interactive group relative rounded-2xl bg-white pt-14 text-center shadow-card ring-1 ring-black/5 hover:ring-brand-gold/40"
      >
        <span v-if="member.year" class="absolute right-4 top-4 rounded-full bg-brand-navy/5 px-2.5 py-1 text-xs font-bold text-brand-navy transition group-hover:bg-brand-gold/15">{{ member.year }}</span>

        <div class="absolute -top-12 left-1/2 -translate-x-1/2 transition-transform duration-300 ease-out group-hover:-translate-y-1 group-hover:scale-110">
          <img
            v-if="member.photo_url && !photoFailed[member.id]"
            :src="member.photo_url"
            :alt="member.name"
            class="h-24 w-24 rounded-full border-4 border-white object-cover shadow-lg ring-2 ring-brand-gold transition-shadow duration-300 group-hover:shadow-xl group-hover:ring-4"
            @error="photoFailed[member.id] = true"
          >
          <div
            v-else
            class="flex h-24 w-24 items-center justify-center rounded-full border-4 border-white bg-brand-navy/10 text-2xl font-bold text-brand-navy/40 shadow-lg ring-2 ring-brand-gold transition-shadow duration-300 group-hover:shadow-xl group-hover:ring-4"
          >
            {{ member.name?.trim().charAt(0).toUpperCase() }}
          </div>
        </div>

        <div class="px-5 pb-7">
          <h3 class="font-display text-base font-bold leading-snug text-gray-900 transition-colors group-hover:text-brand-navy">{{ member.name }}</h3>
          <span v-if="member.exam" class="mt-1 block text-xs font-bold uppercase tracking-widest text-brand-navy/40">{{ member.exam }}</span>

          <div v-if="member.division" class="mx-auto mt-4 w-fit rounded-full bg-gradient-to-r from-brand-gold/15 to-brand-gold/5 px-4 py-1.5 text-sm font-bold text-brand-navy ring-1 ring-brand-gold/30 transition group-hover:ring-brand-gold/60">
            {{ member.division }}
          </div>

          <p v-if="member.description" class="mt-4 text-sm leading-relaxed text-gray-600">{{ member.description }}</p>
        </div>
      </article>
    </div>
  </section>
</template>
