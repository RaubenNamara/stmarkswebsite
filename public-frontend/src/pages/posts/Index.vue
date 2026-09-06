<script setup lang="ts">
import { useHead } from '@unhead/vue'
import { api } from '../../services/api'
import PageHeader from '../../components/PageHeader.vue'
import ListingCard from '../../components/ListingCard.vue'
import EmptyState from '../../components/EmptyState.vue'

useHead({ title: 'Posts' })

const { data } = await api.get('/posts')
const posts = data.data.posts as Array<Record<string, any>>

function excerptFor(content: string): string {
  const text = content.replace(/<[^>]*>/g, '')
  return text.length > 160 ? text.slice(0, 160) + '...' : text
}
</script>

<template>
  <PageHeader title="Posts" />

  <section class="mx-auto max-w-7xl px-6 py-14">
    <EmptyState v-if="!posts.length" message="No posts yet." />
    <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <ListingCard
        v-for="post in posts"
        :key="post.id"
        :url="`/posts/${post.slug}`"
        :title="post.title"
        :excerpt="excerptFor(post.content)"
      />
    </div>
  </section>
</template>
