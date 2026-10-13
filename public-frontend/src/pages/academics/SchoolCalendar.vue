<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useHead } from '@unhead/vue'
import { api } from '../../services/api'
import PageHeader from '../../components/PageHeader.vue'
import EmptyState from '../../components/EmptyState.vue'
import { staticAsset } from '../../utils/staticAsset'

useHead({ title: 'School Calendar' })

const { data } = await api.get('/calendar-events')
const events = data.data.calendar_events as Array<Record<string, any>>

const upcoming = computed(() => events.filter((e) => !e.is_completed))
const past = computed(() => events.filter((e) => e.is_completed))

// Highlights the event linked to from a #event-N URL (e.g. clicked from a Home page card).
// Driven in JS rather than CSS :target - :target doesn't reliably fire on vue-router's
// client-side (History API) navigation, only on a real browser fragment navigation, so it never
// lit up here even though the scroll-to-element itself (main.ts's scrollBehavior) works fine.
const route = useRoute()
const highlightedEventId = ref<number | null>(null)
let highlightTimer: ReturnType<typeof setTimeout> | null = null

function applyHighlightFromHash() {
  const match = route.hash.match(/^#event-(\d+)$/)
  if (!match) return
  if (highlightTimer) clearTimeout(highlightTimer)
  highlightedEventId.value = Number(match[1])
  highlightTimer = setTimeout(() => {
    highlightedEventId.value = null
  }, 3200) // matches the CSS pulse's 1.6s x 2 iterations below
}
onMounted(applyHighlightFromHash)
watch(() => route.hash, applyHighlightFromHash)

function formatDate(value: string): string {
  const d = new Date(`${value}T00:00:00`)
  return Number.isNaN(d.getTime()) ? value : d.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' })
}

function dayOfMonth(value: string): string {
  const d = new Date(`${value}T00:00:00`)
  return Number.isNaN(d.getTime()) ? '' : String(d.getDate())
}

function monthLabel(value: string): string {
  const d = new Date(`${value}T00:00:00`)
  return Number.isNaN(d.getTime()) ? '' : d.toLocaleDateString(undefined, { month: 'short' })
}
</script>

<template>
  <PageHeader title="School Calendar" subtitle="Key dates for the academic year." :bg-image="staticAsset('storage/images/smacon.jpg')" />

  <!-- container-wide (not a fixed max-w-*) so this scales up properly on 27"+ monitors instead
       of leaving most of the screen empty - matches the events-grid pattern already used on the
       Home page, just with every event shown here (not just a teaser handful). -->
  <section class="container-wide py-14">
    <div v-if="upcoming.length" class="relative overflow-hidden rounded-2xl bg-white p-6 shadow-card ring-1 ring-black/5 sm:p-8">
      <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-brand-navy via-brand-gold to-brand-navy" />
      <h2 class="font-display text-2xl font-bold text-gray-900">Upcoming Events</h2>

      <div class="events-grid mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 2xl:grid-cols-7">
        <div
          v-for="event in upcoming"
          :id="`event-${event.id}`"
          :key="event.id"
          class="event-card card-interactive flex flex-col items-center gap-2 rounded-xl border border-gray-100 bg-white p-3 text-center shadow-sm transition-all duration-200 hover:-translate-y-1 hover:border-brand-gold/40 hover:shadow-md"
          :class="{ 'event-card-highlight': event.id === highlightedEventId }"
        >
          <div :title="formatDate(event.event_date)" class="flex w-fit items-baseline gap-1 rounded-md bg-gray-50 px-2 py-1 ring-1 ring-gray-100">
            <span class="font-display text-base font-extrabold leading-none text-brand-navy">{{ dayOfMonth(event.event_date) }}</span>
            <span class="text-[12px] font-bold uppercase tracking-widest text-brand-gold">{{ monthLabel(event.event_date) }}</span>
          </div>
          <h3 :title="event.title" class="line-clamp-2 font-display text-xs font-bold text-gray-900">{{ event.title }}</h3>
          <p v-if="event.description" :title="event.description" class="line-clamp-2 text-[13px] leading-snug text-gray-500">{{ event.description }}</p>
        </div>
      </div>
    </div>

    <EmptyState v-else message="No upcoming events on the calendar right now." />

    <div v-if="past.length" class="relative mt-10 overflow-hidden rounded-2xl bg-white p-6 shadow-card ring-1 ring-black/5 sm:p-8">
      <h2 class="font-display text-2xl font-bold text-gray-900">Past Events</h2>

      <div class="events-grid mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 2xl:grid-cols-7">
        <div
          v-for="event in past"
          :id="`event-${event.id}`"
          :key="event.id"
          class="event-card relative flex flex-col items-center gap-2 rounded-xl border border-gray-100 bg-white p-3 text-center shadow-sm"
          :class="{ 'event-card-highlight': event.id === highlightedEventId }"
        >
          <div :title="formatDate(event.event_date)" class="relative flex w-fit items-baseline gap-1 rounded-md bg-gray-50 px-2 py-1 ring-1 ring-gray-100">
            <span class="font-display text-base font-extrabold leading-none text-brand-navy">{{ dayOfMonth(event.event_date) }}</span>
            <span class="text-[12px] font-bold uppercase tracking-widest text-brand-gold">{{ monthLabel(event.event_date) }}</span>
            <span class="absolute -right-1.5 -top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-green-500 text-white shadow-sm">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
            </span>
          </div>
          <h3 :title="event.title" class="line-clamp-2 font-display text-xs font-bold text-gray-900">{{ event.title }}</h3>
          <p v-if="event.description" :title="event.description" class="line-clamp-2 text-[13px] leading-snug text-gray-500">{{ event.description }}</p>
        </div>
      </div>
    </div>

    <div class="card mx-auto mt-10 max-w-3xl">
      <p class="text-gray-600">The school calendar, including term dates, holidays and key academic events, is shared with parents and guardians at the start of each term.</p>
    </div>
  </section>
</template>

<style scoped>
/* Matches Home.vue's own 27"+ (>=1920px) breakpoint for its events grid - up to 8 across in one
   row instead of capping out at 6, since a 27" monitor has the width to spare and this is the
   full calendar listing (potentially many more events than the Home page's capped teaser). */
@media (min-width: 1920px) {
  .events-grid {
    grid-template-columns: repeat(8, minmax(0, 1fr));
  }
}

/* #event-N deep link from the Home page's cards: scroll-margin-top keeps the card from landing
   hidden under the sticky header. The highlight ring itself is driven by the
   .event-card-highlight class (script setup, via route.hash) rather than CSS :target - :target
   doesn't reliably fire on vue-router's client-side navigation (only on a real browser fragment
   navigation), so it never lit up even though the scroll-to-element itself worked fine. */
.event-card {
  scroll-margin-top: 100px;
}
.event-card-highlight {
  animation: eventTargetPulse 1.6s ease-out 2;
}
@keyframes eventTargetPulse {
  0% {
    box-shadow: 0 0 0 0 rgba(250, 204, 21, 0.6);
  }
  70% {
    box-shadow: 0 0 0 14px rgba(250, 204, 21, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(250, 204, 21, 0);
  }
}
@media (prefers-reduced-motion: reduce) {
  .event-card-highlight {
    animation: none;
    box-shadow: 0 0 0 4px rgba(250, 204, 21, 0.6);
  }
}
</style>
