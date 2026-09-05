import { reactive, ref } from 'vue'
import { api, apiErrorMessage } from '@/services/api'
import { useToast } from '@/composables/useToast'
import type { ApiResponse } from '@/types'

/**
 * Shared data/mutation logic for the ~20 near-identical admin CRUD pages (list + create-form +
 * delete, some with update). Each page still writes its own template (fields differ too much for
 * a generic form renderer to be worth the complexity - see the plan's "don't over-abstract"
 * guidance), but the fetch/create/update/delete/toast/confirm wiring is one implementation.
 */
export function useCrudResource<T extends { id: number }>(endpoint: string, opts: { listKey?: string } = {}) {
  const toast = useToast()
  const items = ref<T[]>([])
  const loading = ref(false)
  const submitting = ref(false)
  const confirmState = reactive({ open: false, id: null as number | null })

  async function fetchAll(params: Record<string, unknown> = {}) {
    loading.value = true
    try {
      const { data } = await api.get<ApiResponse<any>>(endpoint, { params })
      const payload = data.data
      const key = opts.listKey ?? null
      items.value = key ? (payload?.[key] ?? []) : (payload?.data ?? payload ?? [])
    } catch (e) {
      toast.error(apiErrorMessage(e, 'Failed to load'))
    } finally {
      loading.value = false
    }
  }

  async function create(body: FormData | Record<string, unknown>): Promise<boolean> {
    submitting.value = true
    try {
      await api.post(endpoint, body)
      toast.success('Created successfully')
      return true
    } catch (e) {
      toast.error(apiErrorMessage(e, 'Failed to create'))
      return false
    } finally {
      submitting.value = false
    }
  }

  async function update(id: number, body: FormData | Record<string, unknown>): Promise<boolean> {
    submitting.value = true
    try {
      await api.post(`${endpoint}/${id}`, body)
      toast.success('Updated successfully')
      return true
    } catch (e) {
      toast.error(apiErrorMessage(e, 'Failed to update'))
      return false
    } finally {
      submitting.value = false
    }
  }

  function requestDelete(id: number) {
    confirmState.open = true
    confirmState.id = id
  }

  async function confirmDelete(onDeleted?: (id: number) => void): Promise<void> {
    const id = confirmState.id
    confirmState.open = false
    if (id === null) return

    try {
      await api.delete(`${endpoint}/${id}`)
      items.value = items.value.filter((item) => item.id !== id) as T[]
      toast.success('Deleted successfully')
      onDeleted?.(id)
    } catch (e) {
      toast.error(apiErrorMessage(e, 'Failed to delete'))
    }
  }

  return { items, loading, submitting, confirmState, fetchAll, create, update, requestDelete, confirmDelete }
}
