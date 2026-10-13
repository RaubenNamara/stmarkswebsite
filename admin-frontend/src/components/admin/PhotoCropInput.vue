<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue'
import Cropper from 'cropperjs'
import 'cropperjs/dist/cropper.css'

const props = withDefaults(
  defineProps<{
    modelValue: File | null
    /** Currently saved photo, shown until a new file is picked (edit mode). */
    existingUrl?: string | null
    /** Crop box aspect ratio - defaults to a square, matching this app's circular avatar display. */
    aspectRatio?: number
  }>(),
  { existingUrl: null, aspectRatio: 1 },
)

const emit = defineEmits<{ 'update:modelValue': [File | null] }>()

const fileInput = ref<HTMLInputElement | null>(null)
const cropImageEl = ref<HTMLImageElement | null>(null)
let cropper: Cropper | null = null

const cropModalOpen = ref(false)
const cropSourceUrl = ref<string | null>(null)
const pendingFile = ref<File | null>(null)

const previewUrl = ref<string | null>(null)
const compressing = ref(false)

// Keeps the preview in sync whenever the parent sets/clears modelValue (e.g. resetForm()), not
// just when this component picks the file itself.
watch(
  () => props.modelValue,
  (file) => {
    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)
    previewUrl.value = file ? URL.createObjectURL(file) : null
  },
  { immediate: true },
)

onBeforeUnmount(() => {
  if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)
  if (cropSourceUrl.value) URL.revokeObjectURL(cropSourceUrl.value)
  cropper?.destroy()
})

function formatSize(bytes: number): string {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} KB`
  return `${(bytes / 1024 / 1024).toFixed(1)} MB`
}

function pickFile() {
  fileInput.value?.click()
}

function onFileChosen(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0] ?? null
  ;(e.target as HTMLInputElement).value = '' // lets picking the exact same file again re-trigger change
  if (!file) return
  pendingFile.value = file
  cropSourceUrl.value = URL.createObjectURL(file)
  cropModalOpen.value = true
  nextTick(() => {
    if (!cropImageEl.value) return
    cropper = new Cropper(cropImageEl.value, {
      aspectRatio: props.aspectRatio,
      viewMode: 1,
      autoCropArea: 1,
      background: false,
      responsive: true,
    })
  })
}

function closeCropModal() {
  cropper?.destroy()
  cropper = null
  if (cropSourceUrl.value) URL.revokeObjectURL(cropSourceUrl.value)
  cropSourceUrl.value = null
  pendingFile.value = null
  cropModalOpen.value = false
}

function confirmCrop() {
  if (!cropper) return
  const canvas = cropper.getCroppedCanvas({ maxWidth: 1600, maxHeight: 1600, imageSmoothingQuality: 'high' })
  canvas.toBlob(
    (blob) => {
      if (!blob) return
      const name = pendingFile.value?.name ?? 'photo.jpg'
      emit('update:modelValue', new File([blob], name, { type: 'image/jpeg' }))
      closeCropModal()
    },
    'image/jpeg',
    0.92,
  )
}

function skipCrop() {
  if (pendingFile.value) emit('update:modelValue', pendingFile.value)
  closeCropModal()
}

function removePhoto() {
  emit('update:modelValue', null)
}

// Re-encodes the currently held file at a capped size/quality - a manual step rather than
// something applied automatically, so the admin can see and control the size/quality trade-off
// (staff photos are portraits, not full galleries, so this is about trimming an oversized phone
// photo before upload, not aggressive lossy compression).
async function compressPhoto() {
  const file = props.modelValue
  if (!file || compressing.value) return
  compressing.value = true
  try {
    const bitmap = await createImageBitmap(file)
    const maxDimension = 800
    const scale = Math.min(1, maxDimension / Math.max(bitmap.width, bitmap.height))
    const width = Math.round(bitmap.width * scale)
    const height = Math.round(bitmap.height * scale)

    const canvas = document.createElement('canvas')
    canvas.width = width
    canvas.height = height
    const ctx = canvas.getContext('2d')
    if (!ctx) return
    ctx.drawImage(bitmap, 0, 0, width, height)
    bitmap.close()

    const blob = await new Promise<Blob | null>((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.7))
    if (!blob) return
    // A compressed result that isn't actually smaller (already-tiny or already-compressed source)
    // would just be a lossier re-encode for no benefit - keep the original in that case.
    if (blob.size >= file.size) return
    const name = file.name.replace(/\.[^.]+$/, '') + '.jpg'
    emit('update:modelValue', new File([blob], name, { type: 'image/jpeg' }))
  } finally {
    compressing.value = false
  }
}

const currentSizeLabel = computed(() => (props.modelValue ? formatSize(props.modelValue.size) : null))
</script>

<template>
  <div>
    <img v-if="!previewUrl && existingUrl" :src="existingUrl" class="h-24 w-24 mb-2 rounded-full object-cover object-top ring-1 ring-gray-200" />

    <div v-if="previewUrl" class="mb-2 flex items-center gap-3">
      <img :src="previewUrl" class="h-24 w-24 rounded-full object-cover object-top ring-1 ring-gray-200" />
      <div class="flex flex-col items-start gap-1.5">
        <span class="text-xs text-gray-500">{{ currentSizeLabel }}</span>
        <div class="flex gap-2">
          <button
            type="button"
            class="rounded-md border border-gray-300 px-2.5 py-1 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 disabled:opacity-50"
            :disabled="compressing"
            @click="compressPhoto"
          >
            {{ compressing ? 'Compressing…' : 'Compress' }}
          </button>
          <button type="button" class="rounded-md border border-gray-300 px-2.5 py-1 text-xs font-semibold text-gray-700 transition hover:bg-gray-50" @click="pickFile">
            Replace
          </button>
          <button type="button" class="rounded-md border border-red-200 px-2.5 py-1 text-xs font-semibold text-red-600 transition hover:bg-red-50" @click="removePhoto">
            Remove
          </button>
        </div>
      </div>
    </div>

    <label
      v-else
      class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-dashed border-gray-300 px-3.5 py-3 text-sm text-gray-500 transition hover:border-blue-500/50 hover:bg-blue-50/50"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 8a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2v-6a2 2 0 00-2-2M4 8V6a2 2 0 012-2h4l2 2h6a2 2 0 012 2v2" /></svg>
      <span>Choose a photo</span>
      <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="onFileChosen" />
    </label>

    <Teleport to="body">
      <div v-if="cropModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" role="dialog" aria-modal="true" aria-label="Crop photo" @keydown.esc="closeCropModal">
        <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
          <h2 class="text-lg font-semibold text-slate-900">Crop photo</h2>
          <p class="mt-1 text-sm text-slate-500">Drag to reposition, drag a corner to resize. It'll be saved as a square.</p>

          <div class="mt-4 h-80 w-full overflow-hidden bg-gray-100">
            <img ref="cropImageEl" :src="cropSourceUrl ?? undefined" class="block max-w-full" />
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button type="button" class="rounded-md px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100" @click="closeCropModal">Cancel</button>
            <button type="button" class="rounded-md px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100" @click="skipCrop">Use original</button>
            <button type="button" class="rounded-md bg-blue-900 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800" @click="confirmCrop">Crop &amp; use</button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
