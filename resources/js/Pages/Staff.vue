<script setup>
import { computed } from 'vue'
import MainLayout from '@/Layouts/MainLayout.vue'

defineOptions({
  layout: MainLayout
})

const props = defineProps({
  groupedStaff: {
    type: Object,
    default: () => ({})
  }
})

function initials(name = '') {
  const cleaned = String(name || '').trim()
  if (!cleaned) return 'NA'

  const parts = cleaned.split(/\s+/).filter(Boolean)
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase()

  return ((parts[0]?.[0] || '') + (parts[parts.length - 1]?.[0] || '')).toUpperCase()
}

function normalizeCategory(category = '') {
  const value = String(category || '').trim().toLowerCase()

  if (!value) return 'Uncategorized'

  if (value === 'administrator' || value === 'administrators') {
    return 'Administrators'
  }

  if (
    value === 'head of department' ||
    value === 'heads of department' ||
    value === 'heads of departments'
  ) {
    return 'Heads of Departments'
  }

  if (
    value === 'support staff' ||
    value === 'non teaching staff' ||
    value === 'non-teaching staff'
  ) {
    return 'Non Teaching Staff'
  }

  if (value === 'teaching staff') {
    return 'Teaching Staff'
  }

  return String(category || '').trim()
}

function getOrderValue(person, fallbackIndex = 0) {
  const candidates = [
    person?.sort_order,
    person?.display_order,
    person?.position,
    person?.order
  ]

  for (const value of candidates) {
    const num = Number(value)
    if (Number.isFinite(num)) return num
  }

  return fallbackIndex + 1
}

function sortMembers(members = []) {
  return [...members]
    .map((person, index) => ({
      ...person,
      category: normalizeCategory(person.category),
      _fallbackIndex: index
    }))
    .sort((a, b) => {
      const orderA = getOrderValue(a, a._fallbackIndex)
      const orderB = getOrderValue(b, b._fallbackIndex)

      if (orderA !== orderB) return orderA - orderB
      return Number(a.id) - Number(b.id)
    })
}

function categoryPriority(category) {
  const order = [
    'Administrators',
    'Heads of Departments',
    'Teaching Staff',
    'Non Teaching Staff',
    'Uncategorized'
  ]

  const normalized = normalizeCategory(category)
  const index = order.indexOf(normalized)
  return index === -1 ? 999 : index
}

function photoUrl(photo) {
  if (!photo) return ''
  const value = String(photo).trim()
  if (!value) return ''

  if (value.startsWith('http://') || value.startsWith('https://') || value.startsWith('//')) {
    return value
  }

  const cleaned = value.replace(/^\/+/, '')
  return `/storage/${cleaned}`
}

function displayGroupTitle(category = '') {
  const normalized = normalizeCategory(category)

  if (normalized === 'Administrators') return 'Administrators'
  if (normalized === 'Heads of Departments') return 'Heads Of Departments'
  if (normalized === 'Teaching Staff') return 'Teaching Staff'
  if (normalized === 'Non Teaching Staff') return 'Non Teaching Staff'

  return normalized
}

function displayRole(person = {}) {
  const options = [
    person?.title,
    person?.department,
    person?.position,
    person?.designation,
    person?.role,
    person?.job_title
  ]

  for (const value of options) {
    const text = String(value || '').trim()
    if (text) return text
  }

  return ''
}

const orderedGroupedStaff = computed(() => {
  const result = {}

  Object.entries(props.groupedStaff || {})
    .sort(([a], [b]) => categoryPriority(a) - categoryPriority(b))
    .forEach(([category, members]) => {
      const normalizedCategory = normalizeCategory(category)
      result[normalizedCategory] = sortMembers(Array.isArray(members) ? members : [])
    })

  return result
})

const hasStaff = computed(() => Object.keys(orderedGroupedStaff.value).length > 0)
</script>

<template>
  <section class="relative min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top,_rgba(59,130,246,0.10),_transparent_35%),radial-gradient(circle_at_right,_rgba(250,204,21,0.10),_transparent_28%),linear-gradient(to_bottom,_#f8fafc,_#ffffff,_#f8fafc)]">
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
      <div class="absolute -left-20 top-10 h-72 w-72 rounded-full bg-blue-200/30 blur-3xl"></div>
      <div class="absolute right-0 top-1/4 h-96 w-96 rounded-full bg-amber-200/25 blur-3xl"></div>
      <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-sky-200/20 blur-3xl"></div>
    </div>

    <section class="relative z-10 mx-auto w-full max-w-[1800px] px-4 py-12 sm:px-6 sm:py-14 lg:px-10 lg:py-16 xl:px-14 2xl:px-20">
      <header class="mx-auto mb-12 max-w-4xl text-center sm:mb-14 lg:mb-16">
        <div class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-white/85 px-4 py-2 text-[11px] font-extrabold tracking-[0.3em] text-blue-900 shadow-sm backdrop-blur">
          <span class="h-2 w-2 rounded-full bg-blue-900"></span>
          STAFF DIRECTORY
          <span class="h-2 w-2 rounded-full bg-amber-400"></span>
        </div>

        <h1 class="mt-5 text-4xl font-black tracking-tight text-slate-950 sm:text-5xl lg:text-6xl xl:text-7xl">
          OUR STAFF
        </h1>

        <p class="mx-auto mt-4 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base lg:text-lg xl:text-xl">
          Meet our dedicated team in a clean, organized directory.
        </p>
      </header>

      <div
        v-if="!hasStaff"
        class="mx-auto max-w-3xl rounded-[2rem] border border-dashed border-slate-300 bg-white/80 px-6 py-16 text-center shadow-[0_10px_40px_rgba(15,23,42,0.06)] backdrop-blur-sm sm:px-10 sm:py-20"
      >
        <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-50 to-amber-50 text-3xl shadow-inner">
          👥
        </div>
        <p class="text-lg font-bold text-slate-800 sm:text-xl">No staff available.</p>
        <p class="mt-2 text-sm text-slate-500 sm:text-base">
          Staff profiles will appear here once they are added from the admin panel.
        </p>
      </div>

      <div
        v-for="(members, category) in orderedGroupedStaff"
        :key="category"
        class="mb-16 sm:mb-20 lg:mb-24"
      >
        <div class="mb-8 text-center sm:mb-10">
          <div class="inline-flex max-w-full items-center gap-3 rounded-full border border-blue-100 bg-white/90 px-4 py-2 shadow-[0_10px_30px_rgba(15,23,42,0.05)] backdrop-blur sm:px-5">
            <span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>
            <h2 class="text-sm font-extrabold tracking-[0.22em] text-blue-950 sm:text-base lg:text-lg xl:text-xl">
              {{ displayGroupTitle(category).toUpperCase() }}
            </h2>
            <span class="h-2.5 w-2.5 rounded-full bg-blue-900"></span>
          </div>

          <p class="mx-auto mt-4 max-w-2xl text-xs leading-6 text-slate-500 sm:text-sm sm:leading-7">
          Meet our team: the staff members listed below form part of our professional workforce.
          </p>
        </div>

        <div class="staff-grid">
          <article
            v-for="person in members"
            :key="person.id"
            class="group relative overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white/90 shadow-[0_14px_45px_rgba(15,23,42,0.08)] backdrop-blur transition-all duration-500 hover:-translate-y-2 hover:shadow-[0_28px_70px_rgba(15,23,42,0.14)]"
          >
            <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-blue-950 via-sky-500 to-amber-400"></div>

            <div class="relative p-6 sm:p-7 lg:p-8">
              <div class="mb-6 flex justify-center">
                <div class="relative">
                  <div class="absolute inset-0 -z-10 rounded-full bg-gradient-to-br from-blue-100 via-white to-amber-100 blur-2xl opacity-70 transition duration-500 group-hover:opacity-100"></div>

                  <div class="premium-avatar">
                    <img
                      v-if="person.photo"
                      :src="photoUrl(person.photo)"
                      :alt="person.name || 'Staff member photo'"
                      loading="lazy"
                    />
                    <div v-else class="premium-initials">
                      {{ initials(person.name) }}
                    </div>
                  </div>
                </div>
              </div>

              <h3 class="text-center text-lg font-extrabold tracking-tight text-slate-950 sm:text-xl lg:text-[1.35rem]">
                {{ person.name }}
              </h3>

              <div v-if="displayRole(person)" class="mt-4 flex justify-center">
                <span class="inline-flex items-center rounded-full bg-slate-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                  {{ displayRole(person) }}
                </span>
              </div>
            </div>

            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-slate-50/70 to-transparent opacity-0 transition duration-500 group-hover:opacity-100"></div>
          </article>
        </div>
      </div>
    </section>
  </section>
</template>

<style scoped>
.staff-grid {
  display: grid;
  gap: 1.25rem;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
}

@media (min-width: 640px) {
  .staff-grid {
    gap: 1.5rem;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  }
}

@media (min-width: 1024px) {
  .staff-grid {
    gap: 1.75rem;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  }
}

@media (min-width: 1536px) {
  .staff-grid {
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  }
}

.premium-avatar {
  position: relative;
  width: clamp(128px, 14vw, 215px);
  aspect-ratio: 1 / 1.05;
  border-radius: 9999px;
  padding: 7px;
  background: linear-gradient(135deg, #0f2f6b, #38bdf8, #facc15);
  box-shadow:
    0 20px 45px rgba(15, 47, 107, 0.18),
    0 10px 25px rgba(2, 6, 23, 0.08);
  transition: transform 0.45s cubic-bezier(.34,1.56,.64,1), box-shadow 0.45s ease;
}

.group:hover .premium-avatar {
  transform: translateY(-8px) scale(1.03);
  box-shadow:
    0 28px 60px rgba(15, 47, 107, 0.22),
    0 16px 35px rgba(2, 6, 23, 0.12);
}

.premium-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 9999px;
  border: 8px solid rgba(255, 255, 255, 0.96);
  background: #fff;
}

.premium-initials {
  width: 100%;
  height: 100%;
  border-radius: 9999px;
  background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 900;
  font-size: clamp(1.8rem, 3vw, 2.6rem);
  color: #062347;
  border: 8px solid rgba(255, 255, 255, 0.96);
  letter-spacing: 0.05em;
}

@media (max-width: 640px) {
  .premium-avatar {
    width: clamp(118px, 46vw, 150px);
  }
}
</style>