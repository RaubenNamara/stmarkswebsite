<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useHead } from '@unhead/vue'
import { api, apiErrorMessage, apiFieldErrors } from '../../services/api'
import PageHeader from '../../components/PageHeader.vue'

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
  <PageHeader title="Contact Us" />

  <section class="mx-auto max-w-xl px-6 py-14">
    <div class="card">
      <div v-if="success" class="alert alert-success">Thank you — your message has been sent. We'll get back to you soon.</div>
      <form v-else @submit.prevent="submit">
        <div v-if="generalError" class="alert alert-error mb-6">{{ generalError }}</div>

        <label for="name" class="field-label">Name</label>
        <input id="name" v-model="form.name" type="text" required class="field-input">
        <p v-if="errors.name" class="field-error">{{ errors.name }}</p>

        <label for="telephone" class="field-label">Telephone</label>
        <input id="telephone" v-model="form.telephone" type="tel" class="field-input">

        <label for="email" class="field-label">Email</label>
        <input id="email" v-model="form.email" type="email" required class="field-input">
        <p v-if="errors.email" class="field-error">{{ errors.email }}</p>

        <label for="message" class="field-label">Message</label>
        <textarea id="message" v-model="form.message" rows="5" required class="field-input" />
        <p v-if="errors.message" class="field-error">{{ errors.message }}</p>

        <button type="submit" class="btn w-full" :disabled="submitting">{{ submitting ? 'Sending…' : 'Send Message' }}</button>
      </form>
    </div>
  </section>
</template>
