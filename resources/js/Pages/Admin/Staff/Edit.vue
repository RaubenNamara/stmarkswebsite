<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useForm } from '@inertiajs/vue3'

defineOptions({
  layout: AdminLayout
})

const props = defineProps({
  staff: Object
})

/*
|--------------------------------------------------------------------------
| Normalize category (important)
|--------------------------------------------------------------------------
*/
function normalizeCategory(category = '') {
  const value = String(category || '').trim().toLowerCase()

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

  return category
}

/*
|--------------------------------------------------------------------------
| Form Data
|--------------------------------------------------------------------------
*/
const form = useForm({
  name: props.staff.name ?? '',
  department: props.staff.department ?? '',
  category: normalizeCategory(props.staff.category), // ✅ FIXED
  photo: null,
  _method: 'put'
})

function submit() {
  form.post(route('admin.staff.update', props.staff.id), {
    forceFormData: true,
    preserveScroll: true
  })
}
</script>

<template>
  <div class="p-10 max-w-xl">

    <h2 class="text-2xl font-bold mb-8 text-gray-800">
      Edit Staff
    </h2>

    <form
      @submit.prevent="submit"
      enctype="multipart/form-data"
      class="space-y-6 bg-white p-8 rounded-2xl shadow-md border"
    >

      <!-- NAME -->
      <div>
        <label class="block text-sm font-medium mb-2">
          Full Name
        </label>
        <input
          v-model="form.name"
          type="text"
          class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-900 focus:outline-none"
          required
        />
        <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">
          {{ form.errors.name }}
        </p>
      </div>

      <!-- DEPARTMENT -->
      <div>
        <label class="block text-sm font-medium mb-2">
          Department
        </label>
        <input
          v-model="form.department"
          type="text"
          class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-900 focus:outline-none"
          required
        />
        <p v-if="form.errors.department" class="text-red-500 text-sm mt-1">
          {{ form.errors.department }}
        </p>
      </div>

      <!-- CATEGORY -->
      <div>
        <label class="block text-sm font-medium mb-2">
          Category
        </label>
        <select
          v-model="form.category"
          class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-900 focus:outline-none"
          required
        >
          <option value="">Select Category</option>
          <option value="Administrators">Administrators</option>
          <option value="Heads of Departments">Heads of Departments</option>
          <option value="Teaching Staff">Teaching Staff</option>
          <option value="Non Teaching Staff">Non Teaching Staff</option>
        </select>

        <p v-if="form.errors.category" class="text-red-500 text-sm mt-1">
          {{ form.errors.category }}
        </p>
      </div>

      <!-- CURRENT PHOTO -->
      <div v-if="props.staff.photo">
        <label class="block text-sm font-medium mb-2">
          Current Photo
        </label>
        <img
          :src="`/storage/${props.staff.photo}`"
          class="w-28 h-28 rounded-full object-cover"
          :alt="props.staff.name"
        />
      </div>

      <!-- CHANGE PHOTO -->
      <div>
        <label class="block text-sm font-medium mb-2">
          Change Photo (optional)
        </label>
        <input
          type="file"
          accept="image/*"
          @change="e => form.photo = e.target.files?.[0] ?? null"
        />
        <p v-if="form.errors.photo" class="text-red-500 text-sm mt-1">
          {{ form.errors.photo }}
        </p>
      </div>

      <!-- BUTTON -->
      <div>
        <button
          type="submit"
          :disabled="form.processing"
          class="bg-blue-900 hover:bg-blue-800 text-white px-6 py-2 rounded-lg transition disabled:opacity-60"
        >
          <span v-if="form.processing">Updating...</span>
          <span v-else>Update Staff</span>
        </button>
      </div>

    </form>

  </div>
</template>