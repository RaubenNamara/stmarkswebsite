<template>
  <div class="p-6 max-w-3xl">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl font-bold">{{ talk.title }}</h1>
      <Link :href="route('admin.inspiration.index')" class="btn-secondary">Back</Link>
    </div>

    <div class="mb-4">
      <img v-if="talk.image_url" :src="talk.image_url" class="w-full h-64 object-cover rounded" />
    </div>

    <div class="mb-4">
      <p class="text-gray-700" v-html="talk.description"></p>
    </div>

    <div class="mb-2"><strong>Speaker:</strong> {{ talk.speaker || '—' }}</div>
    <div><strong>Date:</strong> {{ talk.date || '—' }}</div>

    <div v-if="talk.video" class="mt-4">
      <strong>Video:</strong>
      <div class="mt-2">
        <iframe v-if="youtubeEmbed" :src="youtubeEmbed" class="w-full h-64" frameborder="0" allowfullscreen></iframe>
        <div v-else><a :href="talk.video" target="_blank" class="text-blue-600 underline">{{ talk.video }}</a></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

defineOptions({ layout: AdminLayout })

const props = defineProps({ talk: Object })

const youtubeEmbed = computed(() => {
  if (!props.talk.video) return null
  const url = props.talk.video
  const match = url.match(/(?:v=|\/)([0-9A-Za-z_-]{11})(?:\?|&|$)/)
  return match ? `https://www.youtube.com/embed/${match[1]}` : null
})
</script>

<style scoped>
.btn-secondary { padding: .5rem .75rem; background:#e5e7eb; border-radius:.375rem; }
</style>