<script setup>
import MainLayout from '@/Layouts/MainLayout.vue'

defineOptions({
layout: MainLayout
})

const props = defineProps({
cantatas: {
  type: Array,
  default: () => []
}
})

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

// format date
function formatDate(date){

if(!date) return ''

return new Date(date).toLocaleDateString('en-GB',{
day:'numeric',
month:'long',
year:'numeric'
})

}
</script>


<template>

<div class="page">

<header class="header">
<h1>Christmas Cantata</h1>
<p>A joyful celebration of the birth of Jesus Christ.</p>
</header>


<div class="grid">

<div
v-for="c in cantatas"
:key="c.id"
class="item"
>

<h2 class="title">
{{ c.title }}
</h2>

<p class="meta">
<strong>Choir:</strong> {{ c.choir }}
</p>

<p class="meta">
<strong>Date:</strong> {{ formatDate(c.date) }}
</p>

<p class="content">
{{ c.description }}
</p>

<!-- IMAGE -->
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
margin-top:10px;
color:#555;
font-size:18px;
}

/* GRID */

.grid{
display:grid;
grid-template-columns:repeat(1,1fr);
gap:30px;
}

@media (min-width:640px){
.grid{
grid-template-columns:repeat(2,1fr);
}
}

@media (min-width:1024px){
.grid{
grid-template-columns:repeat(2,1fr); /* laptop = 2 */
}
}

@media (min-width:1280px){
.grid{
grid-template-columns:repeat(3,1fr);
}
}

@media (min-width:1536px){
.grid{
grid-template-columns:repeat(4,1fr);
gap:40px;
}
}

/* ITEM */

.item{
background:white;
padding:20px;
border:1px solid #e5e7eb;
transition:0.3s;
display:flex;
flex-direction:column;
}

@media (min-width:1536px){
.item{
padding:25px;
}
}

.item:hover{
box-shadow:0 8px 25px rgba(0,0,0,0.1);
}

/* TEXT */

.title{
font-size:22px;
font-weight:700;
margin-bottom:10px;
color:#111827;
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
margin-top:12px;
color:#444;
line-height:1.7;
text-align:justify;
flex:1;
}

/* IMAGE */

.image{
width:100%;
margin-top:18px;
height:220px;
object-fit:cover;
}

@media (min-width:1536px){
.image{
height:260px;
}
}

/* VIDEO */

.video iframe{
width:100%;
height:220px;
margin-top:18px;
}

@media (min-width:1536px){
.video iframe{
height:260px;
}
}

/* MOBILE SPACING FIX */
@media (max-width:768px){
.page{
padding:40px 15px;
}
}

</style>