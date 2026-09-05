```vue
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'
import { useForm, router } from '@inertiajs/vue3'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  cantata: Object
})

const cantata = props.cantata

const form = useForm()

function destroy() {
  if (!confirm('Delete this cantata?')) return

  form.delete(`/admin/christmas-cantata/${cantata.id}`, {
    onSuccess: () => router.visit('/admin/christmas-cantata')
  })
}

// Convert YouTube links to embed format
function youtubeEmbed(url){

  if(!url) return null

  // normal youtube link
  if(url.includes("watch?v=")){
    return url.replace("watch?v=","embed/")
  }

  // short youtube link
  if(url.includes("youtu.be/")){
    return "https://www.youtube.com/embed/" + url.split("youtu.be/")[1]
  }

  // already embed
  if(url.includes("embed/")){
    return url
  }

  return null
}
</script>

<template>
  <div class="page">
    <h1 class="title">Cantata details</h1>

    <div class="card" style="padding:20px;margin-bottom:20px;">

      <h2 class="cantata-title">
        {{ cantata.title }}
      </h2>

      <p class="meta">
        <strong>Choir:</strong> {{ cantata.choir || '-' }}
      </p>

      <p class="meta">
        <strong>Date:</strong> {{ cantata.date || '-' }}
      </p>

      <p class="meta">
        <strong>Created:</strong> {{ cantata.created_at || '-' }}
      </p>

      <div style="margin-top:12px;">
        <p class="desc">
          {{ cantata.description }}
        </p>
      </div>

      <!-- IMAGE -->
      <div v-if="cantata.image" style="margin-top:14px;">
        <img
          :src="cantata.image"
          style="max-width:560px;border-radius:6px"
        />
      </div>

      <!-- YOUTUBE VIDEO -->
      <div v-if="cantata.video" style="margin-top:20px;">

        <iframe
          v-if="youtubeEmbed(cantata.video)"
          :src="youtubeEmbed(cantata.video)"
          width="560"
          height="315"
          frameborder="0"
          allowfullscreen
          class="rounded"
        ></iframe>

        <div v-else>
          <p class="meta">
            <strong>Video:</strong>
            <a :href="cantata.video" target="_blank">
              {{ cantata.video }}
            </a>
          </p>
        </div>

      </div>

      <!-- ACTION BUTTONS -->
      <div style="display:flex; gap:10px; margin-top:20px;">

        <Link
          :href="`/admin/christmas-cantata/${cantata.id}/edit`"
          class="btn-edit"
        >
          Edit
        </Link>

        <button
          @click="destroy"
          class="btn-delete"
        >
          Delete
        </button>

        <Link
          href="/admin/christmas-cantata"
          class="btn-cancel"
        >
          Back
        </Link>

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

.title{
font-size:30px;
font-weight:bold;
margin-bottom:30px;
color:#4338ca;
}

.card{
background:white;
border-radius:10px;
box-shadow:0 2px 8px rgba(0,0,0,0.08);
}

.cantata-title{
font-size:20px;
font-weight:700;
margin-bottom:8px;
}

.meta{
font-size:14px;
color:#555;
margin-top:6px;
}

.desc{
color:#444;
margin-top:8px;
white-space:pre-line;
}

.btn-edit{
background:#2563eb;
color:white;
border:none;
padding:8px 14px;
border-radius:5px;
cursor:pointer;
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

.btn-cancel{
background:#6b7280;
color:white;
padding:8px 14px;
border-radius:5px;
border:none;
text-decoration:none;
}

</style>
```
