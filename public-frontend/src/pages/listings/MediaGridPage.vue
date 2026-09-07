<script setup lang="ts">
import { useHead } from '@unhead/vue'
import { api } from '../../services/api'
import PageHeader from '../../components/PageHeader.vue'
import ListingCard from '../../components/ListingCard.vue'
import EmptyState from '../../components/EmptyState.vue'

/**
 * Vue equivalent of public-site's templates/pages/listings/media-grid.php - one shared page for
 * the ~10 "flat table with optional image/video" domains, configured per-route (see router/index.ts).
 */
const props = defineProps<{
  endpoint: string
  collectionKey: string
  pageTitle: string
  titleField: string
  textFields: Record<string, string>
  imageField?: string
  videoField?: string
}>()

useHead({ title: props.pageTitle })

const { data } = await api.get(props.endpoint)
const items = data.data[props.collectionKey] as Array<Record<string, any>>
const imageField = props.imageField ?? 'image_url'
const videoField = props.videoField ?? 'video_url'

function excerptFor(item: Record<string, any>): string | null {
  for (const column of Object.keys(props.textFields)) {
    if (item[column]) {
      const text = String(item[column]).replace(/<[^>]*>/g, '')
      return text.length > 180 ? text.slice(0, 180) + '...' : text
    }
  }
  return null
}
</script>

<template>
  <PageHeader :title="pageTitle" />

  <section class="mx-auto max-w-7xl px-6 py-14 2xl:max-w-[1600px]">
    <EmptyState v-if="!items.length" message="Nothing here yet." />
    <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <ListingCard
        v-for="item in items"
        :key="item.id"
        :image="item[imageField]"
        :video="item[videoField]"
        :title="item[titleField]"
        :excerpt="excerptFor(item)"
      />
    </div>
  </section>
</template>
