<script setup lang="ts">
defineProps<{ message: string }>()
</script>

<template>
  <div class="rounded-xl border border-dashed border-gray-300 py-16 text-center text-gray-400">
    {{ message }}
  </div>
</template>
