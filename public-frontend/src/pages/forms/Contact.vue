<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import { useHead } from '@unhead/vue'
import { api, apiErrorMessage, apiFieldErrors } from '../../services/api'
import { staticAsset } from '../../utils/staticAsset'

useHead({
  title: 'Contact Us',
  meta: [{ name: 'description', content: "Contact St. Mark's College Namagoma: phone numbers, email, WhatsApp, office hours, directions and a message form." }],
})

// ---- School details (the same ones shown in the site footer) -----------------------------------
const phones = [
  { label: "Headteacher's Office", numbers: ['+256 393 103 418', '+256 775 831 844', '+256 750 330 158'] },
  { label: 'Public Relations Officer', numbers: ['+256 772 996 680'] },
]
const emails = [
  { label: 'General enquiries', address: 'info@stmark.sc.ug' },
  { label: "Director's Office", address: 'director@stmark.sc.ug' },
]
const tel = (n: string) => `tel:${n.replace(/\s+/g, '')}`

const lat = 0.287574544131338
const lng = 32.47190598057264
const mapsViewUrl = `https://www.google.com/maps?q=${lat},${lng}`
const mapsEmbedUrl = `https://www.google.com/maps?q=${lat},${lng}&z=16&output=embed`
const mapsDirectionsUrl = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`

// Opening hours, Africa/Kampala time. `days` use JS getDay(): 0 = Sunday.
const hours = [
  { label: 'Monday – Friday', days: [1, 2, 3, 4, 5], open: '7:30 AM', close: '5:00 PM', from: 450, to: 1020 },
  { label: 'Saturday', days: [6], open: '9:00 AM', close: '1:00 PM', from: 540, to: 780 },
  { label: 'Sunday', days: [0], open: null, close: null, from: 0, to: 0 },
]

// "Open now" is worked out in the visitor's browser (so the prerendered page never shows a stale value).
const now = ref<{ day: number; minutes: number } | null>(null)
let timer: ReturnType<typeof setInterval> | null = null
function readClock() {
  const parts = new Intl.DateTimeFormat('en-US', { timeZone: 'Africa/Kampala', weekday: 'short', hour: 'numeric', minute: 'numeric', hour12: false }).formatToParts(new Date())
  const get = (t: string) => parts.find((p) => p.type === t)?.value ?? ''
  const day = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].indexOf(get('weekday'))
  now.value = { day, minutes: (Number(get('hour')) % 24) * 60 + Number(get('minute')) }
}
const todayRow = computed(() => (now.value ? hours.find((h) => h.days.includes(now.value!.day)) : undefined))
const isOpen = computed(() => !!(now.value && todayRow.value?.open && now.value.minutes >= todayRow.value.from && now.value.minutes < todayRow.value.to))
onMounted(() => {
  readClock()
  timer = setInterval(readClock, 60_000)
})
onBeforeUnmount(() => timer && clearInterval(timer))

// ---- Message form --------------------------------------------------------------------------------
const topics = ['General enquiry', 'Admissions', 'School fees', 'Academics', 'Other']
const form = reactive({ name: '', telephone: '', email: '', message: '', topic: topics[0] })
const errors = ref<Record<string, string>>({})
const generalError = ref<string | null>(null)
const success = ref(false)
const submitting = ref(false)
const MAX = 2000

/** Client-side checks, so obvious mistakes are explained before anything is sent. */
function validate(): boolean {
  const e: Record<string, string> = {}
  if (!form.name.trim()) e.name = 'Please tell us your name.'
  if (!form.email.trim()) e.email = 'Please enter your email address so we can reply.'
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(form.email.trim())) e.email = "That email address doesn't look right."
  if (form.message.trim().length < 10) e.message = 'Please write a little more (at least 10 characters).'
  errors.value = e
  return Object.keys(e).length === 0
}

async function submit() {
  generalError.value = null
  if (!validate()) {
    generalError.value = 'Please check the highlighted fields.'
    return
  }
  submitting.value = true
  try {
    const prefix = form.topic === topics[0] ? '' : `[${form.topic}] `
    await api.post('/contact', { name: form.name.trim(), telephone: form.telephone.trim(), email: form.email.trim(), message: prefix + form.message.trim() })
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

function sendAnother() {
  Object.assign(form, { name: '', telephone: '', email: '', message: '', topic: topics[0] })
  errors.value = {}
  success.value = false
}

const field =
  'block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-base text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:border-brand-navy focus:outline-none focus:ring-4 focus:ring-brand-navy/10'
</script>

<template>
  <!-- ===== Banner (same size and style as the other pages' banners) ===== -->
  <header
    class="relative overflow-hidden bg-brand-navy-dark bg-cover bg-center px-6 pb-24 pt-16 text-center text-white sm:pb-28 sm:pt-20"
    :style="{ backgroundImage: `url('${staticAsset('storage/images/smacon.jpg')}')` }"
  >
    <div class="pointer-events-none absolute inset-0 bg-brand-navy-dark/75" />
    <div class="pointer-events-none absolute -right-24 -top-24 h-64 w-64 rounded-full bg-brand-gold/10 blur-3xl" />
    <div class="pointer-events-none absolute -bottom-24 -left-24 h-64 w-64 rounded-full bg-white/5 blur-3xl" />

    <div class="relative mx-auto max-w-3xl">
      <div class="mx-auto mb-4 h-1 w-16 rounded-full bg-brand-gold" />
      <h1 class="font-display text-3xl font-bold tracking-tight sm:text-4xl">Get in touch</h1>
      <p class="mx-auto mt-4 max-w-2xl text-blue-100">Questions about admissions, fees or school life? Call, message or visit us - we'd love to hear from you.</p>

      <p
        v-if="now"
        class="mx-auto mt-6 inline-flex items-center gap-2.5 rounded-full px-4 py-1.5 text-sm font-semibold backdrop-blur"
        :class="isOpen ? 'bg-green-500/20 text-green-100 ring-1 ring-green-400/40' : 'bg-white/10 text-blue-100 ring-1 ring-white/20'"
        role="status"
      >
        <span class="relative flex h-2.5 w-2.5">
          <span v-if="isOpen" class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75" />
          <span class="relative inline-flex h-2.5 w-2.5 rounded-full" :class="isOpen ? 'bg-green-400' : 'bg-gray-400'" />
        </span>
        {{ isOpen ? 'The school office is open now' : 'The school office is closed right now' }}
        <span v-if="todayRow?.open" class="text-white/70">· {{ isOpen ? `until ${todayRow.close}` : `today ${todayRow.open} – ${todayRow.close}` }}</span>
      </p>
    </div>
  </header>

  <div class="container-wide pb-16 min-[1920px]:pb-24">
    <!-- ===== Four ways to reach us: every card has the same parts - icon + title, details, one action ===== -->
    <section class="relative z-10 -mt-16 grid gap-5 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ways to reach us">
      <!-- call -->
      <div class="contact-card">
        <div class="contact-head">
          <span class="contact-icon"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h2.15a1 1 0 01.99.84l.74 4.44a1 1 0 01-.54 1.06l-1.55.77a11 11 0 006.1 6.1l.77-1.55a1 1 0 011.06-.54l4.44.74a1 1 0 01.84.99V19a2 2 0 01-2 2h-1C9.72 21 3 14.28 3 6V5z" /></svg></span>
          <h2 class="contact-title">Call us</h2>
        </div>
        <div class="contact-body">
          <div v-for="g in phones" :key="g.label">
            <p class="contact-label">{{ g.label }}</p>
            <a v-for="n in g.numbers" :key="n" :href="tel(n)" class="contact-link">{{ n }}</a>
          </div>
        </div>
        <a :href="tel(phones[0].numbers[1])" class="contact-action">Call now</a>
      </div>

      <!-- email -->
      <div class="contact-card">
        <div class="contact-head">
          <span class="contact-icon"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></span>
          <h2 class="contact-title">Email us</h2>
        </div>
        <div class="contact-body">
          <div v-for="e in emails" :key="e.address">
            <p class="contact-label">{{ e.label }}</p>
            <a :href="`mailto:${e.address}`" class="contact-link break-all">{{ e.address }}</a>
          </div>
        </div>
        <a :href="`mailto:${emails[0].address}`" class="contact-action">Send an email</a>
      </div>

      <!-- whatsapp -->
      <div class="contact-card">
        <div class="contact-head">
          <span class="contact-icon !bg-[#25D366]/15 !text-[#128C7E]"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.5 14.4c-.3-.1-1.6-.8-1.9-.9-.2-.1-.4-.1-.6.1-.2.3-.7.9-.8 1-.2.2-.3.2-.5.1-.3-.1-1.2-.4-2.2-1.4-.8-.7-1.4-1.6-1.5-1.9-.2-.3 0-.4.1-.6l.4-.5c.1-.1.2-.3.2-.4.1-.2 0-.3 0-.5l-.8-1.9c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.2.3-1 1-1 2.3s1 2.7 1.1 2.9c.1.2 2 3.1 4.9 4.3.7.3 1.2.5 1.6.6.7.2 1.3.2 1.8.1.5-.1 1.6-.7 1.9-1.3.2-.6.2-1.1.2-1.3-.1-.1-.3-.2-.6-.3z" /><path d="M12 2C6.5 2 2 6.4 2 11.8c0 2 .6 3.9 1.6 5.5L2 22l4.9-1.5c1.6.9 3.3 1.3 5.1 1.3 5.5 0 10-4.4 10-9.8S17.5 2 12 2zm0 17.8c-1.6 0-3.2-.4-4.5-1.2l-.3-.2-3.3 1 1-3.1-.2-.3c-.9-1.4-1.4-3.1-1.4-4.8C3.3 6.9 7.2 3 12 3s8.7 3.9 8.7 8.8-3.9 8-8.7 8z" /></svg></span>
          <h2 class="contact-title">WhatsApp</h2>
        </div>
        <div class="contact-body">
          <div>
            <p class="contact-label">Chat with the school</p>
            <a href="https://wa.me/256775831844" target="_blank" rel="noopener noreferrer" class="contact-link">+256 775 831 844</a>
            <p class="mt-2 text-sm text-gray-500">We reply as soon as we can.</p>
          </div>
        </div>
        <a href="https://wa.me/256775831844" target="_blank" rel="noopener noreferrer" class="contact-action contact-action--whatsapp">Chat on WhatsApp</a>
      </div>

      <!-- visit -->
      <div class="contact-card">
        <div class="contact-head">
          <span class="contact-icon"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M17.66 16.66L13.41 20.9a2 2 0 01-2.82 0l-4.25-4.24a8 8 0 1111.32 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg></span>
          <h2 class="contact-title">Visit us</h2>
        </div>
        <div class="contact-body">
          <div>
            <p class="contact-label">St. Mark's College Namagoma</p>
            <p class="text-gray-700">10 Miles Kampala – Masaka Road, Namagoma</p>
            <p class="text-gray-700">P.O. Box 22888, Kampala – Uganda</p>
          </div>
        </div>
        <a :href="mapsDirectionsUrl" target="_blank" rel="noopener noreferrer" class="contact-action">Get directions</a>
      </div>
    </section>

    <!-- ===== Form + hours / map ===== -->
    <section class="mt-10 grid gap-8 xl:grid-cols-[minmax(0,1.35fr)_minmax(0,1fr)]">
      <!-- form -->
      <div class="rounded-2xl bg-white p-6 shadow-card ring-1 ring-black/5 sm:p-8">
        <div v-if="success" class="flex flex-col items-center py-12 text-center" role="status" aria-live="polite">
          <span class="success-badge flex h-20 w-20 items-center justify-center rounded-full bg-green-100 text-green-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
          </span>
          <h2 class="mt-6 text-3xl font-bold text-gray-900">Message sent!</h2>
          <p class="mx-auto mt-3 max-w-md text-lg text-gray-600">Thank you, {{ form.name.split(' ')[0] }}. We've received your message and will reply to <strong class="text-gray-800">{{ form.email }}</strong> soon.</p>
          <button type="button" class="btn mt-8" @click="sendAnother">Send another message</button>
        </div>

        <form v-else novalidate class="flex h-full flex-col" @submit.prevent="submit">
          <h2 class="text-2xl font-bold text-gray-900">Send us a message</h2>
          <p class="mt-2 text-gray-600">Fill in the form and the right office will get back to you.</p>

          <div v-if="generalError" class="mt-6 flex items-start gap-3 rounded-xl bg-red-50 px-4 py-3 text-red-800 ring-1 ring-red-600/20" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a1 1 0 00.87 1.5h18.62a1 1 0 00.87-1.5L13.71 3.86a1 1 0 00-1.72 0z" /></svg>
            {{ generalError }}
          </div>

          <fieldset class="mt-7">
            <legend class="mb-3 text-sm font-semibold text-gray-800">What is your message about?</legend>
            <div class="flex flex-wrap gap-2.5">
              <label v-for="t in topics" :key="t" class="topic-chip">
                <input v-model="form.topic" type="radio" name="topic" :value="t" class="peer sr-only">
                <span class="inline-block cursor-pointer rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition peer-checked:border-brand-navy peer-checked:bg-brand-navy peer-checked:text-white peer-focus-visible:outline peer-focus-visible:outline-4 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand-gold hover:border-brand-navy/50">{{ t }}</span>
              </label>
            </div>
          </fieldset>

          <div class="mt-7 grid flex-1 gap-6 sm:grid-cols-2 sm:grid-rows-[auto_auto_1fr]">
            <div>
              <label for="name" class="mb-2 block text-sm font-semibold text-gray-800">Your name <span class="text-red-500" aria-hidden="true">*</span></label>
              <input id="name" v-model="form.name" type="text" autocomplete="name" :class="[field, errors.name && 'border-red-400 focus:border-red-500 focus:ring-red-500/10']" placeholder="e.g. Sarah Nakato" :aria-invalid="!!errors.name" aria-describedby="name-err" @input="errors.name = ''">
              <p v-if="errors.name" id="name-err" class="mt-1.5 text-sm text-red-600">{{ errors.name }}</p>
            </div>
            <div>
              <label for="telephone" class="mb-2 block text-sm font-semibold text-gray-800">Phone <span class="font-normal text-gray-400">(optional)</span></label>
              <input id="telephone" v-model="form.telephone" type="tel" autocomplete="tel" inputmode="tel" :class="field" placeholder="+256 …">
            </div>
            <div class="sm:col-span-2">
              <label for="email" class="mb-2 block text-sm font-semibold text-gray-800">Email address <span class="text-red-500" aria-hidden="true">*</span></label>
              <input id="email" v-model="form.email" type="email" autocomplete="email" inputmode="email" :class="[field, errors.email && 'border-red-400 focus:border-red-500 focus:ring-red-500/10']" placeholder="you@example.com" :aria-invalid="!!errors.email" aria-describedby="email-err" @input="errors.email = ''">
              <p v-if="errors.email" id="email-err" class="mt-1.5 text-sm text-red-600">{{ errors.email }}</p>
            </div>
            <div class="flex flex-col sm:col-span-2">
              <div class="mb-2 flex items-end justify-between gap-3">
                <label for="message" class="block text-sm font-semibold text-gray-800">Your message <span class="text-red-500" aria-hidden="true">*</span></label>
                <span class="text-xs tabular-nums" :class="form.message.length > MAX ? 'font-bold text-red-600' : 'text-gray-400'">{{ form.message.length }} / {{ MAX }}</span>
              </div>
              <textarea id="message" v-model="form.message" rows="6" :maxlength="MAX" :class="[field, 'min-h-[10rem] flex-1 resize-y', errors.message && 'border-red-400 focus:border-red-500 focus:ring-red-500/10']" placeholder="How can we help?" :aria-invalid="!!errors.message" aria-describedby="message-err" @input="errors.message = ''" />
              <p v-if="errors.message" id="message-err" class="mt-1.5 text-sm text-red-600">{{ errors.message }}</p>
            </div>
          </div>

          <button type="submit" class="btn btn-gold mt-8 w-full gap-2 py-3.5 text-base" :disabled="submitting">
            <svg v-if="submitting" class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" /></svg>
            {{ submitting ? 'Sending…' : 'Send message' }}
            <svg v-if="!submitting" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25"><path stroke-linecap="round" stroke-linejoin="round" d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z" /></svg>
          </button>
          <p class="mt-4 text-center text-sm text-gray-500">We only use your details to reply to this message.</p>
        </form>
      </div>

      <!-- hours + map + social -->
      <div class="flex flex-col gap-8">
        <div class="rounded-2xl bg-white p-6 shadow-card ring-1 ring-black/5 sm:p-8">
          <h2 class="flex items-center gap-3 text-xl font-bold text-gray-900">
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-gold/20 text-brand-navy"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>
            Office hours
          </h2>
          <ul class="mt-5 divide-y divide-gray-100">
            <li v-for="h in hours" :key="h.label" class="flex items-center justify-between gap-4 py-3.5" :class="todayRow === h ? 'font-semibold' : ''">
              <span class="flex items-center gap-2 text-gray-800">
                {{ h.label }}
                <span v-if="todayRow === h" class="rounded-full bg-brand-gold px-2.5 py-0.5 text-xs font-bold text-brand-navy-dark">Today</span>
              </span>
              <span :class="h.open ? 'text-gray-700' : 'text-gray-400'">{{ h.open ? `${h.open} – ${h.close}` : 'Closed' }}</span>
            </li>
          </ul>
          <p class="mt-3 text-xs text-gray-400">Times are East Africa Time (Kampala).</p>
        </div>

        <div class="flex flex-1 flex-col overflow-hidden rounded-2xl bg-white shadow-card ring-1 ring-black/5">
          <iframe :src="mapsEmbedUrl" title="Map showing St. Mark's College Namagoma" class="block min-h-72 w-full flex-1" loading="lazy" referrerpolicy="no-referrer-when-downgrade" />
          <div class="flex flex-wrap items-center justify-between gap-3 p-5">
            <p class="text-sm text-gray-600">About 16 km from Kampala on Masaka Road, 1 km off the main road after Nabbingo.</p>
            <div class="flex gap-4 text-sm font-semibold">
              <a :href="mapsViewUrl" target="_blank" rel="noopener noreferrer" class="contact-link">Open map</a>
              <a :href="mapsDirectionsUrl" target="_blank" rel="noopener noreferrer" class="contact-link">Directions</a>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
/* Four equal cards: each is icon + title, details, and one action pinned to the bottom. */
.contact-card {
  display: flex;
  flex-direction: column;
  border-radius: 1.25rem;
  background: #fff;
  padding: 1.5rem;
  box-shadow: 0 1px 2px rgb(0 0 0 / 0.05), 0 12px 28px -14px rgb(23 37 84 / 0.28);
  border: 1px solid rgb(0 0 0 / 0.05);
  transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.35s cubic-bezier(0.22, 1, 0.36, 1);
}
.contact-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 2px 4px rgb(0 0 0 / 0.06), 0 22px 40px -16px rgb(23 37 84 / 0.38);
}
.contact-head {
  display: flex;
  align-items: center;
  gap: 0.85rem;
}
.contact-icon {
  display: inline-flex;
  height: 2.75rem;
  width: 2.75rem;
  flex: none;
  align-items: center;
  justify-content: center;
  border-radius: 0.85rem;
  background: rgb(23 37 84 / 0.08);
  color: #172554;
}
.contact-title {
  font-size: 1.2rem;
  font-weight: 700;
  color: #111827;
}
.contact-body {
  flex: 1;
  margin-top: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
}
.contact-label {
  margin-bottom: 0.1rem;
  font-size: 0.8125rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: #9ca3af;
}
.contact-link {
  display: block;
  font-weight: 600;
  color: #172554;
  text-decoration: none;
  transition: color 0.2s;
}
.contact-link:hover {
  color: #b45309;
  text-decoration: underline;
}
.contact-link:focus-visible,
.contact-action:focus-visible {
  outline: 3px solid #facc15;
  outline-offset: 2px;
  border-radius: 6px;
}
/* the one button at the bottom of each card (same size and position in all four) */
.contact-action {
  margin-top: 1.5rem;
  display: block;
  border-radius: 0.75rem;
  background: #172554;
  padding: 0.7rem 1rem;
  text-align: center;
  font-weight: 600;
  color: #fff !important;
  text-decoration: none;
  transition: background-color 0.2s, transform 0.2s;
}
.contact-action:hover {
  background: #0f1a3d;
  transform: translateY(-1px);
}
.contact-action--whatsapp {
  background: #25d366;
  color: #fff !important;
}
.contact-action--whatsapp:hover {
  background: #1fb857;
}

.success-badge {
  animation: pop 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}
@keyframes pop {
  from { transform: scale(0.3); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}
@media (prefers-reduced-motion: reduce) {
  .contact-card, .contact-action { transition: none; }
  .contact-card:hover, .contact-action:hover { transform: none; }
  .success-badge { animation: none; }
}
</style>
