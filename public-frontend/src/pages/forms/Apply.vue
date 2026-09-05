<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useHead } from '@unhead/vue'
import { api, apiErrorMessage, apiFieldErrors } from '../../services/api'
import PageHeader from '../../components/PageHeader.vue'

useHead({ title: 'Apply - Careers' })

const form = reactive({ full_name: '', contact: '', email: '', address: '', position: '' })
const cvFile = ref<File | null>(null)
const errors = ref<Record<string, string>>({})
const generalError = ref<string | null>(null)
const success = ref(false)
const submitting = ref(false)

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
  />

  <section class="mx-auto max-w-xl px-6 py-14">
    <div class="card">
      <div v-if="success" class="alert alert-success">Thank you — your application has been received. We'll be in touch if there's a match.</div>
      <form v-else @submit.prevent="submit">
        <div v-if="generalError" class="alert alert-error mb-6">{{ generalError }}</div>

        <label for="full_name" class="field-label">Full Name</label>
        <input id="full_name" v-model="form.full_name" type="text" required class="field-input">
        <p v-if="errors.full_name" class="field-error">{{ errors.full_name }}</p>

        <label for="contact" class="field-label">Contact (phone)</label>
        <input id="contact" v-model="form.contact" type="text" required class="field-input">

        <label for="email" class="field-label">Email</label>
        <input id="email" v-model="form.email" type="email" required class="field-input">

        <label for="address" class="field-label">Address</label>
        <textarea id="address" v-model="form.address" rows="3" required class="field-input" />

        <label for="position" class="field-label">Position Applying For</label>
        <input id="position" v-model="form.position" type="text" required class="field-input">

        <label for="cv" class="field-label">CV (PDF, max 5MB)</label>
        <input id="cv" type="file" accept="application/pdf" required class="field-input" @change="onFileChange">
        <p v-if="errors.file" class="field-error -mt-3">{{ errors.file }}</p>

        <button type="submit" class="btn btn-gold w-full" :disabled="submitting">{{ submitting ? 'Submitting…' : 'Submit Application' }}</button>
      </form>
    </div>
  </section>
</template>
