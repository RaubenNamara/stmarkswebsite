<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { api, apiErrorMessage } from '@/services/api'
import { useToast } from '@/composables/useToast'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import type { SinglePhotoItem } from '@/types'

const toast = useToast()
const items = ref<SinglePhotoItem[]>([])
const loading = ref(false)
const submitting = ref(false)
const confirmState = reactive({ open: false, id: null as number | null })
const editingId = ref<number | null>(null)
const editingPhoto = ref<string | null>(null)
const formCard = ref<HTMLElement | null>(null)

const form = reactive({ name: '', department: '', category: '' })
const photoFile = ref<File | null>(null)

async function fetchAll() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/staff')
    items.value = data.data?.staff ?? []
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to load staff'))
  } finally {
    loading.value = false
  }
}

function resetForm() {
  form.name = ''
  form.department = ''
  form.category = ''
  photoFile.value = null
  editingId.value = null
  editingPhoto.value = null
}

function startEdit(item: SinglePhotoItem) {
  editingId.value = item.id
  form.name = String(item.name ?? '')
  form.department = String(item.department ?? '')
  form.category = String(item.category ?? '')
  photoFile.value = null
  editingPhoto.value = item.photo_url ?? null
  formCard.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

async function submit() {
  const body = new FormData()
  body.append('name', form.name)
  body.append('department', form.department)
  body.append('category', form.category)
  if (photoFile.value) body.append('photo', photoFile.value)

  submitting.value = true
  try {
    if (editingId.value !== null) {
      await api.post(`/admin/staff/${editingId.value}`, body)
      toast.success('Staff member updated')
    } else {
      await api.post('/admin/staff', body)
      toast.success('Staff member created')
    }
    resetForm()
    await fetchAll()
  } catch (e) {
    toast.error(apiErrorMessage(e, editingId.value !== null ? 'Failed to update staff member' : 'Failed to create staff member'))
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
    await api.delete(`/admin/staff/${id}`)
    items.value = items.value.filter((i) => i.id !== id)
    if (editingId.value === id) resetForm()
    toast.success('Staff member deleted')
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to delete'))
  }
}

async function move(index: number, direction: -1 | 1) {
  const target = index + direction
  if (target < 0 || target >= items.value.length) return
  const reordered = [...items.value]
  ;[reordered[index], reordered[target]] = [reordered[target], reordered[index]]
  items.value = reordered

  try {
    await api.post('/admin/staff-reorder', { ids: reordered.map((i) => i.id) })
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to save order'))
    await fetchAll()
  }
}

onMounted(fetchAll)
</script>

<template>
  <div class="p-8 space-y-8">
    <h1 class="text-3xl font-bold text-gray-800">Manage Staff</h1>

    <div ref="formCard" class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold">{{ editingId !== null ? 'Edit Staff Member' : 'Add Staff Member' }}</h2>
        <button v-if="editingId !== null" type="button" class="text-sm text-gray-500 hover:underline" @click="resetForm">
          Cancel edit
        </button>
      </div>

      <form class="space-y-4" @submit.prevent="submit">
        <div>
          <label class="block text-sm font-medium mb-1">Name</label>
          <input v-model="form.name" type="text" required class="w-full border p-2 rounded" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Department</label>
          <input v-model="form.department" type="text" required class="w-full border p-2 rounded" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Category</label>
          <input v-model="form.category" type="text" placeholder="e.g. Teaching, Administration, Support" class="w-full border p-2 rounded" />
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
      <h2 class="text-xl font-bold mb-4">All Staff (use ↑/↓ to reorder display order)</h2>

      <div v-if="loading" class="text-center py-8 text-gray-500">Loading...</div>

      <div v-else class="bg-white rounded-lg shadow divide-y">
        <div v-for="(item, index) in items" :key="item.id" class="flex items-center gap-4 p-4">
          <div class="flex flex-col">
            <button type="button" class="text-gray-400 hover:text-gray-700" :disabled="index === 0" @click="move(index, -1)">▲</button>
            <button type="button" class="text-gray-400 hover:text-gray-700" :disabled="index === items.length - 1" @click="move(index, 1)">▼</button>
          </div>
          <img v-if="item.photo_url" :src="item.photo_url" class="w-12 h-12 rounded-full object-cover" />
          <div v-else class="w-12 h-12 rounded-full bg-gray-100"></div>
          <div class="flex-1">
            <p class="font-semibold">{{ item.name }}</p>
            <p class="text-sm text-gray-500">{{ item.department }} <span v-if="item.category">· {{ item.category }}</span></p>
          </div>
          <div class="flex gap-2">
            <button type="button" class="border border-blue-900 text-blue-900 px-3 py-1.5 rounded text-sm" @click="startEdit(item)">Edit</button>
            <button type="button" class="bg-red-600 text-white px-3 py-1.5 rounded text-sm" @click="requestDelete(item.id)">Delete</button>
          </div>
        </div>
      </div>

      <div v-if="!loading && items.length === 0" class="text-center py-8 text-gray-500">No staff yet.</div>
    </div>

    <ConfirmDialog
      :open="confirmState.open"
      title="Delete staff member"
      message="Are you sure you want to delete this staff member? This cannot be undone."
      confirm-label="Delete"
      danger
      @confirm="confirmDelete"
      @cancel="confirmState.open = false"
    />
  </div>
</template>
