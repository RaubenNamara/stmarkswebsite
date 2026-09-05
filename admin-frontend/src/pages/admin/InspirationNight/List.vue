<script setup lang="ts">
import ImageVideoCrudPage from '@/components/admin/ImageVideoCrudPage.vue'
</script>

<template>
  <ImageVideoCrudPage
    page-title="Inspiration Night"
    endpoint="/admin/inspiration"
    list-key="inspiration_nights"
    create-label="Add Inspiration Night"
    :fields="[
      { name: 'description', label: 'Description', type: 'textarea', required: true },
      { name: 'date', label: 'Date', type: 'date' },
      { name: 'speaker', label: 'Speaker' },
    ]"
  />
</template>
