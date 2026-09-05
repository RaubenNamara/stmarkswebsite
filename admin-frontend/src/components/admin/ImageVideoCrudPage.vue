<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { api, apiErrorMessage } from '@/services/api'
import { useToast } from '@/composables/useToast'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import type { ImageVideoItem } from '@/types'

/**
 * Shared list/create/edit page for the ~8 "flat table with an optional image and video" admin
 * domains (Co-Curricular, Girl-Boy Talk, Mentorship, Chaplaincy, Inspiration Night, Christmas
 * Cantata, Student Leadership, SMOSA Alumni) - identical UI shape, only labels/fields differ, so
 * one component driven by config instead of 8 near-duplicate files (mirrors the backend's
 * ImageVideoContentService for the same reason).
 */
interface FieldConfig {
  name: string
  label: string
  type?: 'text' | 'textarea' | 'date'
  required?: boolean
}

const props = defineProps<{
  pageTitle: string
  endpoint: string
  listKey: string
  titleField?: string
  titleLabel?: string
  fields?: FieldConfig[]
  imageField?: string
  videoField?: string
  createLabel?: string
}>()

const titleField = props.titleField ?? 'title'
const imageField = props.imageField ?? 'image'
const videoField = props.videoField ?? 'video'
const fields = props.fields ?? []

const toast = useToast()
const items = ref<ImageVideoItem[]>([])
const loading = ref(false)
const submitting = ref(false)
const confirmState = reactive({ open: false, id: null as number | null })
const editingId = ref<number | null>(null)
const editingCurrent = ref<{ image: string | null; video: string | null }>({ image: null, video: null })
const formCard = ref<HTMLElement | null>(null)

const form = reactive<Record<string, string>>({ [titleField]: '' })
for (const f of fields) form[f.name] = ''

const imageFile = ref<File | null>(null)
const videoFile = ref<File | null>(null)

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
  form[titleField] = ''
  for (const f of fields) form[f.name] = ''
  imageFile.value = null
  videoFile.value = null
  editingId.value = null
  editingCurrent.value = { image: null, video: null }
}

function startEdit(item: ImageVideoItem) {
  editingId.value = item.id
  form[titleField] = String(item[titleField] ?? '')
  for (const f of fields) form[f.name] = String(item[f.name] ?? '')
  imageFile.value = null
  videoFile.value = null
  editingCurrent.value = {
    image: (item.image_url as string | null) ?? null,
    video: (item.video_url as string | null) ?? null,
  }
  formCard.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

async function submit() {
  const body = new FormData()
  for (const [key, value] of Object.entries(form)) body.append(key, value)
  if (imageFile.value) body.append(imageField, imageFile.value)
  if (videoFile.value) body.append(videoField, videoFile.value)

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
        <div>
          <label class="block text-sm font-medium mb-1">{{ titleLabel ?? 'Title' }}</label>
          <input v-model="form[titleField]" type="text" required class="w-full border p-2 rounded" />
        </div>

        <div v-for="f in fields" :key="f.name">
          <label class="block text-sm font-medium mb-1">{{ f.label }}</label>
          <textarea
            v-if="f.type === 'textarea'"
            v-model="form[f.name]"
            rows="4"
            :required="f.required"
            class="w-full border p-2 rounded"
          ></textarea>
          <input
            v-else
            v-model="form[f.name]"
            :type="f.type === 'date' ? 'date' : 'text'"
            :required="f.required"
            class="w-full border p-2 rounded"
          />
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Image</label>
            <img v-if="editingCurrent.image" :src="editingCurrent.image" class="h-20 mb-2 rounded object-cover" />
            <input type="file" accept="image/*" @change="(e) => (imageFile = (e.target as HTMLInputElement).files?.[0] ?? null)" />
            <p v-if="editingId !== null" class="text-xs text-gray-400 mt-1">Leave blank to keep the current image.</p>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Video</label>
            <video v-if="editingCurrent.video" :src="editingCurrent.video" controls class="h-20 mb-2 rounded"></video>
            <input type="file" accept="video/*" @change="(e) => (videoFile = (e.target as HTMLInputElement).files?.[0] ?? null)" />
            <p v-if="editingId !== null" class="text-xs text-gray-400 mt-1">Leave blank to keep the current video.</p>
          </div>
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

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="item in items" :key="item.id" class="bg-white rounded-lg shadow overflow-hidden flex flex-col">
          <div class="h-40 bg-gray-100">
            <img v-if="item.image_url" :src="item.image_url as string" class="w-full h-full object-cover" />
            <video v-else-if="item.video_url" :src="item.video_url as string" controls class="w-full h-full object-cover"></video>
            <div v-else class="flex items-center justify-center h-full text-gray-400 text-sm">No media</div>
          </div>

          <div class="p-4 flex flex-col flex-1">
            <h3 class="font-semibold text-lg mb-2 truncate">{{ item[titleField] }}</h3>
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
