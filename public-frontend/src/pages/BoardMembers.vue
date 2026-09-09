<script setup lang="ts">
import { useHead } from '@unhead/vue'
import { api } from '../services/api'
import PageHeader from '../components/PageHeader.vue'
import EmptyState from '../components/EmptyState.vue'
import AvatarImage from '../components/AvatarImage.vue'
import { staticAsset } from '../utils/staticAsset'

useHead({ title: 'Board Members' })

const { data } = await api.get('/board-members')
const members = data.data.board_members as Array<{ id: number; name: string; position: string; photo_url: string | null }>
</script>

<template>
  <PageHeader
    title="Board Members"
    subtitle="Meet the men and women who provide governance, guidance and oversight for St Mark's College Namagoma."
    :bg-image="staticAsset('storage/images/smacon.jpg')"
  />

  <section class="container-wide py-14">
    <EmptyState v-if="!members.length" message="Board member details coming soon." />

    <template v-else>
      <div v-reveal class="mx-auto max-w-2xl text-center">
        <span class="eyebrow">Governance</span>
        <h2 class="mt-2 font-display text-2xl font-bold text-brand-navy sm:text-3xl">Our Board of Governors</h2>
        <p class="mt-3 text-gray-600">Providing strategic direction and oversight in service of the school's mission and values.</p>
      </div>

      <div class="mt-12 rounded-3xl bg-gradient-to-br from-brand-navy to-brand-navy-dark p-8 sm:p-10">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="member in members"
            :key="member.id"
            v-reveal
            class="card-interactive rounded-2xl bg-white p-6 pt-8 text-center shadow-card ring-1 ring-black/5"
          >
            <AvatarImage :src="member.photo_url" :alt="member.name" size="lg" />
            <h3 class="mt-4 font-display text-base font-bold text-gray-900">{{ member.name }}</h3>
            <span class="mt-2 inline-block rounded-full bg-brand-gold/10 px-3 py-1 text-xs font-bold uppercase tracking-wide text-brand-navy">{{ member.position }}</span>
          </div>
        </div>
      </div>
    </template>
  </section>
</template>
