<script setup lang="ts">
import { useToast } from '@/composables/useToast'

const { toasts, dismiss } = useToast()

const styles: Record<string, string> = {
  success: 'bg-green-600',
  error: 'bg-red-600',
  info: 'bg-slate-700',
}
</script>

<template>
  <div class="fixed top-4 right-4 z-50 flex flex-col gap-2" aria-live="polite" aria-atomic="false">
    <TransitionGroup name="toast">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        role="status"
        class="min-w-64 max-w-sm rounded-lg px-4 py-3 text-sm text-white shadow-lg cursor-pointer"
        :class="styles[toast.type]"
        tabindex="0"
        @click="dismiss(toast.id)"
        @keydown.enter="dismiss(toast.id)"
      >
        {{ toast.message }}
      </div>
    </TransitionGroup>
  </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.2s ease;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(1rem);
}
</style>
