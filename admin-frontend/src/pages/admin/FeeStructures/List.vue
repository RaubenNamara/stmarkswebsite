<script setup lang="ts">
import PdfCrudPage from '@/components/admin/PdfCrudPage.vue'
</script>

<template>
  <PdfCrudPage
    page-title="Fee, Personal Needs, School Rules & Calendar"
    endpoint="/admin/fee-structures"
    list-key="fee_structures"
    create-label="Upload Fee Structure"
  />
</template>
