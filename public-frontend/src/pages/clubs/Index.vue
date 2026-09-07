<script setup lang="ts">
import { useHead } from '@unhead/vue'
import { api } from '../../services/api'
import PageHeader from '../../components/PageHeader.vue'
import ListingCard from '../../components/ListingCard.vue'
import EmptyState from '../../components/EmptyState.vue'

useHead({ title: 'Clubs' })

const { data } = await api.get('/clubs')
const clubs = data.data.clubs as Array<Record<string, any>>

function excerptFor(content: string | null): string | null {
  if (!content) return null
  const text = content.replace(/<[^>]*>/g, '')
  return text.length > 150 ? text.slice(0, 150) + '...' : text
}
</script>

<template>
  <PageHeader title="Clubs" />

  <section class="container-wide py-14">
    <EmptyState v-if="!clubs.length" message="No clubs yet." />
    <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <ListingCard
        v-for="club in clubs"
        :key="club.id"
        :url="`/clubs/${club.slug}`"
        :image="club.images?.[0]?.image_url"
        :title="club.title"
        :excerpt="excerptFor(club.content)"
      />
    </div>
  </section>
</template>
