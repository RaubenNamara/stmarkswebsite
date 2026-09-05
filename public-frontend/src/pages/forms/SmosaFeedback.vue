<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useHead } from '@unhead/vue'
import { api, apiErrorMessage, apiFieldErrors } from '../../services/api'
import PageHeader from '../../components/PageHeader.vue'

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
  <PageHeader title="SMOSA Feedback" subtitle="Help us improve future SMOSA events by sharing your experience." />

  <section class="mx-auto max-w-2xl px-6 py-14">
    <div class="card">
      <div v-if="success" class="alert alert-success">Thank you for your feedback!</div>
      <div v-else-if="alreadySubmitted" class="alert alert-success">You've already submitted feedback for this event. Thank you!</div>
      <form v-else @submit.prevent="submit">
        <div v-if="generalError" class="alert alert-error mb-6">{{ generalError }}</div>

        <label for="overall_experience" class="field-label">Overall Experience</label>
        <select id="overall_experience" v-model="form.overall_experience" required class="field-input">
          <option value="">Select...</option>
          <option v-for="s in scale" :key="s" :value="s">{{ s }}</option>
        </select>

        <template v-for="[field, label] in ratingFields" :key="field">
          <label class="field-label">{{ label }}</label>
          <select v-model="form[field]" :required="field !== 'rating_photography_video'" class="field-input">
            <option value="">Select...</option>
            <option v-for="s in scale" :key="s" :value="s">{{ s }}</option>
          </select>
        </template>

        <label class="field-label">Activities you're interested in for future events</label>
        <div class="mb-4 space-y-1.5">
          <label v-for="activity in activityOptions" :key="activity" class="flex items-center gap-2 text-sm font-normal text-gray-700">
            <input v-model="form.activities_interest" type="checkbox" :value="activity" class="h-4 w-4 rounded border-gray-300 text-brand-navy focus:ring-brand-navy">
            {{ activity }}
          </label>
        </div>

        <label for="best_part" class="field-label">What was the best part of the event?</label>
        <textarea id="best_part" v-model="form.best_part" rows="3" class="field-input" />

        <label for="improvements" class="field-label">What could be improved?</label>
        <textarea id="improvements" v-model="form.improvements" rows="3" class="field-input" />

        <label for="future_suggestions" class="field-label">Suggestions for future events</label>
        <textarea id="future_suggestions" v-model="form.future_suggestions" rows="3" class="field-input" />

        <label for="future_participation" class="field-label">Will you participate in future SMOSA events?</label>
        <select id="future_participation" v-model="form.future_participation" required class="field-input">
          <option value="">Select...</option>
          <option value="Definitely">Definitely</option>
          <option value="Probably">Probably</option>
          <option value="Not sure">Not sure</option>
          <option value="Unlikely">Unlikely</option>
        </select>

        <label for="other_comments" class="field-label">Other comments</label>
        <textarea id="other_comments" v-model="form.other_comments" rows="3" class="field-input" />

        <button type="submit" class="btn w-full" :disabled="submitting">{{ submitting ? 'Submitting…' : 'Submit Feedback' }}</button>
      </form>
    </div>
  </section>
</template>
