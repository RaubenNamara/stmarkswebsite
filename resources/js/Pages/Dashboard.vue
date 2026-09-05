<template>
  <div>
    <Head title="Admin Dashboard" />

    <div class="py-12 bg-gray-100 min-h-screen">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-10">

        <!-- ================= UPLOAD SECTION ================= -->
        <section class="bg-white shadow-xl rounded-xl p-8">
          <h3 class="text-xl font-bold mb-6 border-b pb-3">
            Upload New Slide
          </h3>

          <div class="grid md:grid-cols-2 gap-8">

            <!-- LEFT -->
            <div>
              <label class="block font-semibold mb-2">Slide Type</label>

              <select
                v-model="form.type"
                class="border p-3 w-full rounded-lg mb-4"
              >
                <option value="image">Image</option>
                <option value="video">Video</option>
              </select>

              <input
                type="file"
                @change="handleFile"
                class="border p-3 w-full rounded-lg"
                :accept="form.type === 'image'
                  ? 'image/*'
                  : 'video/mp4,video/mov,video/avi'"
              />

              <!-- PREVIEW -->
              <div v-if="previewUrl" class="mt-4">
                <img v-if="form.type === 'image'" :src="previewUrl" class="h-44 w-full object-cover rounded"/>
                <video v-else :src="previewUrl" controls class="h-44 w-full object-cover rounded"></video>
              </div>
            </div>

            <!-- RIGHT -->
            <div class="space-y-5">
              <input v-model="form.title" placeholder="Title" class="border p-3 w-full rounded"/>
              <input v-model="form.caption" placeholder="Caption" class="border p-3 w-full rounded"/>

              <div class="flex gap-3">
                <button
                  @click="submit"
                  :disabled="form.processing"
                  class="flex-1 bg-yellow-400 px-6 py-3 rounded font-bold"
                >
                  {{ form.processing ? 'Uploading...' : 'Upload Slide' }}
                </button>

                <button @click="resetForm" class="px-4 py-3 border rounded">
                  Reset
                </button>
              </div>
            </div>
          </div>
        </section>

        <!-- ================= SLIDES LIST ================= -->
        <section class="bg-white shadow-xl rounded-xl p-8">
          <h3 class="text-xl font-bold mb-6 border-b pb-3">
            Uploaded Slides
          </h3>

          <div v-if="!localSlides.length" class="text-center py-10">
            No slides uploaded yet.
          </div>

          <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
              v-for="slide in localSlides"
              :key="slide.id"
              class="bg-gray-50 rounded-xl shadow-md overflow-hidden"
            >

              <!-- ✅ IMAGE -->
              <img
                v-if="slide.type === 'image' && slide.image_url"
                :src="slide.image_url"
                class="h-44 w-full object-cover"
              />

              <!-- ✅ VIDEO -->
              <video
                v-if="slide.type === 'video' && slide.video_url"
                :src="slide.video_url"
                controls
                class="h-44 w-full object-cover"
              ></video>

              <div class="p-4">
                <h4 class="font-semibold">{{ slide.title }}</h4>
                <p class="text-sm text-gray-600">{{ slide.caption }}</p>

                <div class="mt-3 grid grid-cols-2 gap-3">
                  <button
                    @click="confirmDelete(slide.id)"
                    class="bg-red-600 text-white px-3 py-2 rounded text-sm"
                  >
                    Delete
                  </button>

                  <!-- ✅ FIXED VIEW -->
                  <a
                    :href="getMedia(slide)"
                    target="_blank"
                    class="border px-3 py-2 rounded text-sm text-center"
                  >
                    View
                  </a>
                </div>
              </div>

            </div>
          </div>
        </section>

      </div>
    </div>
  </div>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref, reactive, watch } from 'vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  slides: Array
})

const localSlides = reactive([...props.slides])

watch(() => props.slides, (newSlides) => {
  localSlides.length = 0
  newSlides.forEach(s => localSlides.push(s))
})

const previewUrl = ref(null)

const form = useForm({
  type: 'image',
  image: null,
  video: null,
  title: '',
  caption: ''
})

function handleFile(e) {
  const file = e.target.files[0]
  if (!file) return

  previewUrl.value = URL.createObjectURL(file)

  if (form.type === 'video') {
    form.video = file
    form.image = null
  } else {
    form.image = file
    form.video = null
  }
}

function submit() {
  form.post(route('slides.store'), {
    forceFormData: true,
    onSuccess: () => window.location.reload()
  })
}

function resetForm() {
  form.reset()
  previewUrl.value = null
}

function confirmDelete(id) {
  if (!confirm('Delete this slide?')) return

  form.delete(route('slides.destroy', id), {
    onSuccess: () => {
      const i = localSlides.findIndex(s => s.id === id)
      if (i !== -1) localSlides.splice(i, 1)
    }
  })
}

/* ✅ HELPER */
function getMedia(slide) {
  return slide.type === 'image'
    ? slide.image_url
    : slide.video_url
}
</script>