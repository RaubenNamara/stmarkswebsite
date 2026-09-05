<script setup>
import { useForm } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { ref } from 'vue'

defineOptions({
  layout: MainLayout
})

const props = defineProps({
  pageTitle: { type: String, default: 'Contact Us' }
})

const successMessage = ref(false)

const form = useForm({
  name: '',
  telephone: '',
  email: '',
  message: ''
})

function submit() {
  form.post(route('contact.store'), {
    onSuccess: () => {
      form.reset()
      successMessage.value = true

      setTimeout(() => {
        successMessage.value = false
      }, 4000)
    }
  })
}

function errorClass(error) {
  return error ? 'border-red-500' : 'border-gray-300'
}
</script>

<template>
  <div class="min-h-screen bg-slate-50">

    <!-- ================= CONTAINER ================= -->
    <section class="max-w-[1000px] md:max-w-[1200px] lg:max-w-[1400px] xl:max-w-[1600px] 2xl:max-w-[1800px] mx-auto py-12 sm:py-16 px-4 sm:px-6 lg:px-8">

      <!-- ================= HEADER ================= -->
      <header class="text-center mb-12">
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900">
          {{ pageTitle }}
        </h1>

        <p class="mt-3 text-gray-600 max-w-2xl mx-auto">
          We’d love to hear from you. Reach out to us through the details below or send us a message directly.
        </p>
      </header>

      <!-- ================= GRID ================= -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">

        <!-- ================= CONTACT INFO ================= -->
        <section class="lg:col-span-5 xl:col-span-4 bg-white p-6 sm:p-8 rounded-2xl shadow-lg border">

          <h2 class="text-xl sm:text-2xl font-semibold mb-6 text-indigo-900">
            Get in Touch
          </h2>

          <div class="space-y-5 text-gray-700">

            <div>
              <p class="font-semibold text-gray-900">Email</p>
              <p class="text-sm sm:text-base">info@stmark.sc.ug</p>
            </div>

            <div>
              <p class="font-semibold text-gray-900">Phone</p>
              <p class="text-sm sm:text-base">+256-775-831844</p>
            </div>

            <div>
              <p class="font-semibold text-gray-900">Address</p>
              <p class="text-sm sm:text-base">Namagoma, Uganda</p>
            </div>

          </div>

        </section>

        <!-- ================= FORM ================= -->
        <section class="lg:col-span-7 xl:col-span-8 bg-white p-6 sm:p-8 lg:p-10 rounded-2xl shadow-lg border">

          <h2 class="text-xl sm:text-2xl font-semibold mb-6 text-indigo-900">
            Send Us a Message
          </h2>

          <!-- SUCCESS -->
          <div
            v-if="successMessage"
            class="mb-4 p-3 rounded-lg bg-green-100 text-green-800 border border-green-300"
          >
            ✅ Message sent successfully!
          </div>

          <form @submit.prevent="submit" class="space-y-6">

            <!-- NAME -->
            <div>
              <label class="block text-sm font-semibold mb-2 text-gray-800">
                Full Name
              </label>

              <input
                v-model="form.name"
                type="text"
                class="input"
                :class="errorClass(form.errors.name)"
                placeholder="Enter your full name"
              />

              <p v-if="form.errors.name" class="error">
                {{ form.errors.name }}
              </p>
            </div>

            <!-- TELEPHONE -->
            <div>
              <label class="block text-sm font-semibold mb-2 text-gray-800">
                Telephone
              </label>

              <input
                v-model="form.telephone"
                type="tel"
                class="input"
                :class="errorClass(form.errors.telephone)"
                placeholder="Enter your telephone number"
              />

              <p v-if="form.errors.telephone" class="error">
                {{ form.errors.telephone }}
              </p>
            </div>

            <!-- EMAIL -->
            <div>
              <label class="block text-sm font-semibold mb-2 text-gray-800">
                Email Address
              </label>

              <input
                v-model="form.email"
                type="email"
                class="input"
                :class="errorClass(form.errors.email)"
                placeholder="Enter your email"
              />

              <p v-if="form.errors.email" class="error">
                {{ form.errors.email }}
              </p>
            </div>

            <!-- MESSAGE -->
            <div>
              <label class="block text-sm font-semibold mb-2 text-gray-800">
                Message
              </label>

              <textarea
                v-model="form.message"
                rows="5"
                class="input"
                :class="errorClass(form.errors.message)"
                placeholder="Write your message..."
              ></textarea>

              <p v-if="form.errors.message" class="error">
                {{ form.errors.message }}
              </p>
            </div>

            <!-- BUTTON -->
            <div>
              <button
                type="submit"
                :disabled="form.processing"
                class="w-full bg-indigo-600 text-white font-semibold py-3 rounded-lg shadow-md hover:bg-indigo-700 hover:shadow-lg transition disabled:opacity-50"
              >
                <span v-if="form.processing">Sending...</span>
                <span v-else>Send Message</span>
              </button>
            </div>

          </form>

        </section>

      </div>

    </section>

  </div>
</template>

<style scoped>
.input {
  width: 100%;
  padding: 12px 14px;
  border-radius: 10px;
  border: 1px solid #d1d5db;
  transition: all 0.2s ease;
}

.input:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
}

.error {
  color: #dc2626;
  font-size: 0.875rem;
  margin-top: 4px;
}
</style>