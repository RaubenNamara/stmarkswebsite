<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  talk: Object
})

const form = useForm({
  title: props.talk.title,
  description: props.talk.description,
  image: null,
  video: null,
  video_link: props.talk.video_link
})

function submit() {
  form.put(route('admin.girlboytalk.update', props.talk.id))
}
</script>

<template>

<div class="p-6 max-w-4xl">

<h1 class="text-2xl font-bold mb-6">
Edit Girl Boy Talk
</h1>

<form
@submit.prevent="submit"
class="space-y-6"
enctype="multipart/form-data"
>

<!-- Title -->
<div>
<label class="block mb-1 font-semibold">
Title
</label>

<input
v-model="form.title"
type="text"
class="w-full border rounded p-3"
/>
</div>

<!-- Description -->
<div>
<label class="block mb-1 font-semibold">
Description
</label>

<textarea
v-model="form.description"
rows="6"
class="w-full border rounded p-3"
></textarea>
</div>


<!-- Current Image -->
<div v-if="talk.image" class="mb-4">

<p class="text-sm font-medium mb-2">
Current Image
</p>

<img
:src="`/storage/${talk.image}`"
class="rounded-lg max-w-xs border"
/>

</div>


<!-- Replace Image -->
<div>
<label class="block mb-1 font-semibold">
Replace Image
</label>

<input
type="file"
@change="e => form.image = e.target.files[0]"
class="w-full border rounded p-2"
/>
</div>


<!-- Upload Video -->
<div>
<label class="block mb-1 font-semibold">
Upload Video (optional)
</label>

<input
type="file"
@change="e => form.video = e.target.files[0]"
class="w-full border rounded p-2"
/>
</div>


<!-- Video Link -->
<div>
<label class="block mb-1 font-semibold">
Video Link
</label>

<input
v-model="form.video_link"
type="text"
placeholder="https://youtube.com/..."
class="w-full border rounded p-3"
/>
</div>


<!-- Buttons -->
<div class="flex gap-3">

<button
type="submit"
class="bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700"
>
Update
</button>

<Link
:href="route('admin.girlboytalk.index')"
class="text-sm text-gray-600 hover:underline"
>
Cancel
</Link>

</div>

</form>

</div>

</template>