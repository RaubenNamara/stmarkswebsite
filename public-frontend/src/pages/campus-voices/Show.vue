<script setup lang="ts">
import { ref } from 'vue'
import { useHead } from '@unhead/vue'
import { api } from '../../services/api'
import NotFound from '../errors/NotFound.vue'

const props = defineProps<{ slug: string }>()

let article: Record<string, any> | null = null
try {
  const { data } = await api.get(`/campus-voices/${props.slug}`)
  article = data.data.article
} catch {
  article = null
}

useHead({
  title: article ? article.title : 'Page not found',
  meta: article?.summary ? [{ name: 'description', content: article.summary }] : [],
})

// The image file is missing from the server for some older articles (pre-existing, unrelated to
// this page) - hide it on load failure instead of showing a broken-image icon.
const imageFailed = ref(false)
</script>

<template>
  <NotFound v-if="!article" message="This article doesn't exist." />
  <article v-else class="mx-auto max-w-3xl px-6 py-14">
    <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ article.title }}</h1>
    <p class="mt-2 text-sm text-gray-500">
      by {{ article.student_name }}
      <template v-if="article.category"> &middot; {{ article.category }}</template>
      &middot; {{ article.reading_time }} min read &middot; {{ article.views }} views
    </p>

    <img v-if="article.featured_image_url && !imageFailed" :src="article.featured_image_url" :alt="article.title" class="mt-6 w-full rounded-xl object-cover" @error="imageFailed = true">

    <div class="prose prose-slate mt-6 max-w-none" v-html="article.content" />

    <div v-if="article.author_bio" class="card mt-8 bg-gray-50">
      <strong class="text-gray-900">About the author</strong>
      <p class="mt-1 text-sm text-gray-600">{{ article.author_bio }}</p>
    </div>

    <p class="mt-8"><router-link to="/campus-voices" class="font-semibold text-brand-navy hover:underline">&larr; Back to eVoices</router-link></p>
  </article>
</template>
