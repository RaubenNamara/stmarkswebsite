<script setup lang="ts">
import { useHead } from '@unhead/vue'
import { api } from '../../services/api'
import PageHeader from '../../components/PageHeader.vue'
import ListingCard from '../../components/ListingCard.vue'
import EmptyState from '../../components/EmptyState.vue'

useHead({ title: 'Campus Voices' })

const { data } = await api.get('/campus-voices')
const articles = data.data.articles as Array<Record<string, any>>
</script>

<template>
  <PageHeader title="Campus Voices" subtitle="Stories, reflections and voices from our student community." />

  <section class="mx-auto max-w-6xl px-6 py-14">
    <EmptyState v-if="!articles.length" message="No articles yet." />
    <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <ListingCard
        v-for="article in articles"
        :key="article.id"
        :url="`/campus-voices/${article.slug}`"
        :image="article.featured_image_url"
        :title="article.title"
        :excerpt="article.summary ? `by ${article.student_name} — ${article.summary}` : `by ${article.student_name}`"
        :meta="`${article.reading_time} min read`"
        :badge="article.featured ? 'Featured' : null"
      />
    </div>
  </section>
</template>
