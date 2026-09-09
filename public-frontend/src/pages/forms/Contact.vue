<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useHead } from '@unhead/vue'
import { api, apiErrorMessage, apiFieldErrors } from '../../services/api'
import PageHeader from '../../components/PageHeader.vue'
import { staticAsset } from '../../utils/staticAsset'

useHead({ title: 'Contact Us' })

const form = reactive({ name: '', telephone: '', email: '', message: '' })
const errors = ref<Record<string, string>>({})
const generalError = ref<string | null>(null)
const success = ref(false)
const submitting = ref(false)

async function submit() {
  submitting.value = true
  errors.value = {}
  generalError.value = null
  try {
    await api.post('/contact', form)
    success.value = true
  } catch (e) {
    const fieldErrors = apiFieldErrors(e)
    if (Object.keys(fieldErrors).length) {
      errors.value = fieldErrors
      generalError.value = 'Please correct the errors below.'
    } else {
      generalError.value = apiErrorMessage(e)
    }
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <PageHeader title="Contact Us" subtitle="We'd love to hear from you — reach out with any questions, or send us a message below." :bg-image="staticAsset('storage/images/smacon.jpg')" />

  <section class="container-wide py-14">
    <div class="mx-auto grid max-w-4xl overflow-hidden rounded-3xl shadow-card ring-1 ring-black/5 lg:grid-cols-5">
      <!-- Contacts -->
      <div class="relative overflow-hidden bg-gradient-to-br from-brand-navy to-brand-navy-dark p-8 text-white sm:p-10 lg:col-span-2">
        <div class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-brand-gold/10 blur-3xl" />
        <div class="pointer-events-none absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-white/5 blur-3xl" />
        <div class="relative">
          <span class="eyebrow text-brand-gold">Get in Touch</span>
          <h2 class="mt-2 font-display text-xl font-bold sm:text-2xl">We're here to help</h2>

          <div class="mt-8 space-y-5">
            <div class="flex items-start gap-3">
              <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-brand-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
              </span>
              <div>
                <p class="text-xs font-bold uppercase tracking-wide text-blue-200/60">Call us</p>
                <a href="tel:+256393103418" class="mt-1 block text-sm text-blue-100 transition hover:text-white">+256 393 103 418</a>
                <a href="tel:+256775831844" class="block text-sm text-blue-100 transition hover:text-white">+256 775 831 844</a>
              </div>
            </div>

            <div class="flex items-start gap-3">
              <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-brand-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
              </span>
              <div>
                <p class="text-xs font-bold uppercase tracking-wide text-blue-200/60">Email us</p>
                <a href="mailto:director@stmark.sc.ug" class="mt-1 block text-sm text-blue-100 transition hover:text-white">director@stmark.sc.ug</a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Form panel -->
      <div class="bg-white p-8 sm:p-10 lg:col-span-3">
        <div v-if="success" class="flex flex-col items-center py-10 text-center">
          <span class="flex h-14 w-14 items-center justify-center rounded-full bg-green-100 text-green-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
          </span>
          <h3 class="mt-4 font-display text-lg font-bold text-gray-900">Message sent</h3>
          <p class="mt-2 max-w-sm text-sm text-gray-600">Thank you — your message has been sent. We'll get back to you soon.</p>
        </div>

        <form v-else @submit.prevent="submit">
          <h2 class="font-display text-lg font-bold text-gray-900">Send us a message</h2>
          <div v-if="generalError" class="alert alert-error mt-4">{{ generalError }}</div>

          <div class="mt-6 grid gap-5 sm:grid-cols-2">
            <div>
              <label for="name" class="field-label mb-1.5">Name</label>
              <input id="name" v-model="form.name" type="text" required class="field-input mb-0">
              <p v-if="errors.name" class="field-error mb-0 mt-1.5">{{ errors.name }}</p>
            </div>

            <div>
              <label for="telephone" class="field-label mb-1.5">Telephone</label>
              <input id="telephone" v-model="form.telephone" type="tel" class="field-input mb-0">
            </div>

            <div class="sm:col-span-2">
              <label for="email" class="field-label mb-1.5">Email</label>
              <input id="email" v-model="form.email" type="email" required class="field-input mb-0">
              <p v-if="errors.email" class="field-error mb-0 mt-1.5">{{ errors.email }}</p>
            </div>

            <div class="sm:col-span-2">
              <label for="message" class="field-label mb-1.5">Message</label>
              <textarea id="message" v-model="form.message" rows="5" required class="field-input mb-0" />
              <p v-if="errors.message" class="field-error mb-0 mt-1.5">{{ errors.message }}</p>
            </div>
          </div>

          <button type="submit" class="btn btn-gold mt-8 w-full" :disabled="submitting">{{ submitting ? 'Sending…' : 'Send Message' }}</button>
        </form>
      </div>
    </div>
  </section>
</template>
