<script setup>
import { useForm } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { ref } from 'vue'

defineOptions({
  layout: MainLayout
})

const props = defineProps({
  pageTitle: { type: String, default: 'SMOSA Feedback' },
  alreadySubmitted: { type: Boolean, default: false }
})

const successMessage = ref(false)
const hasSubmitted = ref(props.alreadySubmitted)

const eventAreas = [
  { key: 'rating_event_organization', label: 'Event organization' },
  { key: 'rating_communication', label: 'Communication before the event' },
  { key: 'rating_venue_setup', label: 'Venue & setup' },
  { key: 'rating_programme_activities', label: 'Programme & activities' },
  { key: 'rating_food_refreshments', label: 'Food & refreshments' },
  { key: 'rating_entertainment', label: 'Entertainment' },
  { key: 'rating_guest_experience', label: 'Guest experience' },
  { key: 'rating_time_management', label: 'Time management' },
  { key: 'rating_photography_video', label: 'Photography & Video' }
]

const activityOptions = [
  'Networking',
  'Career & Business Opportunities',
  'Social Gatherings',
  'Sports & Recreation',
  'Community/Charity Activities',
  'Mentorship',
  'Professional Development',
  'Other'
]

const form = useForm({
  overall_experience: '',
  rating_event_organization: '',
  rating_communication: '',
  rating_venue_setup: '',
  rating_programme_activities: '',
  rating_food_refreshments: '',
  rating_entertainment: '',
  rating_guest_experience: '',
  rating_time_management: '',
  rating_photography_video: '',
  best_part: '',
  improvements: '',
  future_suggestions: '',
  other_comments: '',
  future_participation: '',
  activities_interest: [],
  activities_other: ''
})

function submit() {
  form.post(route('empowerment.smosa-feedback.store'), {
    preserveScroll: true,
    onSuccess: () => {
      if (form.hasErrors) return
      form.reset()
      successMessage.value = true
      hasSubmitted.value = true
      window.scrollTo({ top: 0, behavior: 'smooth' })
      setTimeout(() => {
        successMessage.value = false
      }, 6000)
    }
  })
}
</script>

<template>
  <div class="min-h-screen bg-slate-50">
    <section class="max-w-4xl lg:max-w-5xl xl:max-w-6xl 2xl:max-w-7xl mx-auto py-8 sm:py-12 lg:py-16 xl:py-20 px-4 sm:px-6 lg:px-8 xl:px-12">

      <!-- HEADER -->
      <header class="sm:relative text-center mb-6 sm:mb-10">
        <img
          src="/images/SMOSA.jpg"
          alt="SMOSA Logo"
          class="mx-auto mb-3 h-14 w-14 sm:mb-0 sm:mx-0 sm:absolute sm:top-0 sm:right-0 sm:h-16 sm:w-16 xl:h-20 xl:w-20 rounded-full object-cover shadow-md border-2 border-yellow-400"
        />
        <p class="text-xs sm:text-sm xl:text-base font-bold tracking-widest text-yellow-600 uppercase">SMOSA Homecoming Dinner 2026</p>
        <h1 class="mt-2 text-2xl sm:text-4xl lg:text-5xl xl:text-6xl font-extrabold text-indigo-900 leading-tight">
          Evaluation &amp; Feedback Form
        </h1>
        <p class="mt-2 sm:mt-3 text-base sm:text-lg xl:text-xl font-semibold text-blue-900 italic">
          Reconnect &bull; Relive &bull; Reignite
        </p>
        <p class="mt-4 sm:mt-6 text-sm sm:text-base xl:text-lg text-gray-600 max-w-2xl xl:max-w-3xl mx-auto leading-relaxed">
          Dear SMOSA Member, thank you for being part of the SMOSA Homecoming Dinner 2026.
          Your feedback is important in helping us understand what worked well, what needs
          improvement, and how we can make future SMOSA engagements even better. Please take
          a few minutes to share your honest and constructive feedback.
        </p>
      </header>

      <!-- SUCCESS -->
      <div
        v-if="successMessage"
        class="mb-6 p-4 rounded-lg bg-green-100 text-green-800 border border-green-300 text-center font-semibold text-sm sm:text-base"
      >
        ✅ Thank you for your feedback! Your voice helps us build a better SMOSA.
      </div>

      <!-- ALREADY SUBMITTED -->
      <div
        v-if="hasSubmitted"
        class="bg-white p-8 sm:p-10 rounded-2xl shadow-lg border text-center"
      >
        <p class="text-4xl mb-3">🙏</p>
        <h2 class="text-xl sm:text-2xl font-bold text-indigo-900 mb-2">Feedback Already Received</h2>
        <p class="text-gray-600 max-w-md mx-auto">
          Thank you - we've already recorded feedback from this device for the SMOSA Homecoming
          Dinner 2026. Only one response per device is accepted.
        </p>
      </div>

      <form v-else @submit.prevent="submit" class="space-y-8 sm:space-y-10 xl:space-y-12 bg-white p-4 sm:p-8 lg:p-10 xl:p-14 rounded-2xl shadow-lg border">

        <p v-if="form.errors.duplicate" class="error text-center">{{ form.errors.duplicate }}</p>

        <!-- 1. OVERALL EXPERIENCE -->
        <section>
          <h2 class="section-heading">1. Overall Experience</h2>
          <p class="section-sub">How would you rate your overall experience?</p>

          <div class="flex flex-wrap gap-4 mt-3">
            <label v-for="opt in ['Excellent', 'Very Good', 'Good', 'Fair', 'Poor']" :key="opt" class="radio-pill">
              <input type="radio" v-model="form.overall_experience" :value="opt" name="overall_experience" required />
              <span>{{ opt }}</span>
            </label>
          </div>
          <p v-if="form.errors.overall_experience" class="error">{{ form.errors.overall_experience }}</p>
        </section>

        <!-- 2. EVENT EXPERIENCE -->
        <section>
          <h2 class="section-heading">2. Event Experience</h2>
          <p class="section-sub">Please rate the following:</p>

          <!-- Mobile: stacked cards -->
          <div class="mt-4 space-y-4 sm:hidden">
            <div v-for="area in eventAreas" :key="area.key" class="rating-card">
              <p class="font-medium text-gray-800 mb-2">{{ area.label }}</p>
              <div class="flex flex-wrap gap-2">
                <label v-for="c in ['Excellent', 'Good', 'Fair', 'Poor']" :key="c" class="radio-pill text-xs">
                  <input type="radio" v-model="form[area.key]" :value="c" :name="area.key" required />
                  <span>{{ c }}</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Tablet/Desktop: table -->
          <div class="mt-4 hidden sm:block overflow-x-auto">
            <table class="w-full text-sm xl:text-base border-collapse min-w-[520px]">
              <thead>
                <tr class="text-left text-gray-700">
                  <th class="py-2 xl:py-3 pr-2">Area</th>
                  <th v-for="c in ['Excellent', 'Good', 'Fair', 'Poor']" :key="c" class="py-2 xl:py-3 px-2 text-center font-semibold">
                    {{ c }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="area in eventAreas" :key="area.key" class="border-t border-gray-100">
                  <td class="py-3 xl:py-4 pr-2 text-gray-800 font-medium">{{ area.label }}</td>
                  <td v-for="c in ['Excellent', 'Good', 'Fair', 'Poor']" :key="c" class="py-3 xl:py-4 px-2 text-center">
                    <input type="radio" v-model="form[area.key]" :value="c" :name="area.key" required class="xl:w-4 xl:h-4" />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <!-- 3. WHAT DID WE DO WELL -->
        <section>
          <h2 class="section-heading">3. What Did We Do Well?</h2>
          <p class="section-sub">What was your best or most memorable part of the Homecoming Dinner?</p>
          <textarea v-model="form.best_part" rows="3" class="input mt-2"></textarea>
        </section>

        <!-- 4. WHAT COULD WE IMPROVE -->
        <section>
          <h2 class="section-heading">4. What Could We Improve?</h2>
          <p class="section-sub">What did not go well or could have been handled better?</p>
          <textarea v-model="form.improvements" rows="3" class="input mt-2"></textarea>
        </section>

        <!-- 5. FUTURE ENGAGEMENTS -->
        <section>
          <h2 class="section-heading">5. Future Engagements</h2>
          <p class="section-sub">What would you like SMOSA to improve, introduce or do differently at future events?</p>
          <textarea v-model="form.future_suggestions" rows="3" class="input mt-2"></textarea>
        </section>

        <!-- 6. SUGGESTIONS -->
        <section>
          <h2 class="section-heading">6. Your Suggestions</h2>
          <p class="section-sub">Any other comments, ideas or recommendations for the SMOSA leadership?</p>
          <textarea v-model="form.other_comments" rows="3" class="input mt-2"></textarea>
        </section>

        <!-- 7. FUTURE PARTICIPATION -->
        <section>
          <h2 class="section-heading">7. Future Participation</h2>
          <p class="section-sub">Would you attend another SMOSA Homecoming/Dinner event?</p>

          <div class="flex flex-wrap gap-4 mt-3">
            <label v-for="opt in ['Definitely', 'Probably', 'Not sure', 'Probably not']" :key="opt" class="radio-pill">
              <input type="radio" v-model="form.future_participation" :value="opt" name="future_participation" required />
              <span>{{ opt }}</span>
            </label>
          </div>
          <p v-if="form.errors.future_participation" class="error">{{ form.errors.future_participation }}</p>

          <p class="section-sub mt-6">What type of SMOSA activities would you like to see more of?</p>
          <div class="flex flex-wrap gap-3 mt-3">
            <label v-for="opt in activityOptions" :key="opt" class="checkbox-pill">
              <input type="checkbox" v-model="form.activities_interest" :value="opt" />
              <span>{{ opt }}</span>
            </label>
          </div>

          <div v-if="form.activities_interest.includes('Other')" class="mt-3">
            <input
              v-model="form.activities_other"
              type="text"
              class="input"
              placeholder="Please specify"
            />
          </div>
        </section>

        <!-- SUBMIT -->
        <div class="pt-2">
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full bg-indigo-600 text-white font-semibold py-3 rounded-lg shadow-md hover:bg-indigo-700 hover:shadow-lg transition disabled:opacity-50"
          >
            <span v-if="form.processing">Submitting...</span>
            <span v-else>Submit Feedback</span>
          </button>
        </div>

        <p class="text-center text-gray-500 text-sm">
          Your feedback will be reviewed by the SMOSA Leadership Team and used to guide
          improvements in planning, communication and execution of future engagements.
          <br />
          <span class="font-semibold text-blue-900">SMOSA - Reconnect &bull; Relive &bull; Reignite</span>
        </p>

      </form>

      <p class="text-center mt-10 sm:mt-14 text-3xl sm:text-4xl xl:text-5xl font-extrabold tracking-wide text-indigo-900">
        YES WE CAN
      </p>

    </section>
  </div>
</template>

<style scoped>
.section-heading {
  @apply text-lg sm:text-xl xl:text-2xl font-bold text-indigo-900;
}
.section-sub {
  @apply text-gray-600 mt-1 xl:text-lg;
}
.input {
  width: 100%;
  padding: 12px 14px;
  border-radius: 10px;
  border: 1px solid #d1d5db;
  font-size: 1rem;
  transition: all 0.2s ease;
}
@media (min-width: 1280px) {
  .input {
    padding: 14px 16px;
    font-size: 1.05rem;
  }
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
.rating-card {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 0.85rem;
  background: #f9fafb;
}
.radio-pill,
.checkbox-pill {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.35rem 0.7rem;
  border: 1px solid #d1d5db;
  border-radius: 999px;
  font-size: 0.8rem;
  color: #374151;
  cursor: pointer;
  transition: all 0.15s ease;
  white-space: nowrap;
}
@media (min-width: 640px) {
  .radio-pill,
  .checkbox-pill {
    gap: 0.4rem;
    padding: 0.4rem 0.85rem;
    font-size: 0.875rem;
  }
}
@media (min-width: 1280px) {
  .radio-pill,
  .checkbox-pill {
    gap: 0.5rem;
    padding: 0.55rem 1.1rem;
    font-size: 1rem;
  }
}
.radio-pill:has(input:checked),
.checkbox-pill:has(input:checked) {
  border-color: #4f46e5;
  background: rgba(79, 70, 229, 0.08);
  color: #3730a3;
  font-weight: 600;
}
</style>
