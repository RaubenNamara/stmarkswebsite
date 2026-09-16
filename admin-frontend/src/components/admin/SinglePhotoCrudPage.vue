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
  subtitleField?: string
  /** Enables move-up/move-down buttons on each card, posting the full reordered id list to `${endpoint}-reorder`. */
  reorderable?: boolean
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

const photoUrlField = `${photoField}_url`

function startEdit(item: SinglePhotoItem) {
  editingId.value = item.id
  for (const f of props.fields) form[f.name] = String(item[f.name] ?? '')
  photoFile.value = null
  editingPhoto.value = (item[photoUrlField] as string | undefined) ?? null
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

const reordering = ref(false)

async function moveItem(index: number, direction: -1 | 1) {
  const target = index + direction
  if (target < 0 || target >= items.value.length) return

  const reordered = [...items.value]
  const [moved] = reordered.splice(index, 1)
  reordered.splice(target, 0, moved)
  items.value = reordered

  reordering.value = true
  try {
    await api.post(`${props.endpoint}-reorder`, { ids: reordered.map((i) => i.id) })
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to save new order'))
    await fetchAll()
  } finally {
    reordering.value = false
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
  <div class="p-6 sm:p-8 space-y-8 max-w-6xl mx-auto">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">{{ pageTitle }}</h1>
      <p class="mt-1 text-sm text-gray-500">{{ items.length }} {{ items.length === 1 ? 'item' : 'items' }} published</p>
    </div>

    <div ref="formCard" class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6 sm:p-8">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-bold text-gray-900">{{ editingId !== null ? 'Edit Item' : (createLabel ?? 'Add New') }}</h2>
        <button v-if="editingId !== null" type="button" class="text-sm font-medium text-gray-500 hover:text-gray-700 hover:underline" @click="resetForm">
          Cancel edit
        </button>
      </div>

      <form class="space-y-5" @submit.prevent="submit">
        <div v-for="f in fields" :key="f.name">
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ f.label }}</label>
          <textarea
            v-if="f.type === 'textarea'"
            v-model="form[f.name]"
            rows="4"
            :required="f.required"
            class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20"
          ></textarea>
          <input
            v-else
            v-model="form[f.name]"
            type="text"
            :required="f.required"
            class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20"
          />
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Photo</label>
          <img v-if="editingPhoto" :src="editingPhoto" class="h-24 w-24 mb-2 rounded-full object-cover object-top ring-1 ring-gray-200" />
          <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-dashed border-gray-300 px-3.5 py-3 text-sm text-gray-500 transition hover:border-blue-500/50 hover:bg-blue-50/50">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 8a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2v-6a2 2 0 00-2-2M4 8V6a2 2 0 012-2h4l2 2h6a2 2 0 012 2v2" /></svg>
            <span class="truncate">{{ photoFile ? photoFile.name : 'Choose a photo' }}</span>
            <input type="file" accept="image/*" class="hidden" @change="(e) => (photoFile = (e.target as HTMLInputElement).files?.[0] ?? null)" />
          </label>
          <p v-if="editingId !== null" class="text-xs text-gray-400 mt-1.5">Leave blank to keep the current photo.</p>
        </div>

        <div class="flex gap-3 pt-2">
          <button type="submit" :disabled="submitting" class="bg-blue-900 hover:bg-blue-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition disabled:opacity-50">
            {{ submitting ? 'Saving...' : editingId !== null ? 'Update' : 'Save' }}
          </button>
          <button v-if="editingId !== null" type="button" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition" @click="resetForm">Cancel</button>
        </div>
      </form>
    </div>

    <div>
      <h2 class="text-lg font-bold text-gray-900 mb-4">All Items</h2>

      <div v-if="loading" class="flex items-center justify-center gap-2 py-12 text-gray-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" /></svg>
        <span class="text-sm">Loading...</span>
      </div>

      <div v-else-if="items.length" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5">
        <div v-for="(item, index) in items" :key="item.id" class="relative bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-4 text-center transition hover:shadow-md hover:-translate-y-0.5">
          <div v-if="reorderable" class="absolute left-2 top-2 flex flex-col gap-0.5">
            <button
              type="button"
              class="flex h-5 w-5 items-center justify-center rounded bg-gray-100 text-gray-500 transition hover:bg-blue-100 hover:text-blue-900 disabled:opacity-30 disabled:hover:bg-gray-100 disabled:hover:text-gray-500"
              :disabled="index === 0 || reordering"
              aria-label="Move up"
              @click="moveItem(index, -1)"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>
            </button>
            <button
              type="button"
              class="flex h-5 w-5 items-center justify-center rounded bg-gray-100 text-gray-500 transition hover:bg-blue-100 hover:text-blue-900 disabled:opacity-30 disabled:hover:bg-gray-100 disabled:hover:text-gray-500"
              :disabled="index === items.length - 1 || reordering"
              aria-label="Move down"
              @click="moveItem(index, 1)"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
            </button>
          </div>

          <img v-if="item[photoUrlField]" :src="item[photoUrlField] as string" class="w-20 h-20 mx-auto rounded-full object-cover object-top" />
          <div v-else class="flex w-20 h-20 mx-auto items-center justify-center rounded-full bg-gray-100 text-gray-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
          </div>

          <p class="mt-3 font-semibold text-sm text-gray-900 truncate">{{ item[titleField] }}</p>
          <p v-if="subtitleField && item[subtitleField]" class="text-xs text-gray-500 truncate">{{ item[subtitleField] }}</p>

          <div class="mt-3 flex justify-center gap-2">
            <button type="button" class="border border-blue-900 text-blue-900 px-2.5 py-1 rounded-md text-xs font-semibold hover:bg-blue-50 transition" @click="startEdit(item)">Edit</button>
            <button type="button" class="bg-red-600 hover:bg-red-700 text-white px-2.5 py-1 rounded-md text-xs font-semibold transition" @click="requestDelete(item.id)">Delete</button>
          </div>
        </div>
      </div>

      <div v-else class="flex flex-col items-center gap-2 rounded-xl border-2 border-dashed border-gray-200 py-16 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 20.25a48.25 48.25 0 01-8.135-.687c-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" /></svg>
        <p class="text-sm text-gray-400">Nothing here yet.</p>
      </div>
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
