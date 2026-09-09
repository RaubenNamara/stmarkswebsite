<script setup lang="ts">
import { useHead } from '@unhead/vue'
import { api } from '../../services/api'
import PageHeader from '../../components/PageHeader.vue'
import EmptyState from '../../components/EmptyState.vue'
import ListingCard from '../../components/ListingCard.vue'
import { staticAsset } from '../../utils/staticAsset'

useHead({ title: 'Inspiration Night' })

const { data } = await api.get('/inspiration-night')
const items = data.data.inspiration as Array<Record<string, any>>

function stripHtml(content: string | null): string | null {
  return content ? content.replace(/<[^>]*>/g, '') : null
}
function excerptFor(content: string | null): string | null {
  const text = stripHtml(content)
  if (!text) return null
  return text.length > 160 ? text.slice(0, 160) + '...' : text
}
function formatDate(date: string | null): string | null {
  if (!date) return null
  return new Date(date).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' })
}
function metaFor(item: Record<string, any>): string | null {
  const parts = [formatDate(item.date), item.speaker].filter(Boolean)
  return parts.length ? parts.join(' — ') : null
}
</script>

<template>
  <PageHeader
    title="Inspiration Night"
    subtitle="An evening of wisdom, reflection and renewed purpose — igniting dreams and transforming lives."
    :bg-image="staticAsset('storage/images/smacon.jpg')"
  />

  <section class="container-wide py-14">
    <EmptyState v-if="!items.length" message="No Inspiration Night updates published yet." />

    <template v-else>
      <div v-reveal class="mx-auto max-w-2xl text-center">
        <span class="eyebrow">Motivation & Purpose</span>
        <h2 class="mt-2 font-display text-2xl font-bold text-brand-navy sm:text-3xl">Moments that changed lives</h2>
        <p class="mt-3 text-gray-600">Voices of wisdom and experience, challenging every learner to aim higher and believe in themselves.</p>
      </div>

      <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="item in items" :key="item.id" v-reveal>
          <ListingCard
            :image="item.image_url"
            :video="item.video_url"
            :title="item.title"
            :excerpt="excerptFor(item.description)"
            :full-text="stripHtml(item.description)"
            :meta="metaFor(item)"
          />
        </div>
      </div>
    </template>
  </section>
</template>
