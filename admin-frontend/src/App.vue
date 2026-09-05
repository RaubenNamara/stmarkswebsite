<script setup lang="ts">
import ToastContainer from '@/components/common/ToastContainer.vue'
import ErrorBoundary from '@/components/common/ErrorBoundary.vue'
</script>

<template>
  <ErrorBoundary>
    <router-view />
  </ErrorBoundary>
  <ToastContainer />
</template>
