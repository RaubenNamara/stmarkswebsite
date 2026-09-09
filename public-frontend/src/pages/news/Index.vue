<script setup lang="ts">
import { useHead } from '@unhead/vue'
import { api } from '../../services/api'
import ListingCard from '../../components/ListingCard.vue'
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
      <ListingCard
        v-for="item in newsItems"
        :key="item.id"
        :url="`/news/${item.slug}`"
        :image="item.image_url"
        :title="item.title"
        :excerpt="item.excerpt"
      />
    </div>
  </section>
</template>
