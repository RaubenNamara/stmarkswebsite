<script setup lang="ts">
import ImageVideoCrudPage from '@/components/admin/ImageVideoCrudPage.vue'
</script>

<template>
  <ImageVideoCrudPage
    page-title="Manage Co-Curriculars"
    endpoint="/admin/co-curricular"
    list-key="co_curriculars"
    create-label="Add Co-Curricular Activity"
    :fields="[{ name: 'content', label: 'Content', type: 'textarea' }]"
  />
</template>
