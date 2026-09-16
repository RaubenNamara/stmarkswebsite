<script setup lang="ts">
import ImageVideoCrudPage from '@/components/admin/ImageVideoCrudPage.vue'
</script>

<template>
  <ImageVideoCrudPage
    page-title="SMOSA Alumni"
    endpoint="/admin/smosa"
    list-key="alumni"
    create-label="Add Alumni"
    title-field="name"
    title-label="Name"
    image-field="photo"
    subtitle-field="profession"
    :avatar-style="true"
    :fields="[
      { name: 'profession', label: 'Profession' },
      { name: 'message', label: 'Message', type: 'textarea' },
    ]"
  />
</template>
