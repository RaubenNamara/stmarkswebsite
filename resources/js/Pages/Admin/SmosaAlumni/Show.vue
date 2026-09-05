<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold">{{ alumni.name }}</h1>
      <div>
        <Link :href="route('admin.smosa.index')" class="text-gray-600 mr-4">Back</Link>
        <Link :href="route('admin.smosa.edit', alumni.id)" class="text-blue-600">Edit</Link>
      </div>
    </div>

    <div class="bg-white p-6 rounded shadow">
      <div class="flex gap-6">
        <div v-if="alumni.photo">
          <img :src="alumni.photo" class="w-48 h-36 object-cover rounded" alt="" />
        </div>

        <div>
          <p class="text-lg font-semibold">{{ alumni.profession }}</p>
          <p class="mt-4 whitespace-pre-line">{{ alumni.message }}</p>

          <div v-if="alumni.video" class="mt-4">
            <p class="text-sm font-medium">Video link</p>
            <a :href="alumni.video" target="_blank" class="text-blue-600 break-all">{{ alumni.video }}</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'

defineOptions({
  layout: AdminLayout
})

const props = defineProps({
  alumni: Object
})
</script>