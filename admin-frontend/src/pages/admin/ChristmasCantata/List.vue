<script setup lang="ts">
import ImageVideoCrudPage from '@/components/admin/ImageVideoCrudPage.vue'
</script>

<template>
  <ImageVideoCrudPage
    page-title="Manage Cantata"
    endpoint="/admin/christmas-cantata"
    list-key="cantatas"
    create-label="Add Cantata"
    title-label="Title"
    subtitle-field="choir"
    :fields="[
      { name: 'choir', label: 'Choir' },
      { name: 'date', label: 'Date', type: 'date' },
      { name: 'description', label: 'Description', type: 'textarea' },
    ]"
  />
</template>
