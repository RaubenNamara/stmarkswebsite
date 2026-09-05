<template>

<div class="max-w-3xl">

<!-- HEADER -->
<div class="flex items-center justify-between mb-6">

<h1 class="text-2xl font-bold">
Edit Alumni
</h1>

<Link
:href="route('admin.smosa.index')"
class="text-gray-600 hover:text-black"
>
← Back
</Link>

</div>


<!-- FORM -->
<form
@submit.prevent="submit"
class="space-y-6 bg-white p-6 rounded-xl shadow"
>

<!-- NAME -->
<div>

<label class="label">
Name
</label>

<input
v-model="form.name"
type="text"
class="input w-full"
/>

<p v-if="form.errors.name" class="error">
{{ form.errors.name }}
</p>

</div>


<!-- PROFESSION -->
<div>

<label class="label">
Profession
</label>

<input
v-model="form.profession"
type="text"
class="input w-full"
/>

<p v-if="form.errors.profession" class="error">
{{ form.errors.profession }}
</p>

</div>


<!-- MESSAGE -->
<div>

<label class="label">
Message / Bio
</label>

<textarea
v-model="form.message"
rows="4"
class="textarea w-full"
/>

</div>


<!-- VIDEO -->
<div>

<label class="label">
Video URL
</label>

<input
v-model="form.video"
type="text"
class="input w-full"
/>

</div>


<!-- CURRENT PHOTO -->
<div>

<label class="label">
Current Photo
</label>

<div v-if="alumni.photo" class="mt-2">

<img
:src="alumni.photo"
class="w-40 h-28 object-cover rounded-lg border"
/>

</div>

</div>


<!-- REPLACE PHOTO -->
<div>

<label class="label">
Replace Photo (optional)
</label>

<input
type="file"
accept="image/*"
@change="onFileChange"
/>

<p v-if="form.errors.photo" class="error">
{{ form.errors.photo }}
</p>

<!-- PREVIEW -->
<img
v-if="preview"
:src="preview"
class="mt-3 w-40 h-28 object-cover rounded-lg border"
/>

</div>


<!-- BUTTONS -->
<div class="flex gap-3 pt-2">

<button
type="submit"
:disabled="form.processing"
class="btn"
>

<span v-if="form.processing">
Updating...
</span>

<span v-else>
Update Alumni
</span>

</button>

<Link
:href="route('admin.smosa.index')"
class="btn-cancel"
>
Cancel
</Link>

</div>

</form>

</div>

</template>


<script setup>

import { Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({
layout: AdminLayout
})

const props = defineProps({
alumni: Object
})

const alumni = props.alumni

const preview = ref(null)

const form = useForm({

_method: 'put',
name: alumni.name || '',
profession: alumni.profession || '',
message: alumni.message || '',
video: alumni.video || '',
photo: null

})


function onFileChange(e){

const file = e.target.files[0]

form.photo = file

if(file){
preview.value = URL.createObjectURL(file)
}

}


function submit(){

form.post(route('admin.smosa.update', alumni.id), {
forceFormData: true
})

}

</script>


<style scoped>

.label{
display:block;
font-weight:600;
margin-bottom:4px;
}

.input{
border:1px solid #ddd;
padding:10px;
border-radius:6px;
}

.textarea{
border:1px solid #ddd;
padding:10px;
border-radius:6px;
}

.btn{
background:#2563eb;
color:white;
padding:10px 16px;
border-radius:6px;
font-weight:500;
}

.btn:hover{
background:#1e40af;
}

.btn-cancel{
background:#e5e7eb;
padding:10px 16px;
border-radius:6px;
}

.error{
color:#dc2626;
font-size:13px;
margin-top:4px;
}

</style>