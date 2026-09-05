<script setup lang="ts">
import ImageVideoCrudPage from '@/components/admin/ImageVideoCrudPage.vue'
</script>

<template>
  <ImageVideoCrudPage
    page-title="Manage Chaplaincy"
    endpoint="/admin/chaplaincy"
    list-key="chaplaincies"
    create-label="Add Chaplaincy Post"
    :fields="[
      { name: 'content', label: 'Content', type: 'textarea' },
      { name: 'video_link', label: 'Video Link (URL)' },
    ]"
  />
</template>
