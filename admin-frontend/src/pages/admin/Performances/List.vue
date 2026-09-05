<script setup lang="ts">
import PdfCrudPage from '@/components/admin/PdfCrudPage.vue'
</script>

<template>
  <PdfCrudPage
    page-title="Performance & Circulars"
    endpoint="/admin/performances"
    list-key="performances"
    create-label="Upload Performance / Circular"
  />
</template>
