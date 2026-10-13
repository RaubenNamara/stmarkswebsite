<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useHead } from '@unhead/vue'
import { api } from '../services/api'
import PageHeader from '../components/PageHeader.vue'
import EmptyState from '../components/EmptyState.vue'
import AvatarImage from '../components/AvatarImage.vue'
import { staticAsset } from '../utils/staticAsset'

useHead({ title: 'Our Staff' })

const { data } = await api.get('/staff')
const groups = data.data.groups as Record<string, Array<Record<string, any>>>
const categories = Object.keys(groups)

// Highlights the staff member linked to from a #staff-N URL (e.g. clicked from a Home page team
// card). Driven in JS rather than CSS :target - :target doesn't reliably fire on vue-router's
// client-side (History API) navigation, only on a real browser fragment navigation, so it never
// lit up here even though the scroll-to-element itself (main.ts's scrollBehavior) works fine.
const route = useRoute()
const highlightedStaffId = ref<number | null>(null)
let highlightTimer: ReturnType<typeof setTimeout> | null = null

function applyHighlightFromHash() {
  const match = route.hash.match(/^#staff-(\d+)$/)
  if (!match) return
  if (highlightTimer) clearTimeout(highlightTimer)
  highlightedStaffId.value = Number(match[1])
  highlightTimer = setTimeout(() => {
    highlightedStaffId.value = null
  }, 3200) // matches the CSS pulse's 1.6s x 2 iterations below
}
onMounted(applyHighlightFromHash)
watch(() => route.hash, applyHighlightFromHash)
</script>

<template>
  <PageHeader title="Our Staff" :bg-image="staticAsset('storage/images/smacon.jpg')" />

  <section class="container-wide py-14">
    <div v-for="category in categories" :key="category" class="mb-14">
      <template v-if="category === 'Administrator' || category === 'Head of Department'">
        <div class="rounded-3xl bg-white p-8 shadow-card ring-1 ring-brand-gold/25 sm:p-10">
          <h2 class="font-display text-2xl font-bold text-brand-navy sm:text-3xl">{{ category }}</h2>

          <div class="mt-8 grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            <div
              v-for="member in groups[category]"
              :id="`staff-${member.id}`"
              :key="member.id"
              class="staff-card card-interactive rounded-2xl bg-gray-50 p-6 text-center ring-1 ring-black/5"
              :class="{ 'staff-card-highlight': member.id === highlightedStaffId }"
            >
              <AvatarImage :src="member.photo_url" :alt="member.name" size="lg" />
              <div class="mt-4 font-display text-base font-bold text-gray-900">{{ member.name }}</div>
              <div class="mt-1 inline-block rounded-full bg-brand-gold/10 px-3 py-1 text-xs font-bold uppercase tracking-wide text-brand-navy">{{ member.department }}</div>
            </div>
          </div>
        </div>
      </template>

      <template v-else>
        <div class="rounded-3xl bg-white p-8 shadow-card ring-1 ring-black/5 sm:p-10">
          <h2 class="font-display text-2xl font-bold text-brand-navy sm:text-3xl">{{ category }}</h2>

          <div class="mt-8 grid grid-cols-2 gap-5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-7">
            <div
              v-for="member in groups[category]"
              :id="`staff-${member.id}`"
              :key="member.id"
              class="staff-card card-interactive rounded-xl bg-gray-50 p-4 text-center ring-1 ring-black/5"
              :class="{ 'staff-card-highlight': member.id === highlightedStaffId }"
            >
              <AvatarImage :src="member.photo_url" :alt="member.name" />
              <div class="mt-2 text-sm font-semibold text-gray-900">{{ member.name }}</div>
              <div class="text-xs text-gray-500">{{ member.department }}</div>
            </div>
          </div>
        </div>
      </template>
    </div>

    <EmptyState v-if="!categories.length" message="Staff directory coming soon." />
  </section>
</template>

<style scoped>
/* Offsets the fragment scroll so the card doesn't land hidden under the sticky header - used
   both by main.ts's scrollBehavior (SPA navigation) and a hard refresh straight on a
   /staff#staff-N URL (native browser fragment scroll). */
.staff-card {
  scroll-margin-top: 100px;
}

/* Briefly rings the card in gold when arriving via a #staff-N link (from the Home page's team
   cards), so it's obvious which person was clicked rather than just landing generically in the
   directory. Driven by the .staff-card-highlight class (script setup, via route.hash) rather
   than CSS :target - :target doesn't reliably fire on vue-router's client-side navigation (only
   on a real browser fragment navigation), so it never lit up even though the scroll-to-element
   itself worked fine. */
.staff-card-highlight {
  animation: staffTargetPulse 1.6s ease-out 2;
}
@keyframes staffTargetPulse {
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
  .staff-card-highlight {
    animation: none;
    box-shadow: 0 0 0 4px rgba(250, 204, 21, 0.6);
  }
}
</style>
