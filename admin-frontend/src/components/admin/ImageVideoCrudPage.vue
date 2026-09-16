<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { api, apiErrorMessage } from '@/services/api'
import { useToast } from '@/composables/useToast'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import type { ImageVideoItem } from '@/types'

/**
 * Shared list/create/edit page for the ~8 "flat table with an optional image and video" admin
 * domains (Co-Curricular, Girl-Boy Talk, Mentorship, Chaplaincy, Inspiration Night, Christmas
 * Cantata, Student Leadership, SMOSA Alumni) - identical UI shape, only labels/fields differ, so
 * one component driven by config instead of 8 near-duplicate files (mirrors the backend's
 * ImageVideoContentService for the same reason).
 */
interface FieldConfig {
  name: string
  label: string
  type?: 'text' | 'textarea' | 'date'
  required?: boolean
}

const props = defineProps<{
  pageTitle: string
  endpoint: string
  listKey: string
  titleField?: string
  titleLabel?: string
  fields?: FieldConfig[]
  imageField?: string
  videoField?: string
  videoLinkField?: string
  createLabel?: string
  subtitleField?: string
  avatarStyle?: boolean
  /** Override the default responsive column count, e.g. '2' to cap at two cards per row even on wide screens. */
  cardsPerRow?: '2' | '3' | '4' | '5'
}>()

const titleField = props.titleField ?? 'title'
const imageField = props.imageField ?? 'image'
const videoField = props.videoField ?? 'video'
const videoLinkField = props.videoLinkField ?? 'video_link'
const fields = props.fields ?? []

const FIXED_GRID_COLS: Record<string, string> = {
  '2': 'grid-cols-1 sm:grid-cols-2',
  '3': 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
  '4': 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4',
  '5': 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5',
}
const gridClass = computed(() => {
  if (props.cardsPerRow) return FIXED_GRID_COLS[props.cardsPerRow]
  return props.avatarStyle ? FIXED_GRID_COLS['5'] : FIXED_GRID_COLS['4']
})

// The backend derives each URL field from the storage column name (e.g. imageColumn 'photo' ->
// 'photo_url'), which isn't always the literal word "image"/"video" - SMOSA Alumni uses a `photo`
// column, so its URL field is `photo_url`, not `image_url`. Hardcoding 'image_url'/'video_url'
// here meant this page's photos never matched anything and always fell back to "no image".
const imageUrlField = `${imageField}_url`
const videoUrlField = `${videoField}_url`

// Some rows have no uploaded image/video file, only an external link in the video_link field (or,
// for a couple of domains, the raw link lives directly in the video/video_url column instead) -
// without this the card shows "No media" even though there's a real YouTube video to preview.
function youtubeId(item: ImageVideoItem): string | null {
  const candidates = [item[videoLinkField], item[videoUrlField]]
  for (const url of candidates) {
    if (typeof url !== 'string') continue
    const match = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([a-zA-Z0-9_-]{11})/)
    if (match) return match[1]
  }
  return null
}

const toast = useToast()
const items = ref<ImageVideoItem[]>([])
const loading = ref(false)
const submitting = ref(false)
const confirmState = reactive({ open: false, id: null as number | null })
const editingId = ref<number | null>(null)
const editingCurrent = ref<{ image: string | null; video: string | null }>({ image: null, video: null })
const formCard = ref<HTMLElement | null>(null)
// Which card's YouTube embed is currently playing - clicking the thumbnail swaps it for an
// iframe in place, so the video plays inside the card instead of navigating to youtube.com.
const playingId = ref<number | null>(null)

const form = reactive<Record<string, string>>({ [titleField]: '' })
for (const f of fields) form[f.name] = ''

const imageFile = ref<File | null>(null)
const videoFile = ref<File | null>(null)

async function fetchAll() {
  loading.value = true
  try {
    const { data } = await api.get(props.endpoint)
    items.value = data.data?.[props.listKey] ?? []
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to load'))
  } finally {
    loading.value = false
  }
}

function resetForm() {
  form[titleField] = ''
  for (const f of fields) form[f.name] = ''
  imageFile.value = null
  videoFile.value = null
  editingId.value = null
  editingCurrent.value = { image: null, video: null }
}

function startEdit(item: ImageVideoItem) {
  editingId.value = item.id
  form[titleField] = String(item[titleField] ?? '')
  for (const f of fields) form[f.name] = String(item[f.name] ?? '')
  imageFile.value = null
  videoFile.value = null
  editingCurrent.value = {
    image: (item[imageUrlField] as string | null) ?? null,
    video: (item[videoUrlField] as string | null) ?? null,
  }
  formCard.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

async function submit() {
  const body = new FormData()
  for (const [key, value] of Object.entries(form)) body.append(key, value)
  if (imageFile.value) body.append(imageField, imageFile.value)
  if (videoFile.value) body.append(videoField, videoFile.value)

  submitting.value = true
  try {
    if (editingId.value !== null) {
      await api.post(`${props.endpoint}/${editingId.value}`, body)
      toast.success('Updated successfully')
    } else {
      await api.post(props.endpoint, body)
      toast.success('Created successfully')
    }
    resetForm()
    await fetchAll()
  } catch (e) {
    toast.error(apiErrorMessage(e, editingId.value !== null ? 'Failed to update' : 'Failed to create'))
  } finally {
    submitting.value = false
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
    await api.delete(`${props.endpoint}/${id}`)
    items.value = items.value.filter((i) => i.id !== id)
    if (editingId.value === id) resetForm()
    toast.success('Deleted successfully')
  } catch (e) {
    toast.error(apiErrorMessage(e, 'Failed to delete'))
  }
}

onMounted(fetchAll)
</script>

<template>
  <div class="p-6 sm:p-8 space-y-8 max-w-6xl mx-auto">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">{{ pageTitle }}</h1>
      <p class="mt-1 text-sm text-gray-500">{{ items.length }} {{ items.length === 1 ? 'item' : 'items' }} published</p>
    </div>

    <div ref="formCard" class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6 sm:p-8">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-bold text-gray-900">{{ editingId !== null ? 'Edit Item' : (createLabel ?? 'Add New') }}</h2>
        <button v-if="editingId !== null" type="button" class="text-sm font-medium text-gray-500 hover:text-gray-700 hover:underline" @click="resetForm">
          Cancel edit
        </button>
      </div>

      <form class="space-y-5" @submit.prevent="submit">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ titleLabel ?? 'Title' }}</label>
          <input v-model="form[titleField]" type="text" required class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20" />
        </div>

        <div v-for="f in fields" :key="f.name">
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ f.label }}</label>
          <textarea
            v-if="f.type === 'textarea'"
            v-model="form[f.name]"
            rows="4"
            :required="f.required"
            class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20"
          ></textarea>
          <input
            v-else
            v-model="form[f.name]"
            :type="f.type === 'date' ? 'date' : 'text'"
            :required="f.required"
            class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20"
          />
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Image</label>
            <img v-if="editingCurrent.image" :src="editingCurrent.image" class="h-24 w-full mb-2 rounded-lg object-cover ring-1 ring-gray-200" />
            <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-dashed border-gray-300 px-3.5 py-3 text-sm text-gray-500 transition hover:border-blue-500/50 hover:bg-blue-50/50">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 8a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2v-6a2 2 0 00-2-2M4 8V6a2 2 0 012-2h4l2 2h6a2 2 0 012 2v2" /></svg>
              <span class="truncate">{{ imageFile ? imageFile.name : 'Choose an image' }}</span>
              <input type="file" accept="image/*" class="hidden" @change="(e) => (imageFile = (e.target as HTMLInputElement).files?.[0] ?? null)" />
            </label>
            <p v-if="editingId !== null" class="text-xs text-gray-400 mt-1.5">Leave blank to keep the current image.</p>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Video</label>
            <video v-if="editingCurrent.video" :src="editingCurrent.video" controls class="h-24 w-full mb-2 rounded-lg bg-gray-900 ring-1 ring-gray-200"></video>
            <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-dashed border-gray-300 px-3.5 py-3 text-sm text-gray-500 transition hover:border-blue-500/50 hover:bg-blue-50/50">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
              <span class="truncate">{{ videoFile ? videoFile.name : 'Choose a video' }}</span>
              <input type="file" accept="video/*" class="hidden" @change="(e) => (videoFile = (e.target as HTMLInputElement).files?.[0] ?? null)" />
            </label>
            <p v-if="editingId !== null" class="text-xs text-gray-400 mt-1.5">Leave blank to keep the current video.</p>
          </div>
        </div>

        <div class="flex gap-3 pt-2">
          <button type="submit" :disabled="submitting" class="bg-blue-900 hover:bg-blue-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition disabled:opacity-50">
            {{ submitting ? 'Saving...' : editingId !== null ? 'Update' : 'Save' }}
          </button>
          <button v-if="editingId !== null" type="button" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition" @click="resetForm">Cancel</button>
        </div>
      </form>
    </div>

    <div>
      <h2 class="text-lg font-bold text-gray-900 mb-4">All Items</h2>

      <div v-if="loading" class="flex items-center justify-center gap-2 py-12 text-gray-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" /></svg>
        <span class="text-sm">Loading...</span>
      </div>

      <div v-else-if="items.length" class="grid gap-5" :class="gridClass">
        <!-- Avatar layout: for "person" domains (e.g. SMOSA Alumni) - a landscape event-photo crop
             would badly crop a portrait headshot, so this shows a circular avatar instead. -->
        <template v-if="avatarStyle">
          <div v-for="item in items" :key="item.id" class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-4 text-center transition hover:shadow-md hover:-translate-y-0.5">
            <img v-if="item[imageUrlField]" :src="item[imageUrlField] as string" class="w-20 h-20 mx-auto rounded-full object-cover object-top" />
            <div v-else class="w-20 h-20 mx-auto rounded-full bg-gray-100"></div>

            <p class="mt-3 font-semibold text-sm text-gray-900 truncate">{{ item[titleField] }}</p>
            <p v-if="subtitleField && item[subtitleField]" class="text-xs text-gray-500 truncate">{{ item[subtitleField] }}</p>

            <div class="mt-3 flex justify-center gap-2">
              <button type="button" class="border border-blue-900 text-blue-900 px-2.5 py-1 rounded-md text-xs font-semibold hover:bg-blue-50 transition" @click="startEdit(item)">Edit</button>
              <button type="button" class="bg-red-600 hover:bg-red-700 text-white px-2.5 py-1 rounded-md text-xs font-semibold transition" @click="requestDelete(item.id)">Delete</button>
            </div>
          </div>
        </template>

        <!-- Landscape media layout: everything else (events, activities) -->
        <div v-else v-for="item in items" :key="item.id" class="group bg-white rounded-xl shadow-sm ring-1 ring-gray-200 overflow-hidden flex flex-col transition hover:shadow-md hover:-translate-y-0.5">
          <div class="aspect-video bg-gray-100">
            <img v-if="item[imageUrlField]" :src="item[imageUrlField] as string" class="w-full h-full object-cover" />
            <video v-else-if="item[videoUrlField] && !youtubeId(item)" :src="item[videoUrlField] as string" controls class="w-full h-full object-cover"></video>
            <iframe
              v-else-if="youtubeId(item) && playingId === item.id"
              :src="`https://www.youtube.com/embed/${youtubeId(item)}?autoplay=1`"
              class="h-full w-full"
              frameborder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowfullscreen
            ></iframe>
            <button
              v-else-if="youtubeId(item)"
              type="button"
              class="relative block h-full w-full bg-gray-900"
              @click="playingId = item.id"
            >
              <img :src="`https://img.youtube.com/vi/${youtubeId(item)}/mqdefault.jpg`" class="h-full w-full object-cover opacity-80" />
              <span class="absolute inset-0 flex items-center justify-center">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-white/90 text-blue-900 shadow">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 translate-x-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                </span>
              </span>
            </button>
            <div v-else class="flex h-full flex-col items-center justify-center gap-1.5 text-gray-300">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 8a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2v-6a2 2 0 00-2-2M4 8V6a2 2 0 012-2h4l2 2h6a2 2 0 012 2v2" /></svg>
              <span class="text-xs font-medium">No media</span>
            </div>
          </div>

          <div class="p-4 flex flex-col flex-1">
            <h3 class="font-semibold text-sm text-gray-900 line-clamp-2 flex-1">{{ item[titleField] }}</h3>
            <p v-if="subtitleField && item[subtitleField]" class="text-xs text-gray-500 truncate">{{ item[subtitleField] }}</p>
            <div class="mt-3 flex justify-end gap-2">
              <button type="button" class="border border-blue-900 text-blue-900 px-2.5 py-1 rounded-md text-xs font-semibold hover:bg-blue-50 transition" @click="startEdit(item)">Edit</button>
              <button type="button" class="bg-red-600 hover:bg-red-700 text-white px-2.5 py-1 rounded-md text-xs font-semibold transition" @click="requestDelete(item.id)">Delete</button>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="flex flex-col items-center gap-2 rounded-xl border-2 border-dashed border-gray-200 py-16 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 20.25a48.25 48.25 0 01-8.135-.687c-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" /></svg>
        <p class="text-sm text-gray-400">Nothing here yet.</p>
      </div>
    </div>

    <ConfirmDialog
      :open="confirmState.open"
      title="Delete item"
      message="Are you sure you want to delete this? This cannot be undone."
      confirm-label="Delete"
      danger
      @confirm="confirmDelete"
      @cancel="confirmState.open = false"
    />
  </div>
</template>
