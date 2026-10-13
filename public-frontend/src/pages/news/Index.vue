<script setup lang="ts">
import { useHead } from '@unhead/vue'
import { api } from '../../services/api'
import NewsCard from '../../components/NewsCard.vue'
import PageHeader from '../../components/PageHeader.vue'
import EmptyState from '../../components/EmptyState.vue'
import { staticAsset } from '../../utils/staticAsset'

useHead({ title: 'News' })

const { data } = await api.get('/news', { params: { limit: 100 } })
const newsItems = data.data.news as Array<Record<string, any>>
</script>

<template>
  <PageHeader title="News" :bg-image="staticAsset('storage/images/smacon.jpg')" />

  <section class="container-wide py-14">
    <EmptyState v-if="!newsItems.length" message="No news yet." />
    <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <NewsCard
        v-for="item in newsItems"
        :key="item.id"
        v-reveal
        variant="stacked"
        :url="`/news/${item.slug}`"
        :image="item.image_url"
        :title="item.title"
        :date="item.published_at"
        :excerpt="item.excerpt"
      />
    </div>
  </section>
</template>
