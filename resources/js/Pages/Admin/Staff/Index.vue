<script setup>
import { computed, ref, watch } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useForm, router } from '@inertiajs/vue3'

defineOptions({
  layout: AdminLayout
})

const props = defineProps({
  staff: {
    type: Array,
    default: () => []
  }
})

/* --------------------------------------------------------------------------
 | Helpers
 |-------------------------------------------------------------------------- */
function normalizeCategory(category = '') {
  const value = String(category || '').trim().toLowerCase()

  if (!value) return 'Uncategorized'

  if (value === 'administrator' || value === 'administrators') {
    return 'Administrators'
  }

  if (value === 'head of department' || value === 'heads of departments') {
    return 'Heads of Departments'
  }

  if (value === 'support staff' || value === 'non teaching staff') {
    return 'Non Teaching Staff'
  }

  if (value === 'teaching staff') {
    return 'Teaching Staff'
  }

  return String(category || '').trim()
}

function getOrderValue(person, fallbackIndex = 0) {
  const candidates = [
    person?.sort_order,
    person?.display_order,
    person?.position,
    person?.order
  ]

  for (const value of candidates) {
    const num = Number(value)
    if (Number.isFinite(num)) return num
  }

  return fallbackIndex + 1
}

function categoryPriority(category) {
  const order = [
    'Administrators',
    'Heads of Departments',
    'Teaching Staff',
    'Non Teaching Staff',
    'Uncategorized'
  ]

  const normalized = normalizeCategory(category)
  const index = order.indexOf(normalized)
  return index === -1 ? 999 : index
}

function sortStaff(list = []) {
  return [...list]
    .map((person) => ({
      ...person,
      category: normalizeCategory(person.category),
      sort_order: Number.isFinite(Number(person.sort_order))
        ? Number(person.sort_order)
        : 0
    }))
    .sort((a, b) => {
      const catA = categoryPriority(a.category)
      const catB = categoryPriority(b.category)

      if (catA !== catB) return catA - catB

      const orderA = getOrderValue(a, 0)
      const orderB = getOrderValue(b, 0)

      if (orderA !== orderB) return orderA - orderB

      return Number(a.id) - Number(b.id)
    })
}

function initials(name = '') {
  const cleaned = String(name || '').trim()
  if (!cleaned) return 'NA'

  const parts = cleaned.split(/\s+/)
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase()
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
}

/* --------------------------------------------------------------------------
 | Local staff state
 |-------------------------------------------------------------------------- */
const localStaff = ref([])

watch(
  () => props.staff,
  (newStaff) => {
    localStaff.value = sortStaff(newStaff)
  },
  { immediate: true, deep: true }
)

/* --------------------------------------------------------------------------
 | Category display
 |-------------------------------------------------------------------------- */
const selectedCategory = ref('All')

const categoriesList = computed(() => {
  const cats = new Set()

  localStaff.value.forEach((s) => {
    const cat = normalizeCategory(s.category)
    if (cat) cats.add(cat)
  })

  const ordered = Array.from(cats).sort((a, b) => categoryPriority(a) - categoryPriority(b))
  return ['All', ...ordered]
})

const categoryCounts = computed(() => {
  const counts = { All: localStaff.value.length }

  localStaff.value.forEach((s) => {
    const cat = normalizeCategory(s.category)
    counts[cat] = (counts[cat] || 0) + 1
  })

  categoriesList.value.forEach((cat) => {
    if (!(cat in counts)) counts[cat] = 0
  })

  return counts
})

const groupedStaff = computed(() => {
  const groups = {}

  localStaff.value.forEach((staff) => {
    const cat = normalizeCategory(staff.category)
    if (!groups[cat]) groups[cat] = []
    groups[cat].push(staff)
  })

  Object.keys(groups).forEach((cat) => {
    groups[cat].sort((a, b) => {
      const orderA = getOrderValue(a, 0)
      const orderB = getOrderValue(b, 0)
      if (orderA !== orderB) return orderA - orderB
      return Number(a.id) - Number(b.id)
    })
  })

  return groups
})

const visibleGroups = computed(() => {
  if (selectedCategory.value === 'All') return groupedStaff.value

  const cat = selectedCategory.value
  return groupedStaff.value[cat] ? { [cat]: groupedStaff.value[cat] } : {}
})

const hasVisibleStaff = computed(() => Object.keys(visibleGroups.value).length > 0)

/* --------------------------------------------------------------------------
 | Add Staff Form
 |-------------------------------------------------------------------------- */
const form = useForm({
  name: '',
  department: '',
  category: '',
  photo: null
})

function onFileChange(e) {
  form.photo = e.target.files?.[0] ?? null
}

function submit() {
  form.post(route('admin.staff.store'), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => form.reset()
  })
}

/* --------------------------------------------------------------------------
 | Easy category order controls
 |-------------------------------------------------------------------------- */
const savingOrder = ref(false)

function updateCategoryOrder(category) {
  const members = localStaff.value
    .filter((staff) => normalizeCategory(staff.category) === category)
    .slice()
    .sort((a, b) => {
      const orderA = getOrderValue(a, 0)
      const orderB = getOrderValue(b, 0)
      if (orderA !== orderB) return orderA - orderB
      return Number(a.id) - Number(b.id)
    })

  const orderMap = new Map()
  members.forEach((person, index) => {
    orderMap.set(person.id, index + 1)
  })

  localStaff.value = localStaff.value.map((staff) => {
    const staffCategory = normalizeCategory(staff.category)

    if (staffCategory !== category) {
      return {
        ...staff,
        category: staffCategory
      }
    }

    return {
      ...staff,
      category: staffCategory,
      sort_order: orderMap.get(staff.id) ?? staff.sort_order ?? 0
    }
  })
}

function moveWithinCategory(category, personId, direction) {
  if (savingOrder.value) return

  const members = localStaff.value
    .filter((staff) => normalizeCategory(staff.category) === category)
    .slice()
    .sort((a, b) => {
      const orderA = getOrderValue(a, 0)
      const orderB = getOrderValue(b, 0)
      if (orderA !== orderB) return orderA - orderB
      return Number(a.id) - Number(b.id)
    })

  const index = members.findIndex((person) => person.id === personId)
  if (index === -1) return

  const newIndex = direction === 'up' ? index - 1 : index + 1
  if (newIndex < 0 || newIndex >= members.length) return

  ;[members[index], members[newIndex]] = [members[newIndex], members[index]]

  const orderMap = new Map()
  members.forEach((person, i) => {
    orderMap.set(person.id, i + 1)
  })

  localStaff.value = localStaff.value.map((staff) => {
    const staffCategory = normalizeCategory(staff.category)

    if (staffCategory !== category) {
      return {
        ...staff,
        category: staffCategory
      }
    }

    return {
      ...staff,
      category: staffCategory,
      sort_order: orderMap.get(staff.id) ?? staff.sort_order ?? 0
    }
  })

  saveOrder()
}

function saveOrder() {
  if (savingOrder.value) return

  savingOrder.value = true

  const payload = localStaff.value.map((staff) => ({
    id: staff.id,
    sort_order: Number(getOrderValue(staff, 0))
  }))

  router.post(
    route('admin.staff.reorder'),
    { staff: payload },
    {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({ preserveScroll: true })
      },
      onFinish: () => {
        savingOrder.value = false
      }
    }
  )
}

/* --------------------------------------------------------------------------
 | Edit & Delete
 |-------------------------------------------------------------------------- */
const deletingId = ref(null)

function editStaff(person) {
  router.get(route('admin.staff.edit', person.id))
}

function deleteStaff(person) {
  if (!confirm(`Are you sure you want to delete "${person.name}"?`)) return

  deletingId.value = person.id

  router.delete(route('admin.staff.destroy', person.id), {
    preserveScroll: true,
    onFinish: () => {
      deletingId.value = null
    }
  })
}
</script>

<template>
  <div class="p-6 md:p-10">
    <!-- HEADER -->
    <div class="mb-8 flex items-start justify-between gap-4 flex-wrap">
      <div>
        <h2 class="text-3xl font-bold text-gray-800">
          Manage Staff
        </h2>
        <p class="text-gray-500 mt-1">
          Add staff, arrange them easily inside each category, and save the order instantly.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <button
          type="button"
          @click="saveOrder"
          class="bg-gray-900 hover:bg-gray-800 text-white px-5 py-2 rounded-xl transition shadow-sm"
          :disabled="savingOrder"
        >
          <span v-if="savingOrder">Saving order...</span>
          <span v-else>Save Order</span>
        </button>
      </div>
    </div>

    <!-- FORM -->
    <form
      @submit.prevent="submit"
      class="mb-8 bg-white p-6 md:p-8 rounded-3xl shadow-lg border border-gray-100"
    >
      <div class="grid md:grid-cols-4 gap-6 items-end">
        <div>
          <label class="block text-sm font-medium mb-2">Full Name</label>
          <input
            v-model="form.name"
            type="text"
            class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-900 focus:outline-none"
            required
          />
          <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">
            {{ form.errors.name }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2">Department</label>
          <input
            v-model="form.department"
            type="text"
            class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-900 focus:outline-none"
            required
          />
          <p v-if="form.errors.department" class="text-xs text-red-500 mt-1">
            {{ form.errors.department }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2">Category</label>
          <select
            v-model="form.category"
            class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-900 focus:outline-none"
            required
          >
            <option value="">Select Category</option>
            <option value="Administrators">Administrators</option>
            <option value="Heads of Departments">Heads of Departments</option>
            <option value="Teaching Staff">Teaching Staff</option>
            <option value="Non Teaching Staff">Non Teaching Staff</option>
          </select>
          <p v-if="form.errors.category" class="text-xs text-red-500 mt-1">
            {{ form.errors.category }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2">Photo</label>
          <input type="file" @change="onFileChange" accept="image/*" />
          <p v-if="form.errors.photo" class="text-xs text-red-500 mt-1">
            {{ form.errors.photo }}
          </p>
        </div>
      </div>

      <div class="mt-6">
        <button
          type="submit"
          class="bg-blue-900 hover:bg-blue-800 text-white px-6 py-3 rounded-xl transition shadow-sm"
          :disabled="form.processing"
        >
          <span v-if="form.processing">Adding...</span>
          <span v-else>Add Staff</span>
        </button>
      </div>
    </form>

    <!-- FILTER TABS -->
    <div class="mb-8">
      <div class="flex flex-wrap gap-3 items-center">
        <button
          v-for="cat in categoriesList"
          :key="cat"
          @click="selectedCategory = cat"
          :class="[
            'px-4 py-2 rounded-full text-sm font-medium transition shadow-sm',
            selectedCategory === cat
              ? 'bg-blue-900 text-white'
              : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'
          ]"
        >
          <span class="mr-2">{{ cat }}</span>
          <span class="text-xs inline-block bg-white/10 px-2 py-0.5 rounded-full">
            {{ categoryCounts[cat] ?? 0 }}
          </span>
        </button>

        <button
          v-if="selectedCategory !== 'All'"
          @click="selectedCategory = 'All'"
          class="ml-2 text-sm text-gray-500 hover:underline"
        >
          Clear filter
        </button>
      </div>
    </div>

    <!-- GROUPED STAFF -->
    <div v-if="hasVisibleStaff">
      <div
        v-for="(members, category) in visibleGroups"
        :key="category"
        class="mb-14"
      >
        <div class="mb-6 flex items-end justify-between gap-4 flex-wrap">
          <div>
            <h3 class="text-2xl md:text-3xl font-bold text-gray-800">
              {{ category }}
            </h3>
            <p class="text-sm text-gray-500 mt-1">
              Use the buttons to move staff up or down inside this category.
            </p>
          </div>
        </div>

        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="person in members"
            :key="person.id"
            class="group bg-white rounded-3xl shadow-md hover:shadow-2xl transition border border-gray-100 overflow-hidden"
          >
            <div class="p-6">
              <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-blue-100 text-blue-800">
                  Order #{{ getOrderValue(person, 0) }}
                </span>

                <div class="flex gap-2">
                  <button
                    type="button"
                    @click="moveWithinCategory(category, person.id, 'up')"
                    class="w-9 h-9 rounded-full border border-gray-200 hover:border-blue-900 hover:text-blue-900 transition flex items-center justify-center"
                    title="Move up"
                  >
                    ↑
                  </button>
                  <button
                    type="button"
                    @click="moveWithinCategory(category, person.id, 'down')"
                    class="w-9 h-9 rounded-full border border-gray-200 hover:border-blue-900 hover:text-blue-900 transition flex items-center justify-center"
                    title="Move down"
                  >
                    ↓
                  </button>
                </div>
              </div>

              <div class="mb-5 flex justify-center">
                <img
                  v-if="person.photo"
                  :src="`/storage/${person.photo}`"
                  class="w-28 h-28 rounded-full object-cover ring-4 ring-blue-50"
                  :alt="person.name"
                />
                <div
                  v-else
                  class="w-28 h-28 rounded-full bg-gradient-to-br from-blue-100 to-yellow-100 flex items-center justify-center text-blue-900 font-extrabold text-2xl ring-4 ring-blue-50"
                >
                  {{ initials(person.name) }}
                </div>
              </div>

              <h3 class="text-lg font-bold text-gray-800 text-center">
                {{ person.name }}
              </h3>

              <p class="text-sm text-gray-500 text-center mt-1">
                {{ person.department }}
              </p>

              <div class="mt-4 flex justify-center">
                <span class="px-3 py-1 text-xs rounded-full bg-blue-100 text-blue-800 font-medium">
                  {{ normalizeCategory(person.category) }}
                </span>
              </div>

              <div class="mt-5 flex justify-center gap-3">
                <button
                  @click.prevent="editStaff(person)"
                  class="px-4 py-2 rounded-xl border border-blue-800 text-blue-800 text-sm hover:bg-blue-50 transition"
                >
                  Edit
                </button>

                <button
                  @click.prevent="deleteStaff(person)"
                  class="px-4 py-2 rounded-xl bg-red-600 text-white text-sm hover:bg-red-700 transition"
                  :disabled="deletingId === person.id"
                >
                  <span v-if="deletingId === person.id">Deleting…</span>
                  <span v-else>Delete</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- EMPTY STATE -->
    <div v-else class="mt-10 text-center text-gray-500">
      No staff members found in <strong>{{ selectedCategory }}</strong>.
    </div>
  </div>
</template>

<style scoped>
button[disabled] {
  cursor: not-allowed;
  opacity: 0.6;
}
</style>