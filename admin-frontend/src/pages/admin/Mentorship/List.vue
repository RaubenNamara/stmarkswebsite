<script setup lang="ts">
import ImageVideoCrudPage from '@/components/admin/ImageVideoCrudPage.vue'
</script>

<template>
  <ImageVideoCrudPage
    page-title="Manage Mentorship"
    endpoint="/admin/mentorship"
    list-key="mentorships"
    create-label="Add Mentorship"
    :fields="[
      { name: 'caption', label: 'Caption' },
      { name: 'description', label: 'Description', type: 'textarea' },
      { name: 'video_link', label: 'Video Link (URL)' },
    ]"
  />
</template>
