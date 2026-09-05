<script setup>
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  achievers: {
    type: Array,
    default: () => []
  }
})

const editingId = ref(null)

const form = ref({
  name: '',
  year: '',
  exam: '',
  division: '',
  description: '',
  photo: null
})

function submit() {
  const formData = new FormData()

  formData.append('name', form.value.name)
  formData.append('year', form.value.year)
  formData.append('exam', form.value.exam)
  formData.append('division', form.value.division ?? '')
  formData.append('description', form.value.description ?? '')

  if (form.value.photo) {
    formData.append('photo', form.value.photo)
  }

  if (editingId.value) {
    formData.append('_method', 'PUT')

    router.post(
      route('admin.high-achievers.update', editingId.value),
      formData,
      {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
          resetForm()
        }
      }
    )
  } else {
    router.post(
      route('admin.high-achievers.store'),
      formData,
      {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
          resetForm()
        }
      }
    )
  }
}

function edit(achiever) {
  editingId.value = achiever.id

  form.value.name = achiever.name
  form.value.year = achiever.year
  form.value.exam = achiever.exam
  form.value.division = achiever.division
  form.value.description = achiever.description
  form.value.photo = null
}

function deleteAchiever(id) {
  if (confirm('Are you sure you want to delete this achiever?')) {
    router.delete(route('admin.high-achievers.destroy', id), {
      preserveScroll: true
    })
  }
}

function resetForm() {
  editingId.value = null

  form.value = {
    name: '',
    year: '',
    exam: '',
    division: '',
    description: '',
    photo: null
  }

  const fileInput = document.querySelector('input[type="file"]')
  if (fileInput) fileInput.value = ''
}
</script>

<template>
  <div class="max-w-6xl mx-auto p-10">
    <h1 class="text-3xl font-bold mb-8">
      High Achievers Management
    </h1>

    <div class="bg-white shadow rounded-2xl p-8 mb-12">
      <div class="grid md:grid-cols-2 gap-6">
        <input
          v-model="form.name"
          placeholder="Student Name"
          class="border p-3 rounded-lg"
        />

        <input
          v-model="form.year"
          placeholder="Year"
          class="border p-3 rounded-lg"
        />

        <input
          v-model="form.exam"
          placeholder="Exam (UACE / UCE)"
          class="border p-3 rounded-lg"
        />

        <input
          v-model="form.division"
          placeholder="Division / Aggregates"
          class="border p-3 rounded-lg"
        />
      </div>

      <textarea
        v-model="form.description"
        placeholder="Short Description"
        class="border p-3 rounded-lg w-full mt-6"
      />

      <input
        type="file"
        @change="e => form.photo = e.target.files[0]"
        class="border p-3 rounded-lg w-full mt-6"
        accept="image/*"
      />

      <div class="mt-6 flex gap-4">
        <button
          @click="submit"
          class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-semibold"
        >
          {{ editingId ? 'Update Achiever' : 'Add Achiever' }}
        </button>

        <button
          v-if="editingId"
          @click="resetForm"
          class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-xl"
        >
          Cancel
        </button>
      </div>
    </div>

    <div v-if="achievers.length" class="grid md:grid-cols-3 gap-8">
      <div
        v-for="achiever in achievers"
        :key="achiever.id"
        class="bg-white shadow rounded-2xl p-6"
      >
        <img
          v-if="achiever.photo_url"
          :src="achiever.photo_url"
          class="w-full h-56 object-cover rounded-xl mb-4"
          alt="High achiever photo"
        />

        <h2 class="font-bold text-lg">
          {{ achiever.name }}
        </h2>

        <p class="text-sm text-gray-500">
          {{ achiever.exam }} - {{ achiever.year }}
        </p>

        <p class="text-sm font-semibold text-indigo-700">
          {{ achiever.division }}
        </p>

        <p class="text-sm text-gray-600 mt-2">
          {{ achiever.description }}
        </p>

        <div class="flex justify-between mt-4">
          <button
            @click="edit(achiever)"
            class="text-blue-600 font-semibold"
          >
            Edit
          </button>

          <button
            @click="deleteAchiever(achiever.id)"
            class="text-red-600 font-semibold"
          >
            Delete
          </button>
        </div>
      </div>
    </div>

    <div v-else class="text-gray-500">
      No high achievers added yet.
    </div>
  </div>
</template>