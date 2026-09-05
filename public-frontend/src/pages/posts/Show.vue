<script setup lang="ts">
import { useHead } from '@unhead/vue'
import { api } from '../../services/api'
import NotFound from '../errors/NotFound.vue'

const props = defineProps<{ slug: string }>()

let post: Record<string, any> | null = null
try {
  const { data } = await api.get(`/posts/${props.slug}`)
  post = data.data.post
} catch {
  post = null
}

useHead({ title: post ? post.title : 'Page not found' })
</script>

<template>
  <NotFound v-if="!post" message="This post doesn't exist." />
  <article v-else class="mx-auto max-w-3xl px-6 py-14">
    <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ post.title }}</h1>
    <div class="prose prose-slate mt-6 max-w-none" v-html="post.content" />
    <p class="mt-8"><router-link to="/posts" class="font-semibold text-brand-navy hover:underline">&larr; Back to Posts</router-link></p>
  </article>
</template>
