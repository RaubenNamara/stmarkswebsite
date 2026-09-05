<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { api, apiErrorMessage } from '@/services/api'
import { useToast } from '@/composables/useToast'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import type { SinglePhotoItem } from '@/types'

interface FieldConfig {
  name: string
  label: string
  type?: 'text' | 'textarea'
  required?: boolean
}

const props = defineProps<{
  pageTitle: string
  endpoint: string
  listKey: string
  fields: FieldConfig[]
  photoField?: string
  createLabel?: string
  titleField?: string
}>()

const photoField = props.photoField ?? 'photo'
const titleField = props.titleField ?? props.fields[0]?.name ?? 'name'

const toast = useToast()
const items = ref<SinglePhotoItem[]>([])
const loading = ref(false)
const submitting = ref(false)
const confirmState = reactive({ open: false, id: null as number | null })
const editingId = ref<number | null>(null)
const editingPhoto = ref<string | null>(null)
const formCard = ref<HTMLElement | null>(null)

const form = reactive<Record<string, string>>({})
for (const f of props.fields) form[f.name] = ''
const photoFile = ref<File | null>(null)

async function fetchAll() {
  loading.value = true
  try {
    const { data } = await api.get(props.endpoint)
    items.value = data.data?.[props.listKey] ?? []
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to load'))
  } finally {
    loading.value = false
  }
}

function resetForm() {
  for (const f of props.fields) form[f.name] = ''
  photoFile.value = null
  editingId.value = null
  editingPhoto.value = null
}

function startEdit(item: SinglePhotoItem) {
  editingId.value = item.id
  for (const f of props.fields) form[f.name] = String(item[f.name] ?? '')
  photoFile.value = null
  editingPhoto.value = item.photo_url ?? null
  formCard.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

async function submit() {
  const body = new FormData()
  for (const [key, value] of Object.entries(form)) body.append(key, value)
  if (photoFile.value) body.append(photoField, photoFile.value)

  submitting.value = true
  try {
    if (editingId.value !== null) {
      await api.post(`${props.endpoint}/${editingId.value}`, body)
      toast.success('Updated successfully')
    } else {
      await api.post(props.endpoint, body)
      toast.success('Created successfully')
    }
    resetForm()
    await fetchAll()
  } catch (e) {
    toast.error(apiErrorMessage(e, editingId.value !== null ? 'Failed to update' : 'Failed to create'))
  } finally {
    submitting.value = false
  }
}

function requestDelete(id: number) {
  confirmState.open = true
  confirmState.id = id
}

async function confirmDelete() {
  const id = confirmState.id
  confirmState.open = false
  if (id === null) return
  try {
    await api.delete(`${props.endpoint}/${id}`)
    items.value = items.value.filter((i) => i.id !== id)
    if (editingId.value === id) resetForm()
    toast.success('Deleted successfully')
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to delete'))
  }
}

onMounted(fetchAll)
</script>

<template>
  <div class="p-8 space-y-8">
    <h1 class="text-3xl font-bold text-gray-800">{{ pageTitle }}</h1>

    <div ref="formCard" class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold">{{ editingId !== null ? 'Edit Item' : (createLabel ?? 'Add New') }}</h2>
        <button v-if="editingId !== null" type="button" class="text-sm text-gray-500 hover:underline" @click="resetForm">
          Cancel edit
        </button>
      </div>

      <form class="space-y-4" @submit.prevent="submit">
        <div v-for="f in fields" :key="f.name">
          <label class="block text-sm font-medium mb-1">{{ f.label }}</label>
          <textarea v-if="f.type === 'textarea'" v-model="form[f.name]" rows="4" :required="f.required" class="w-full border p-2 rounded"></textarea>
          <input v-else v-model="form[f.name]" type="text" :required="f.required" class="w-full border p-2 rounded" />
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Photo</label>
          <img v-if="editingPhoto" :src="editingPhoto" class="h-20 mb-2 rounded object-cover" />
          <input type="file" accept="image/*" @change="(e) => (photoFile = (e.target as HTMLInputElement).files?.[0] ?? null)" />
          <p v-if="editingId !== null" class="text-xs text-gray-400 mt-1">Leave blank to keep the current photo.</p>
        </div>

        <div class="flex gap-3">
          <button type="submit" :disabled="submitting" class="bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded disabled:opacity-50">
            {{ submitting ? 'Saving...' : editingId !== null ? 'Update' : 'Save' }}
          </button>
          <button v-if="editingId !== null" type="button" class="px-4 py-2 border rounded" @click="resetForm">Cancel</button>
        </div>
      </form>
    </div>

    <div>
      <h2 class="text-xl font-bold mb-4">All Items</h2>

      <div v-if="loading" class="text-center py-8 text-gray-500">Loading...</div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div v-for="item in items" :key="item.id" class="bg-white rounded-lg shadow overflow-hidden flex flex-col">
          <div class="h-40 bg-gray-100">
            <img v-if="item.photo_url" :src="item.photo_url" class="w-full h-full object-cover" />
            <div v-else class="flex items-center justify-center h-full text-gray-400 text-sm">No photo</div>
          </div>

          <div class="p-4 flex flex-col flex-1">
            <h3 class="font-semibold text-base mb-2 truncate">{{ item[titleField] }}</h3>
            <div class="mt-auto flex justify-end gap-2">
              <button type="button" class="border border-blue-900 text-blue-900 px-2 py-1 rounded text-sm" @click="startEdit(item)">Edit</button>
              <button type="button" class="bg-red-600 text-white px-2 py-1 rounded text-sm" @click="requestDelete(item.id)">Delete</button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="!loading && items.length === 0" class="text-center py-8 text-gray-500">Nothing here yet.</div>
    </div>

    <ConfirmDialog
      :open="confirmState.open"
      title="Delete item"
      message="Are you sure you want to delete this? This cannot be undone."
      confirm-label="Delete"
      danger
      @confirm="confirmDelete"
      @cancel="confirmState.open = false"
    />
  </div>
</template>
