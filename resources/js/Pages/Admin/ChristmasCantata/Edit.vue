```vue
<script setup>

import { Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({
layout: AdminLayout
})

const props = defineProps({
cantata: Object
})

const cantata = props.cantata

const preview = ref(null)

const form = useForm({

_method: 'put',
title: cantata.title || '',
choir: cantata.choir || '',
date: cantata.date || '',
description: cantata.description || '',
video: cantata.video || '',
image: null

})

function onFileChange(e){

const file = e.target.files[0]

form.image = file

if(file){
preview.value = URL.createObjectURL(file)
}

}

function submit(){

form.post(route('admin.christmas-cantata.update', cantata.id), {
forceFormData: true
})

}

</script>


<template>

<div class="max-w-3xl">

<!-- HEADER -->
<div class="flex items-center justify-between mb-6">

<h1 class="text-2xl font-bold">
Edit Cantata
</h1>

<Link
:href="route('admin.christmas-cantata.index')"
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

<!-- TITLE -->
<div>

<label class="label">
Title
</label>

<input
v-model="form.title"
type="text"
class="input w-full"
/>

<p v-if="form.errors.title" class="error">
{{ form.errors.title }}
</p>

</div>


<!-- CHOIR -->
<div>

<label class="label">
Choir
</label>

<input
v-model="form.choir"
type="text"
class="input w-full"
/>

</div>


<!-- DATE -->
<div>

<label class="label">
Date
</label>

<input
v-model="form.date"
type="date"
class="input w-full"
/>

</div>


<!-- DESCRIPTION -->
<div>

<label class="label">
Description
</label>

<textarea
v-model="form.description"
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


<!-- CURRENT IMAGE -->
<div>

<label class="label">
Current Image
</label>

<div v-if="cantata.image" class="mt-2">

<img
:src="cantata.image"
class="w-40 h-28 object-cover rounded-lg border"
/>

</div>

</div>


<!-- REPLACE IMAGE -->
<div>

<label class="label">
Replace Image
</label>

<input
type="file"
accept="image/*"
@change="onFileChange"
/>

<p v-if="form.errors.image" class="error">
{{ form.errors.image }}
</p>

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
Update Cantata
</span>

</button>

<Link
:href="route('admin.christmas-cantata.index')"
class="btn-cancel"
>
Cancel
</Link>

</div>

</form>

</div>

</template>


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
```
