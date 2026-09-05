<script setup>

import MainLayout from '@/Layouts/MainLayout.vue'

defineOptions({
layout: MainLayout
})

const props = defineProps({
nights: Array
})

/* YOUTUBE EMBED */
function embedUrl(link){

if(!link) return ''

let id = null

if(link.includes('youtu.be/')){
id = link.split('youtu.be/')[1].split(/[?&]/)[0]
}

if(link.includes('watch?v=')){
id = link.split('watch?v=')[1].split(/[?&]/)[0]
}

if(link.includes('/embed/')){
return link
}

if(!id) return ''

return `https://www.youtube.com/embed/${id}`

}

</script>



<template>

<div class="page">

<!-- HEADER -->
<header class="header">

<h1>Inspiration Night</h1>

<p>
An evening dedicated to motivation and encouragement.
</p>

</header>



<!-- GRID -->
<div class="grid-layout">

<div
v-for="night in nights"
:key="night.id"
class="card"
>

<!-- IMAGE -->
<img
v-if="night.image"
:src="night.image"
class="image"
/>


<!-- TITLE -->
<h2 class="title">
{{ night.title }}
</h2>


<!-- META -->
<p class="meta">
<strong>Speaker:</strong>
{{ night.speaker || 'Guest Speaker' }}
</p>

<p class="meta">
<strong>Date:</strong>
{{ night.date }}
</p>


<!-- CONTENT -->
<p class="content">
{{ night.description }}
</p>


<!-- VIDEO -->
<div v-if="night.video && embedUrl(night.video)" class="video">

<iframe
:src="embedUrl(night.video)"
frameborder="0"
allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
allowfullscreen
class="video-frame"
></iframe>

</div>


</div>

</div>

</div>

</template>



<style scoped>

/* PAGE */
.page{
max-width:1400px;
margin:auto;
padding:60px 20px;
}

@media (min-width:1536px){
.page{
max-width:1700px;
padding:80px 40px;
}
}

@media (min-width:1920px){
.page{
max-width:1900px;
}
}

/* HEADER */
.header{
text-align:center;
margin-bottom:60px;
}

.header h1{
font-size:38px;
font-weight:800;
color:#3730a3;
}

@media (min-width:1536px){
.header h1{
font-size:48px;
}
}

.header p{
color:#555;
font-size:18px;
margin-top:10px;
}

/* GRID */

.grid-layout{
display:grid;
gap:30px;
grid-template-columns:repeat(1,1fr);
}

@media (min-width:640px){
.grid-layout{
grid-template-columns:repeat(2,1fr);
}
}

@media (min-width:1024px){
.grid-layout{
grid-template-columns:repeat(2,1fr); /* laptop */
}
}

@media (min-width:1280px){
.grid-layout{
grid-template-columns:repeat(3,1fr);
}
}

@media (min-width:1536px){
.grid-layout{
grid-template-columns:repeat(4,1fr);
gap:40px;
}
}

@media (min-width:1920px){
.grid-layout{
grid-template-columns:repeat(5,1fr);
}
}

/* CARD */

.card{
background:white;
padding:20px;
border-radius:16px;
border:1px solid #e5e7eb;
box-shadow:0 4px 12px rgba(0,0,0,0.05);
transition:0.3s;
display:flex;
flex-direction:column;
}

@media (min-width:1536px){
.card{
padding:25px;
}
}

.card:hover{
box-shadow:0 10px 30px rgba(0,0,0,0.1);
}

/* IMAGE */

.image{
width:100%;
height:220px;
object-fit:cover;
border-radius:12px;
margin-bottom:16px;
}

@media (min-width:1536px){
.image{
height:260px;
}
}

/* TEXT */

.title{
font-size:22px;
font-weight:700;
color:#3730a3;
margin-bottom:10px;
}

@media (min-width:1536px){
.title{
font-size:24px;
}
}

.meta{
font-size:14px;
color:#555;
margin-bottom:6px;
}

.content{
color:#444;
line-height:1.7;
margin-top:10px;
flex:1;
}

/* VIDEO */

.video{
margin-top:18px;
}

.video-frame{
width:100%;
height:220px;
border-radius:10px;
}

@media (min-width:1536px){
.video-frame{
height:260px;
}
}

/* MOBILE */
@media (max-width:768px){
.page{
padding:40px 15px;
}
}

</style>