<script setup lang="ts">
import ImageVideoCrudPage from '@/components/admin/ImageVideoCrudPage.vue'
</script>

<template>
  <ImageVideoCrudPage
    page-title="Girl - Boy Talk"
    endpoint="/admin/girlboytalk"
    list-key="talks"
    create-label="Add Talk"
    :fields="[
      { name: 'description', label: 'Description', type: 'textarea', required: true },
      { name: 'video_link', label: 'Video Link (URL)' },
    ]"
  />
</template>
