<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useHead } from '@unhead/vue'
import { api, apiErrorMessage, apiFieldErrors } from '../../services/api'
import PageHeader from '../../components/PageHeader.vue'
import { staticAsset } from '../../utils/staticAsset'

useHead({ title: 'SMOSA Feedback' })

const ratingFields: Array<[string, string]> = [
  ['rating_event_organization', 'Event Organization'],
  ['rating_communication', 'Communication'],
  ['rating_venue_setup', 'Venue Setup'],
  ['rating_programme_activities', 'Programme & Activities'],
  ['rating_food_refreshments', 'Food & Refreshments'],
  ['rating_entertainment', 'Entertainment'],
  ['rating_guest_experience', 'Guest Experience'],
  ['rating_time_management', 'Time Management'],
  ['rating_photography_video', 'Photography & Video (optional)'],
]
const scale = ['Excellent', 'Very Good', 'Good', 'Fair', 'Poor']
const activityOptions = ['Career & Business Opportunities', 'Community/Charity Activities', 'Professional Development', 'Networking', 'Social Gatherings']
const participationOptions = ['Definitely', 'Probably', 'Not sure', 'Unlikely']

const { data: statusData } = await api.get('/smosa-feedback/status')
const alreadySubmitted = ref<boolean>(statusData.data.already_submitted)

const form = reactive<Record<string, any>>({
  overall_experience: '',
  activities_interest: [] as string[],
  best_part: '',
  improvements: '',
  future_suggestions: '',
  future_participation: '',
  other_comments: '',
})
for (const [field] of ratingFields) form[field] = ''

const errors = ref<Record<string, string>>({})
const generalError = ref<string | null>(null)
const success = ref(false)
const submitting = ref(false)

async function submit() {
  submitting.value = true
  errors.value = {}
  generalError.value = null
  try {
    await api.post('/smosa-feedback', form)
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
  <PageHeader title="SMOSA Feedback" subtitle="Help us improve future SMOSA events by sharing your experience." :bg-image="staticAsset('storage/images/smacon.jpg')" />

  <section class="container-wide py-14">
    <div class="mx-auto max-w-2xl 2xl:max-w-6xl">
      <div v-if="success" class="alert alert-success">Thank you for your feedback!</div>
      <div v-else-if="alreadySubmitted" class="alert alert-success">You've already submitted feedback for this event. Thank you!</div>

      <form v-else class="space-y-8" @submit.prevent="submit">
        <div v-if="generalError" class="alert alert-error">{{ generalError }}</div>

        <div class="grid gap-8 2xl:grid-cols-2">
          <!-- Overall experience -->
          <div class="card">
            <h2 class="font-display text-lg font-bold text-gray-900">Overall Experience</h2>
            <div class="mt-4 flex flex-wrap gap-2">
              <label v-for="s in scale" :key="s" class="cursor-pointer select-none rounded-lg border-2 px-4 py-2 text-sm font-semibold transition" :class="form.overall_experience === s ? 'border-brand-navy bg-brand-navy text-white' : 'border-gray-200 text-gray-700 hover:border-brand-navy/40'">
                <input v-model="form.overall_experience" type="radio" name="overall_experience" :value="s" required class="sr-only">
                {{ s }}
              </label>
            </div>
          </div>

          <!-- Interests -->
          <div class="card">
            <h2 class="font-display text-lg font-bold text-gray-900">Activities you're interested in for future events</h2>
            <div class="mt-4 flex flex-wrap gap-2">
              <label v-for="activity in activityOptions" :key="activity" class="flex cursor-pointer select-none items-center gap-2 rounded-lg border-2 px-3.5 py-2 text-sm font-medium transition" :class="form.activities_interest.includes(activity) ? 'border-brand-navy bg-brand-navy text-white' : 'border-gray-200 text-gray-700 hover:border-brand-navy/40'">
                <input v-model="form.activities_interest" type="checkbox" :value="activity" class="sr-only">
                {{ activity }}
              </label>
            </div>
          </div>
        </div>

        <!-- Ratings -->
        <div class="card">
          <h2 class="font-display text-lg font-bold text-gray-900">Rate Specific Aspects</h2>
          <div class="mt-5 grid gap-x-10 gap-y-5 2xl:grid-cols-2">
            <div v-for="[field, label] in ratingFields" :key="field">
              <p class="field-label mb-2">{{ label }}</p>
              <div class="flex flex-wrap gap-2">
                <label v-for="s in scale" :key="s" class="cursor-pointer select-none rounded-lg border-2 px-3.5 py-1.5 text-sm font-medium transition" :class="form[field] === s ? 'border-brand-navy bg-brand-navy text-white' : 'border-gray-200 text-gray-700 hover:border-brand-navy/40'">
                  <input v-model="form[field]" type="radio" :name="field" :value="s" :required="field !== 'rating_photography_video'" class="sr-only">
                  {{ s }}
                </label>
              </div>
            </div>
          </div>
        </div>

        <div class="grid gap-8 2xl:grid-cols-2 2xl:items-start">
          <!-- Open feedback -->
          <div class="card">
            <h2 class="font-display text-lg font-bold text-gray-900">Your Thoughts</h2>
            <div class="mt-4 space-y-4">
              <div>
                <label for="best_part" class="field-label mb-1.5">What was the best part of the event?</label>
                <textarea id="best_part" v-model="form.best_part" rows="3" class="field-input mb-0" />
              </div>
              <div>
                <label for="improvements" class="field-label mb-1.5">What could be improved?</label>
                <textarea id="improvements" v-model="form.improvements" rows="3" class="field-input mb-0" />
              </div>
              <div>
                <label for="future_suggestions" class="field-label mb-1.5">Suggestions for future events</label>
                <textarea id="future_suggestions" v-model="form.future_suggestions" rows="3" class="field-input mb-0" />
              </div>
            </div>
          </div>

          <!-- Future participation -->
          <div class="card">
            <h2 class="font-display text-lg font-bold text-gray-900">Will you participate in future SMOSA events?</h2>
            <div class="mt-4 flex flex-wrap gap-2">
              <label v-for="option in participationOptions" :key="option" class="cursor-pointer select-none rounded-lg border-2 px-4 py-2 text-sm font-semibold transition" :class="form.future_participation === option ? 'border-brand-navy bg-brand-navy text-white' : 'border-gray-200 text-gray-700 hover:border-brand-navy/40'">
                <input v-model="form.future_participation" type="radio" name="future_participation" :value="option" required class="sr-only">
                {{ option }}
              </label>
            </div>

            <label for="other_comments" class="field-label mb-1.5 mt-5">Other comments</label>
            <textarea id="other_comments" v-model="form.other_comments" rows="3" class="field-input mb-0" />
          </div>
        </div>

        <button type="submit" class="btn w-full" :disabled="submitting">{{ submitting ? 'Submitting…' : 'Submit Feedback' }}</button>
      </form>
    </div>
  </section>
</template>
