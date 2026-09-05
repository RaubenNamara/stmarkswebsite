<template>
  <div class="max-w-4xl mx-auto p-6">
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold">{{ club.title }}</h1>
        <p class="text-sm text-gray-500">Club details and photos</p>
      </div>

      <Link href="/admin/clubs" class="px-3 py-2 border rounded">Admin</Link>
    </div>

    <div class="prose mb-6" v-html="club.content"></div>

    <div v-if="club.images && club.images.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="img in club.images" :key="img.id" class="border rounded overflow-hidden">
        <img :src="`/storage/${img.image_path}`" class="w-full h-56 object-cover" />
        <div class="p-2 text-sm text-gray-700">{{ img.caption }}</div>
      </div>
    </div>

    <div v-else class="text-gray-600">No images for this club yet.</div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
const props = defineProps({ club: { type: Object, required: true } })
</script>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue'
export default { layout: AdminLayout }
</script>

<style scoped>
.prose img { display:block; }
</style>