<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
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

// Mirrors shared/src/Services/StaffService.php's groupedForDisplay()/canonicalCategory() exactly -
// real category values are inconsistent leftovers from the old app ("Administrator" vs
// "Administrators", "Head of Department" vs "Heads of Departments", etc.), so the public site
// buckets by substring match rather than exact string. Using exact match here (as this list used
// to) silently dropped members like "Wabwere Joseph" into the wrong bucket whenever their raw
// category didn't literally equal one of the four canonical labels.
const CATEGORY_ORDER = ['Administrator', 'Head of Department', 'Teaching Staff', 'Support Staff']

function canonicalCategory(category: unknown): string {
  const lower = String(category ?? '').toLowerCase()
  if (lower.includes('head')) return 'Head of Department'
  if (lower.includes('admin')) return 'Administrator'
  if (lower.includes('teach')) return 'Teaching Staff'
  return 'Support Staff'
}

// The value saved for "Non Teaching Staff" is "Support Staff", not the label itself - the phrase
// "Non Teaching Staff" contains "teach", which canonicalCategory()/PHP's matching would misread as
// Teaching Staff. "Support Staff" is also what the public site already normalizes this bucket to.
const categoryOptions = [
  { label: 'Administration', value: 'Administration' },
  { label: 'Head of Department', value: 'Head of Department' },
  { label: 'Teaching Staff', value: 'Teaching Staff' },
  { label: 'Non Teaching Staff', value: 'Support Staff' },
]
const CANONICAL_TO_OPTION: Record<string, string> = {
  Administrator: 'Administration',
  'Head of Department': 'Head of Department',
  'Teaching Staff': 'Teaching Staff',
  'Support Staff': 'Support Staff',
}

const groups = computed(() => {
  const buckets = new Map<string, SinglePhotoItem[]>()
  for (const category of CATEGORY_ORDER) buckets.set(category, [])

  for (const item of items.value) {
    buckets.get(canonicalCategory(item.category))!.push(item)
  }

  return Array.from(buckets.entries()).filter(([, members]) => members.length > 0)
})

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
  form.category = CANONICAL_TO_OPTION[canonicalCategory(item.category)]
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

// Categories are just a display grouping over one flat ordered list, so "move up/down within a
// category" swaps this item with the nearest neighbour of the SAME category in the underlying
// list - not just the adjacent index, which could belong to a different category.
async function moveWithinCategory(item: SinglePhotoItem, direction: -1 | 1) {
  const category = canonicalCategory(item.category)
  const fullIndex = items.value.findIndex((i) => i.id === item.id)

  let neighbourIndex = fullIndex + direction
  while (neighbourIndex >= 0 && neighbourIndex < items.value.length) {
    if (canonicalCategory(items.value[neighbourIndex].category) === category) break
    neighbourIndex += direction
  }
  if (neighbourIndex < 0 || neighbourIndex >= items.value.length) return

  const reordered = [...items.value]
  ;[reordered[fullIndex], reordered[neighbourIndex]] = [reordered[neighbourIndex], reordered[fullIndex]]
  items.value = reordered

  try {
    await api.post('/admin/staff-reorder', { ids: reordered.map((i) => i.id) })
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to save order'))
    await fetchAll()
  }
}

function isFirstInCategory(members: SinglePhotoItem[], item: SinglePhotoItem) {
  return members[0]?.id === item.id
}
function isLastInCategory(members: SinglePhotoItem[], item: SinglePhotoItem) {
  return members[members.length - 1]?.id === item.id
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
          <select v-model="form.category" required class="w-full border p-2 rounded">
            <option value="" disabled>Select category...</option>
            <option v-for="option in categoryOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
          </select>
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

    <div v-if="loading" class="text-center py-8 text-gray-500">Loading...</div>

    <div v-else class="space-y-10">
      <div v-for="[category, members] in groups" :key="category">
        <div class="flex items-center gap-3 mb-4">
          <h2 class="text-xl font-bold text-gray-800">{{ category }}</h2>
          <span class="text-sm text-gray-400">{{ members.length }} {{ members.length === 1 ? 'member' : 'members' }}</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
          <div v-for="item in members" :key="item.id" class="bg-white rounded-lg shadow p-4 text-center relative">
            <div class="absolute top-2 left-2 flex flex-col">
              <button type="button" class="text-gray-400 hover:text-gray-700 leading-none" :disabled="isFirstInCategory(members, item)" @click="moveWithinCategory(item, -1)">▲</button>
              <button type="button" class="text-gray-400 hover:text-gray-700 leading-none" :disabled="isLastInCategory(members, item)" @click="moveWithinCategory(item, 1)">▼</button>
            </div>

            <img v-if="item.photo_url" :src="item.photo_url as string" class="w-20 h-20 mx-auto rounded-full object-cover object-top" />
            <div v-else class="w-20 h-20 mx-auto rounded-full bg-gray-100"></div>

            <p class="mt-3 font-semibold">{{ item.name }}</p>
            <p class="text-sm text-gray-500">{{ item.department }}</p>

            <div class="mt-3 flex justify-center gap-2">
              <button type="button" class="border border-blue-900 text-blue-900 px-2.5 py-1 rounded text-xs" @click="startEdit(item)">Edit</button>
              <button type="button" class="bg-red-600 text-white px-2.5 py-1 rounded text-xs" @click="requestDelete(item.id as number)">Delete</button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="items.length === 0" class="text-center py-8 text-gray-500">No staff yet.</div>
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
