<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useForm, Link } from '@inertiajs/vue3'
import { ref } from 'vue'

defineOptions({
  layout: AdminLayout
})

const props = defineProps({
  mentorship: Object
})

const form = useForm({
  title: props.mentorship.title || '',
  caption: props.mentorship.caption || '',
  description: props.mentorship.description || '',
  image: null,
  video: null,
  video_link: props.mentorship.video_link || ''
})

/*
|--------------------------------------------------------------------------
| Existing media
|--------------------------------------------------------------------------
*/

const existingImageUrl = props.mentorship.image
  ? `/storage/${props.mentorship.image}`
  : null

const existingVideoUrl = props.mentorship.video
  ? `/storage/${props.mentorship.video}`
  : null

const imagePreview = ref(existingImageUrl)
const videoPreview = ref(existingVideoUrl)


/*
|--------------------------------------------------------------------------
| Image Change
|--------------------------------------------------------------------------
*/

function onImageChange(e) {

  const file = e.target.files[0]

  form.image = file ?? null

  if (file) {
    imagePreview.value = URL.createObjectURL(file)
  }

}


/*
|--------------------------------------------------------------------------
| Video Change
|--------------------------------------------------------------------------
*/

function onVideoChange(e) {

  const file = e.target.files[0]

  form.video = file ?? null

  if (file) {
    videoPreview.value = URL.createObjectURL(file)
  }

}


/*
|--------------------------------------------------------------------------
| Submit Form
|--------------------------------------------------------------------------
*/

function submit() {

  form.transform((data) => ({
    ...data,
    _method: 'put'
  }))
  .post(`/admin/mentorship/update/${props.mentorship.id}`, {

    forceFormData: true,

    onFinish: () => {

      if (imagePreview.value && form.image) {
        URL.revokeObjectURL(imagePreview.value)
      }

      if (videoPreview.value && form.video) {
        URL.revokeObjectURL(videoPreview.value)
      }

    }

  })

}

</script>



<template>

<div>

<div class="flex items-center justify-between mb-6">

<h1 class="text-2xl font-semibold">
Edit Mentorship
</h1>

<Link
href="/admin/mentorship"
class="text-sm text-gray-600 hover:underline"
>
← Back
</Link>

</div>



<form
@submit.prevent="submit"
class="space-y-6 bg-white p-6 rounded shadow"
>


<!-- TITLE -->

<div>

<label class="block text-sm font-medium mb-1">
Title
</label>

<input
v-model="form.title"
type="text"
class="w-full border rounded px-3 py-2"
/>

<p
v-if="form.errors.title"
class="text-red-600 text-sm mt-1"
>
{{ form.errors.title }}
</p>

</div>



<!-- CAPTION -->

<div>

<label class="block text-sm font-medium mb-1">
Caption
</label>

<input
v-model="form.caption"
type="text"
class="w-full border rounded px-3 py-2"
/>

</div>



<!-- DESCRIPTION -->

<div>

<label class="block text-sm font-medium mb-1">
Description
</label>

<textarea
v-model="form.description"
rows="5"
class="w-full border rounded px-3 py-2"
></textarea>

</div>



<!-- MEDIA -->

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">


<!-- IMAGE -->

<div>

<label class="block text-sm font-medium mb-1">
Replace Image
</label>

<input
type="file"
accept="image/*"
@change="onImageChange"
/>

<p
v-if="form.errors.image"
class="text-red-600 text-sm mt-1"
>
{{ form.errors.image }}
</p>


<div
v-if="imagePreview"
class="mt-3"
>

<img
:src="imagePreview"
class="w-56 h-36 object-cover rounded"
/>

</div>

</div>



<!-- VIDEO -->

<div>

<label class="block text-sm font-medium mb-1">
Replace Video
</label>

<input
type="file"
accept="video/*"
@change="onVideoChange"
/>

<p
v-if="form.errors.video"
class="text-red-600 text-sm mt-1"
>
{{ form.errors.video }}
</p>


<div
v-if="videoPreview"
class="mt-3"
>

<video
controls
class="w-64 h-36 rounded"
>

<source :src="videoPreview" />

</video>

</div>

</div>

</div>



<!-- VIDEO LINK -->

<div>

<label class="block text-sm font-medium mb-1">
YouTube / External Video Link
</label>

<input
v-model="form.video_link"
type="url"
class="w-full border rounded px-3 py-2"
/>

<p
v-if="form.errors.video_link"
class="text-red-600 text-sm mt-1"
>
{{ form.errors.video_link }}
</p>

</div>



<!-- BUTTONS -->

<div class="flex items-center gap-3">

<button
type="submit"
:disabled="form.processing"
class="bg-blue-600 text-white px-4 py-2 rounded shadow"
>
Update
</button>

<Link
href="/admin/mentorship"
class="text-sm text-gray-600 hover:underline"
>
Cancel
</Link>

</div>

</form>

</div>

</template>