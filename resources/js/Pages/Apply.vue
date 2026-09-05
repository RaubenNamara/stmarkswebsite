<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-100">

    <div class="max-w-[700px] md:max-w-[900px] lg:max-w-[1100px] xl:max-w-[1300px] 2xl:max-w-[1500px] mx-auto py-12 sm:py-16 px-4 sm:px-6 lg:px-8">

      <!-- Header -->
      <div class="text-center mb-10 sm:mb-12">
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-blue-900">
          Job Application Form
        </h1>

        <p class="mt-4 text-base sm:text-lg text-gray-600 max-w-2xl mx-auto">
          Complete the form below to apply for a position at
          <span class="font-semibold text-blue-900">
            St. Mark’s College Namagoma
          </span>.
          All fields are required.
        </p>
      </div>

      <!-- Form Card -->
      <div class="bg-white rounded-2xl shadow-xl border border-blue-100 
                  p-6 sm:p-8 lg:p-10 xl:p-12">

        <form @submit.prevent="submitForm" class="space-y-8">

          <!-- GRID (key improvement) -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Full Name -->
            <div>
              <label class="block text-sm font-semibold text-blue-900 mb-2">
                Full Name
              </label>
              <input
                v-model="form.fullName"
                type="text"
                class="input"
                :class="errorClass(form.errors.fullName)"
                placeholder="Enter your full name"
              />
              <p v-if="form.errors.fullName" class="error">
                {{ form.errors.fullName }}
              </p>
            </div>

            <!-- Contact -->
            <div>
              <label class="block text-sm font-semibold text-blue-900 mb-2">
                Contact Number
              </label>
              <input
                v-model="form.contact"
                type="tel"
                class="input"
                :class="errorClass(form.errors.contact)"
              />
              <p v-if="form.errors.contact" class="error">
                {{ form.errors.contact }}
              </p>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-sm font-semibold text-blue-900 mb-2">
                Email Address
              </label>
              <input
                v-model="form.email"
                type="email"
                class="input"
                :class="errorClass(form.errors.email)"
              />
              <p v-if="form.errors.email" class="error">
                {{ form.errors.email }}
              </p>
            </div>

            <!-- Position -->
            <div>
              <label class="block text-sm font-semibold text-blue-900 mb-2">
                Position Applying For
              </label>
              <input
                v-model="form.position"
                type="text"
                class="input"
                :class="errorClass(form.errors.position)"
              />
              <p v-if="form.errors.position" class="error">
                {{ form.errors.position }}
              </p>
            </div>

          </div>

          <!-- Address (full width) -->
          <div>
            <label class="block text-sm font-semibold text-blue-900 mb-2">
              Physical Address
            </label>
            <textarea
              v-model="form.address"
              rows="3"
              class="input"
              :class="errorClass(form.errors.address)"
            ></textarea>
            <p v-if="form.errors.address" class="error">
              {{ form.errors.address }}
            </p>
          </div>

          <!-- File Upload -->
          <div>
            <label class="block text-sm font-semibold text-blue-900 mb-2">
              Upload CV & Certified Academic Documents (PDF, Max 5MB)
            </label>

            <div class="relative">
              <input
                ref="fileInput"
                type="file"
                @change="handleFileUpload"
                accept=".pdf"
                class="input file:bg-blue-900 file:text-white file:px-4 file:py-2 file:rounded-md file:border-0 file:mr-4"
                :class="errorClass(form.errors.file)"
              />
            </div>

            <p v-if="form.errors.file" class="error">
              {{ form.errors.file }}
            </p>
          </div>

          <!-- Success -->
          <p v-if="successMessage"
             class="text-green-600 font-semibold text-center">
             {{ successMessage }}
          </p>

          <!-- Submit -->
          <div>
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full bg-yellow-400 text-blue-950 font-semibold py-4 rounded-lg shadow-md hover:shadow-lg hover:-translate-y-0.5 transition duration-300 disabled:opacity-50"
            >
              <span v-if="form.processing">Submitting...</span>
              <span v-else>Submit Application</span>
            </button>
          </div>

        </form>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const fileInput = ref(null)
const successMessage = ref('')

const form = useForm({
  fullName: '',
  contact: '',
  email: '',
  address: '',
  position: '',
  file: null
})

function handleFileUpload(event) {
  form.file = event.target.files[0]
}

function submitForm() {
  form.post(route('apply.store'), {
    forceFormData: true,
    onSuccess: () => {
      form.reset()
      if (fileInput.value) fileInput.value.value = ''
      successMessage.value = 'Application submitted successfully!'
      setTimeout(() => successMessage.value = '', 4000)
    }
  })
}

function errorClass(error) {
  return error ? 'border-red-500' : 'border-gray-300'
}
</script>

<script>
import MainLayout from '@/Layouts/MainLayout.vue'

export default {
  layout: MainLayout
}
</script>

<style scoped>
.input {
  width: 100%;
  padding: 12px 14px;
  border-radius: 10px;
  border: 1px solid #d1d5db;
  background: #fff;
  transition: all 0.2s ease;
}

.input:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
}

.error {
  color: #dc2626;
  font-size: 0.875rem;
  margin-top: 4px;
}
</style>