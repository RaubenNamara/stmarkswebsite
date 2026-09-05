<script setup lang="ts">
import { onErrorCaptured, ref } from 'vue'

const error = ref<string | null>(null)

onErrorCaptured((err) => {
  error.value = err instanceof Error ? err.message : String(err)
  return false // stop propagation - we've handled it
})

function reload() {
  window.location.reload()
}
</script>

<template>
  <div v-if="error" class="min-h-screen flex items-center justify-center bg-gray-50 p-6">
    <div class="max-w-md text-center">
      <h1 class="text-xl font-bold text-gray-800">Something went wrong</h1>
      <p class="mt-2 text-sm text-gray-500">{{ error }}</p>
      <button type="button" class="mt-6 bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded" @click="reload">
        Reload
      </button>
    </div>
  </div>
  <slot v-else />
</template>
