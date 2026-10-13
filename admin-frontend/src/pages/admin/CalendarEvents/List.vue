<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { api, apiErrorMessage } from '@/services/api'
import { useToast } from '@/composables/useToast'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import type { ApiResponse, CalendarEventItem } from '@/types'

const toast = useToast()
const items = ref<CalendarEventItem[]>([])
const loading = ref(false)
const submitting = ref(false)
const confirmState = reactive({ open: false, id: null as number | null })
const editingId = ref<number | null>(null)
const formCard = ref<HTMLElement | null>(null)

const form = reactive({ title: '', event_date: '', description: '' })

async function fetchAll() {
  loading.value = true
  try {
    const { data } = await api.get<ApiResponse<{ calendar_events: CalendarEventItem[] }>>('/admin/calendar-events')
    items.value = data.data?.calendar_events ?? []
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to load calendar events'))
  } finally {
    loading.value = false
  }
}

function resetForm() {
  form.title = ''
  form.event_date = ''
  form.description = ''
  editingId.value = null
}

function startEdit(item: CalendarEventItem) {
  editingId.value = item.id
  form.title = item.title
  form.event_date = item.event_date
  form.description = item.description ?? ''
  formCard.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

async function submit() {
  submitting.value = true
  try {
    if (editingId.value !== null) {
      await api.post(`/admin/calendar-events/${editingId.value}`, form)
      toast.success('Event updated')
    } else {
      await api.post('/admin/calendar-events', form)
      toast.success('Event added')
    }
    resetForm()
    await fetchAll()
  } catch (e) {
    toast.error(apiErrorMessage(e, editingId.value !== null ? 'Failed to update event' : 'Failed to add event'))
  } finally {
    submitting.value = false
  }
}

async function toggleCompleted(item: CalendarEventItem) {
  const previous = item.is_completed
  item.is_completed = previous ? 0 : 1 // optimistic - a tick should feel instant
  try {
    await api.post(`/admin/calendar-events/${item.id}/toggle-completed`)
  } catch (e) {
    item.is_completed = previous
    toast.error(apiErrorMessage(e, 'Failed to update'))
  }
}

function requestDelete(id: number) {
  confirmState.open = true
  confirmState.id = id
}

async function confirmDelete() {
  const id = confirmState.id
  confirmState.open = false
  if (id === null) return
  try {
    await api.delete(`/admin/calendar-events/${id}`)
    items.value = items.value.filter((i) => i.id !== id)
    if (editingId.value === id) resetForm()
    toast.success('Event deleted')
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to delete'))
  }
}

function formatDate(value: string): string {
  const d = new Date(`${value}T00:00:00`)
  return Number.isNaN(d.getTime()) ? value : d.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' })
}

onMounted(fetchAll)
</script>

<template>
  <div class="p-6 sm:p-8 max-w-7xl 2xl:max-w-[1600px] mx-auto">
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">School Calendar</h1>
      <p class="mt-1 text-sm text-gray-500">{{ items.length }} {{ items.length === 1 ? 'event' : 'events' }} on the calendar</p>
    </div>

    <!-- Form on the side rather than stacked above a full-width list - on a wide/27" screen a
         single-column stack leaves most of the width empty; the form only needs to be as wide as
         its inputs, so it sits fixed-width and sticky while the list gets the remaining space.
         Below lg it collapses back to the original stacked order (form above list). -->
    <div class="lg:grid lg:grid-cols-[360px_1fr] lg:items-start lg:gap-8">
      <div ref="formCard" class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6 sm:p-8 lg:sticky lg:top-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-lg font-bold text-gray-900">{{ editingId !== null ? 'Edit Event' : 'Add Event' }}</h2>
          <button v-if="editingId !== null" type="button" class="text-sm font-medium text-gray-500 hover:text-gray-700 hover:underline" @click="resetForm">
            Cancel edit
          </button>
        </div>

        <form class="space-y-5" @submit.prevent="submit">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Title</label>
            <input v-model="form.title" type="text" required class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Date</label>
            <input v-model="form.event_date" type="date" required class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20" />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Description (optional)</label>
            <textarea v-model="form.description" rows="3" class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20"></textarea>
          </div>

          <div class="flex gap-3 pt-2">
            <button type="submit" :disabled="submitting" class="bg-blue-900 hover:bg-blue-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition disabled:opacity-50">
              {{ submitting ? 'Saving...' : editingId !== null ? 'Update Event' : 'Add Event' }}
            </button>
            <button v-if="editingId !== null" type="button" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition" @click="resetForm">Cancel</button>
          </div>
        </form>
      </div>

      <div class="mt-8 lg:mt-0">
        <div v-if="loading" class="flex items-center justify-center gap-2 py-16 text-gray-400">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" /></svg>
          <span class="text-sm">Loading...</span>
        </div>

        <div v-else-if="!items.length" class="flex flex-col items-center gap-2 rounded-xl border-2 border-dashed border-gray-200 py-16 text-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
          <p class="text-sm text-gray-400">No calendar events yet.</p>
        </div>

        <!-- One card holding every event as a row/sub-section, instead of a separate box per
             event - a tick here flips is_completed, which the public homepage widget and the
             /academics/school-calendar page both reflect automatically (same API data, no
             separate sync step). A 2-column split at xl+ keeps rows from stretching edge-to-edge
             on very wide screens once the sidebar layout above already frees up the space. -->
        <div v-else class="divide-y divide-gray-100 rounded-xl bg-white shadow-sm ring-1 ring-gray-200 xl:columns-2 xl:divide-y-0 xl:gap-0">
          <div v-for="item in items" :key="item.id" class="flex items-start gap-4 p-4 sm:p-5 xl:break-inside-avoid xl:border-b xl:border-gray-100">
            <button
              type="button"
              :aria-label="`Mark '${item.title}' as ${item.is_completed ? 'not done' : 'done'}`"
              class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full border-2 transition"
              :class="item.is_completed ? 'border-green-500 bg-green-500 text-white hover:opacity-80' : 'border-gray-300 text-transparent hover:border-green-500 hover:text-green-500'"
              @click="toggleCompleted(item)"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
            </button>

            <div class="min-w-0 flex-1">
              <p class="text-xs font-semibold uppercase tracking-wide" :class="item.is_completed ? 'text-gray-400' : 'text-blue-700'">{{ formatDate(item.event_date) }}</p>
              <h3 class="font-semibold" :class="item.is_completed ? 'text-gray-500 line-through' : 'text-gray-900'">{{ item.title }}</h3>
              <p v-if="item.description" class="mt-1 text-sm" :class="item.is_completed ? 'text-gray-400' : 'text-gray-500'">{{ item.description }}</p>
            </div>

            <div class="flex shrink-0 gap-2">
              <button type="button" class="border border-blue-900 text-blue-900 px-2.5 py-1 rounded-md text-xs font-semibold hover:bg-blue-50 transition" @click="startEdit(item)">Edit</button>
              <button type="button" class="bg-red-600 hover:bg-red-700 text-white px-2.5 py-1 rounded-md text-xs font-semibold transition" @click="requestDelete(item.id)">Delete</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <ConfirmDialog
      :open="confirmState.open"
      title="Delete event"
      message="Are you sure you want to delete this calendar event? This cannot be undone."
      confirm-label="Delete"
      danger
      @confirm="confirmDelete"
      @cancel="confirmState.open = false"
    />
  </div>
</template>
