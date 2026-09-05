<script setup>
const props = defineProps({
  category: {
    type: String,
    default: ''
  }
})
</script>

<template>
  <span v-if="category" class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
    {{ category }}
  </span>
</template>
