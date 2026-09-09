<script setup lang="ts">
import { useHead } from '@unhead/vue'
import { api } from '../../services/api'
import PageHeader from '../../components/PageHeader.vue'
import EmptyState from '../../components/EmptyState.vue'
import ListingCard from '../../components/ListingCard.vue'
import { staticAsset } from '../../utils/staticAsset'

useHead({ title: 'Co-Curricular Activities' })

const { data } = await api.get('/co-curricular')
const items = data.data.co_curricular as Array<Record<string, any>>

function stripHtml(content: string | null): string | null {
  return content ? content.replace(/<[^>]*>/g, '') : null
}
function excerptFor(content: string | null): string | null {
  const text = stripHtml(content)
  if (!text) return null
  return text.length > 160 ? text.slice(0, 160) + '...' : text
}
</script>

<template>
  <PageHeader
    title="Co-Curricular Activities"
    subtitle="Sports, music, dance, drama and talent — the activities that build character and community beyond the classroom."
    :bg-image="staticAsset('storage/images/smacon.jpg')"
  />

  <section class="container-wide py-14">
    <EmptyState v-if="!items.length" message="No co-curricular activities published yet." />

    <template v-else>
      <div v-reveal class="mx-auto max-w-2xl text-center">
        <span class="eyebrow">Beyond The Classroom</span>
        <h2 class="mt-2 font-display text-2xl font-bold text-brand-navy sm:text-3xl">Where talent and teamwork grow</h2>
        <p class="mt-3 text-gray-600">A well-rounded education means more than academics — here's how our learners explore, compete and express themselves.</p>
      </div>

      <div class="mt-12 grid gap-8 sm:grid-cols-2">
        <div v-for="item in items" :key="item.id" v-reveal>
          <ListingCard
            :image="item.image_url"
            :video="item.video_url"
            :title="item.title"
            :excerpt="excerptFor(item.content)"
            :full-text="stripHtml(item.content)"
          />
        </div>
      </div>
    </template>
  </section>
</template>
