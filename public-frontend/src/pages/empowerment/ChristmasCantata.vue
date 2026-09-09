<script setup lang="ts">
import { useHead } from '@unhead/vue'
import { api } from '../../services/api'
import PageHeader from '../../components/PageHeader.vue'
import EmptyState from '../../components/EmptyState.vue'
import ListingCard from '../../components/ListingCard.vue'
import { staticAsset } from '../../utils/staticAsset'

useHead({ title: 'Christmas Cantata' })

const { data } = await api.get('/christmas-cantata')
const items = data.data.christmas_cantata as Array<Record<string, any>>

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
</script>

<template>
  <PageHeader
    title="Christmas Cantata"
    subtitle="Celebrating the birth of Christ through music, drama and scripture — a highlight of the school calendar."
    :bg-image="staticAsset('storage/images/smacon.jpg')"
  />

  <section class="container-wide py-14">
    <EmptyState v-if="!items.length" message="No Christmas Cantata updates published yet." />

    <template v-else>
      <div v-reveal class="mx-auto max-w-2xl text-center">
        <span class="eyebrow">Faith & Celebration</span>
        <h2 class="mt-2 font-display text-2xl font-bold text-brand-navy sm:text-3xl">A season of song and worship</h2>
        <p class="mt-3 text-gray-600">Every year, our choir and students bring the Christmas story to life through carols, drama and heartfelt performance.</p>
      </div>

      <div class="mt-12 grid gap-8 sm:grid-cols-2">
        <div v-for="item in items" :key="item.id" v-reveal>
          <ListingCard
            :image="item.image_url"
            :video="item.video_url"
            :title="item.title"
            :excerpt="excerptFor(item.description)"
            :full-text="stripHtml(item.description)"
            :meta="formatDate(item.date)"
          />
        </div>
      </div>
    </template>
  </section>
</template>
