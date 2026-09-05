<script setup>

import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'

defineOptions({
layout: AdminLayout
})

const props = defineProps({
cantatas: {
  type: Array,
  default: () => []
}
})

const form = useForm()

function deleteCantata(id){

if(confirm('Delete this Cantata?')){

form.delete(`/admin/christmas-cantata/${id}`,{
preserveScroll:true
})

}

}

// convert youtube links
function youtubeEmbed(url){

if(!url) return null

if(url.includes("watch?v=")){
return url.replace("watch?v=","embed/")
}

if(url.includes("youtu.be/")){
return "https://www.youtube.com/embed/" + url.split("youtu.be/")[1].split(/[?&]/)[0]
}

if(url.includes("embed/")){
return url
}

return null
}

</script>


<template>

<div class="page">

<div class="header">

<h1 class="title">
Christmas Cantatas
</h1>

<Link
href="/admin/christmas-cantata/create"
class="btn-create"
>
Add Cantata
</Link>

</div>


<div class="grid">

<div
v-for="c in cantatas"
:key="c.id"
class="card"
>

<!-- ✅ FIXED IMAGE -->
<img
v-if="c.image_url"
:src="c.image_url"
class="image"
/>

<!-- VIDEO -->
<div v-if="c.video" class="video">

<iframe
v-if="youtubeEmbed(c.video)"
:src="youtubeEmbed(c.video)"
frameborder="0"
allowfullscreen
></iframe>

</div>

<h3 class="cantata-title">
{{ c.title }}
</h3>

<p class="meta">
<strong>Choir:</strong> {{ c.choir }}
</p>

<p class="meta">
<strong>Date:</strong> {{ c.date }}
</p>

<p class="desc">
{{ c.description }}
</p>

<div class="actions">

<Link
:href="`/admin/christmas-cantata/${c.id}`"
class="btn-view"
>
View
</Link>

<Link
:href="`/admin/christmas-cantata/${c.id}/edit`"
class="btn-edit"
>
Edit
</Link>

<button
@click="deleteCantata(c.id)"
class="btn-delete"
>
Delete
</button>

</div>

</div>

</div>

</div>

</template>


<style scoped>

.page{
max-width:1100px;
margin:auto;
padding:40px;
}

.header{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:30px;
}

.title{
font-size:30px;
font-weight:bold;
color:#4338ca;
}

.btn-create{
background:#16a34a;
color:white;
padding:10px 18px;
border-radius:6px;
text-decoration:none;
font-weight:600;
}

.grid{
display:grid;
grid-template-columns:repeat(2,1fr);
gap:20px;
}

.card{
background:white;
padding:20px;
border-radius:10px;
box-shadow:0 2px 8px rgba(0,0,0,0.1);
}

.image{
width:100%;
height:200px;
object-fit:cover;
margin-bottom:10px;
}

/* VIDEO */
.video iframe{
width:100%;
height:200px;
margin-bottom:10px;
}

.cantata-title{
font-size:18px;
font-weight:bold;
margin-bottom:6px;
}

.meta{
font-size:14px;
color:#555;
}

.desc{
margin-top:8px;
color:#444;
text-align:justify;
}

.actions{
margin-top:12px;
display:flex;
gap:10px;
}

.btn-view{
background:#0ea5e9;
color:white;
padding:8px 14px;
border-radius:5px;
text-decoration:none;
}

.btn-edit{
background:#2563eb;
color:white;
padding:8px 14px;
border-radius:5px;
text-decoration:none;
}

.btn-delete{
background:#dc2626;
color:white;
border:none;
padding:8px 14px;
border-radius:5px;
cursor:pointer;
}

@media (max-width:768px){

.grid{
grid-template-columns:1fr;
}

}

</style>