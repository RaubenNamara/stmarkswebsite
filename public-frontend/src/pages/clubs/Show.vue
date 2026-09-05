<script setup lang="ts">
import { ref } from 'vue'
import { useHead } from '@unhead/vue'
import { api } from '../../services/api'
import NotFound from '../errors/NotFound.vue'

const props = defineProps<{ slug: string }>()

let club: Record<string, any> | null = null
try {
  const { data } = await api.get(`/clubs/${props.slug}`)
  club = data.data.club
} catch {
  club = null
}

useHead({ title: club ? club.title : 'Page not found' })

// Some photos are missing from the server (pre-existing, unrelated to this page) - drop them
// from the grid on load failure instead of showing a broken-image icon.
const failedIds = ref(new Set<number>())
function onImageError(id: number) {
  failedIds.value = new Set(failedIds.value).add(id)
}
</script>

<template>
  <NotFound v-if="!club" message="This club doesn't exist." />
  <article v-else class="mx-auto max-w-3xl px-6 py-14">
    <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ club.title }}</h1>
    <div class="prose prose-slate mt-6 max-w-none" v-html="club.content" />

    <div v-if="club.images?.length" class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3">
      <img
        v-for="img in club.images.filter((i: any) => !failedIds.has(i.id))"
        :key="img.id"
        :src="img.image_url"
        alt=""
        class="h-40 w-full rounded-lg bg-gray-100 object-cover"
        @error="onImageError(img.id)"
      >
    </div>

    <p class="mt-8"><router-link to="/clubs" class="font-semibold text-brand-navy hover:underline">&larr; Back to Clubs</router-link></p>
  </article>
</template>
