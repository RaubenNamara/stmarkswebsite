<script setup lang="ts">
withDefaults(
  defineProps<{
    open: boolean
    title?: string
    message: string
    confirmLabel?: string
    danger?: boolean
  }>(),
  {
    title: 'Are you sure?',
    confirmLabel: 'Confirm',
    danger: false,
  },
)

const emit = defineEmits<{ confirm: []; cancel: [] }>()
</script>

<template>
  <div
    v-if="open"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
    role="dialog"
    aria-modal="true"
    :aria-label="title"
    @keydown.esc="emit('cancel')"
  >
    <div class="w-full max-w-sm rounded-lg bg-white p-6 shadow-xl dark:bg-slate-800">
      <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ title }}</h2>
      <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ message }}</p>
      <div class="mt-6 flex justify-end gap-3">
        <button
          type="button"
          class="rounded-md px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-700"
          autofocus
          @click="emit('cancel')"
        >
          Cancel
        </button>
        <button
          type="button"
          class="rounded-md px-4 py-2 text-sm font-medium text-white"
          :class="danger ? 'bg-red-600 hover:bg-red-700' : 'bg-slate-800 hover:bg-slate-900'"
          @click="emit('confirm')"
        >
          {{ confirmLabel }}
        </button>
      </div>
    </div>
  </div>
</template>
