<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useHead } from '@unhead/vue'
import { api, apiErrorMessage, apiFieldErrors } from '../../services/api'
import PageHeader from '../../components/PageHeader.vue'
import { staticAsset } from '../../utils/staticAsset'

useHead({ title: 'Apply - Careers' })

const form = reactive({ full_name: '', contact: '', email: '', address: '', position: '' })
const cvFile = ref<File | null>(null)
const errors = ref<Record<string, string>>({})
const generalError = ref<string | null>(null)
const success = ref(false)
const submitting = ref(false)

const checklist = [
  'A recent CV in PDF format (max 5MB)',
  'A working phone number and email address',
  'The position you\'d like to apply for',
]

function onFileChange(e: Event) {
  cvFile.value = (e.target as HTMLInputElement).files?.[0] ?? null
}

async function submit() {
  submitting.value = true
  errors.value = {}
  generalError.value = null
  try {
    const body = new FormData()
    for (const [key, value] of Object.entries(form)) body.append(key, value)
    if (cvFile.value) body.append('cv', cvFile.value)

    await api.post('/apply', body)
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
  <PageHeader
    title="Apply — Careers"
    subtitle="Join our team and help shape the future with St Mark's College."
    :bg-image="staticAsset('storage/images/smacon.jpg')"
  />

  <section class="container-wide py-14">
    <div class="mx-auto grid max-w-4xl overflow-hidden rounded-3xl shadow-card ring-1 ring-black/5 lg:grid-cols-5">
      <!-- Info panel -->
      <div class="relative overflow-hidden bg-gradient-to-br from-brand-navy to-brand-navy-dark p-8 text-white sm:p-10 lg:col-span-2">
        <div class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-brand-gold/10 blur-3xl" />
        <div class="pointer-events-none absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-white/5 blur-3xl" />
        <div class="relative">
          <span class="eyebrow text-brand-gold">Before you start</span>
          <h2 class="mt-2 font-display text-xl font-bold sm:text-2xl">What you'll need</h2>
          <ul class="mt-6 space-y-4">
            <li v-for="item in checklist" :key="item" class="flex items-start gap-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 shrink-0 text-brand-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
              <span class="text-sm text-blue-100">{{ item }}</span>
            </li>
          </ul>

          <div class="mt-10 border-t border-white/10 pt-6">
            <p class="text-xs font-bold uppercase tracking-wide text-blue-200/60">Need help applying?</p>
            <a href="mailto:director@stmark.sc.ug" class="mt-2 block text-sm text-blue-100 transition hover:text-white">director@stmark.sc.ug</a>
            <a href="tel:+256775831844" class="mt-1 block text-sm text-blue-100 transition hover:text-white">+256 775 831 844</a>
          </div>
        </div>
      </div>

      <!-- Form panel -->
      <div class="bg-white p-8 sm:p-10 lg:col-span-3">
        <div v-if="success" class="flex flex-col items-center py-10 text-center">
          <span class="flex h-14 w-14 items-center justify-center rounded-full bg-green-100 text-green-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
          </span>
          <h3 class="mt-4 font-display text-lg font-bold text-gray-900">Application received</h3>
          <p class="mt-2 max-w-sm text-sm text-gray-600">Thank you for applying — our team will review your details and reach out if there's a match.</p>
          <router-link to="/explore/career" class="btn btn-gold mt-6">Back to Careers</router-link>
        </div>

        <form v-else @submit.prevent="submit">
          <h2 class="font-display text-lg font-bold text-gray-900">Application details</h2>
          <div v-if="generalError" class="alert alert-error mt-4">{{ generalError }}</div>

          <div class="mt-6 grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
              <label for="full_name" class="field-label mb-1.5">Full Name</label>
              <input id="full_name" v-model="form.full_name" type="text" required class="field-input mb-0">
              <p v-if="errors.full_name" class="field-error mb-0 mt-1.5">{{ errors.full_name }}</p>
            </div>

            <div>
              <label for="contact" class="field-label mb-1.5">Contact (phone)</label>
              <input id="contact" v-model="form.contact" type="text" required class="field-input mb-0">
              <p v-if="errors.contact" class="field-error mb-0 mt-1.5">{{ errors.contact }}</p>
            </div>

            <div>
              <label for="email" class="field-label mb-1.5">Email</label>
              <input id="email" v-model="form.email" type="email" required class="field-input mb-0">
              <p v-if="errors.email" class="field-error mb-0 mt-1.5">{{ errors.email }}</p>
            </div>

            <div class="sm:col-span-2">
              <label for="address" class="field-label mb-1.5">Address</label>
              <textarea id="address" v-model="form.address" rows="3" required class="field-input mb-0" />
              <p v-if="errors.address" class="field-error mb-0 mt-1.5">{{ errors.address }}</p>
            </div>

            <div class="sm:col-span-2">
              <label for="position" class="field-label mb-1.5">Position Applying For</label>
              <input id="position" v-model="form.position" type="text" required class="field-input mb-0">
              <p v-if="errors.position" class="field-error mb-0 mt-1.5">{{ errors.position }}</p>
            </div>

            <div class="sm:col-span-2">
              <label for="cv" class="field-label mb-1.5">CV (PDF, max 5MB)</label>
              <label for="cv" class="flex cursor-pointer items-center gap-3 rounded-lg border border-dashed border-gray-300 px-4 py-4 text-sm text-gray-500 transition hover:border-brand-navy/40 hover:bg-brand-navy/5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-brand-navy/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L7 9m5-5l5 5M4 16v3a2 2 0 002 2h12a2 2 0 002-2v-3" /></svg>
                <span>{{ cvFile ? cvFile.name : 'Click to choose a PDF file' }}</span>
              </label>
              <input id="cv" type="file" accept="application/pdf" required class="hidden" @change="onFileChange">
              <p v-if="errors.file" class="field-error mb-0 mt-1.5">{{ errors.file }}</p>
            </div>
          </div>

          <button type="submit" class="btn btn-gold mt-8 w-full" :disabled="submitting">{{ submitting ? 'Submitting…' : 'Submit Application' }}</button>
        </form>
      </div>
    </div>
  </section>
</template>
