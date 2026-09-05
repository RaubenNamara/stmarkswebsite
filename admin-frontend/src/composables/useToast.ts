import { reactive } from 'vue'

export interface Toast {
  id: number
  type: 'success' | 'error' | 'info'
  message: string
}

const toasts = reactive<Toast[]>([])
let nextId = 1

function push(type: Toast['type'], message: string, durationMs = 4000): void {
  const id = nextId++
  toasts.push({ id, type, message })
  setTimeout(() => dismiss(id), durationMs)
}

function dismiss(id: number): void {
  const index = toasts.findIndex((t) => t.id === id)
  if (index !== -1) toasts.splice(index, 1)
}

/** One shared toast queue for the whole app - no per-page inline toast markup. */
export function useToast() {
  return {
    toasts,
    success: (message: string) => push('success', message),
    error: (message: string) => push('error', message),
    info: (message: string) => push('info', message),
    dismiss,
  }
}
