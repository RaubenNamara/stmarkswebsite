```vue
<template>

<div class="p-6 max-w-3xl">

  <h1 class="text-xl font-bold mb-4">
    Edit Inspiration Night
  </h1>

  <form @submit.prevent="submit">

    <!-- Title -->
    <div class="mb-3">
      <label class="block mb-1">Title</label>

      <input
        v-model="form.title"
        type="text"
        class="input w-full"
      />

      <div v-if="form.errors.title" class="text-red-600 text-sm">
        {{ form.errors.title }}
      </div>
    </div>


    <!-- Description -->
    <div class="mb-3">

      <label class="block mb-1">Description</label>

      <textarea
        v-model="form.description"
        class="textarea w-full"
      ></textarea>

      <div v-if="form.errors.description" class="text-red-600 text-sm">
        {{ form.errors.description }}
      </div>

    </div>


    <!-- Current Image -->
    <div class="mb-3">

      <label class="block mb-1">Current Image</label>

      <div v-if="talk.image_url">
        <img
          :src="talk.image_url"
          class="w-48 h-32 object-cover rounded"
        />
      </div>

      <div v-else class="text-gray-500">
        No image uploaded
      </div>

    </div>


    <!-- Replace Image -->
    <div class="mb-3">

      <label class="block mb-1">Replace Image</label>

      <input type="file" @change="onFileChange">

      <div v-if="preview" class="mt-2">
        <img
          :src="preview"
          class="w-48 h-32 object-cover rounded"
        />
      </div>

    </div>


    <!-- Video URL -->
    <div class="mb-3">

      <label class="block mb-1">Video (YouTube URL)</label>

      <input
        v-model="form.video"
        class="input w-full"
        placeholder="https://youtu.be/Z7EpK3_LQsc"
      />

    </div>


    <!-- Video Preview -->
    <div v-if="videoId" class="mb-4">

      <label class="block mb-1">Video Preview</label>

      <iframe
        class="w-full h-64 rounded"
        :src="`https://www.youtube.com/embed/${videoId}`"
        frameborder="0"
        allowfullscreen>
      </iframe>

    </div>


    <!-- Date -->
    <div class="mb-3">

      <label class="block mb-1">Date</label>

      <input
        type="date"
        v-model="form.date"
        class="input"
      />

    </div>


    <!-- Speaker -->
    <div class="mb-3">

      <label class="block mb-1">Speaker</label>

      <input
        v-model="form.speaker"
        class="input w-full"
      />

    </div>


    <!-- Buttons -->
    <div class="flex gap-2">

      <button
        type="submit"
        class="btn"
        :disabled="form.processing"
      >
        Update
      </button>

      <Link
        :href="route('admin.inspiration.index')"
        class="btn-secondary"
      >
        Cancel
      </Link>

    </div>

  </form>

</div>

</template>



<script setup>

import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ref, computed } from 'vue'

defineOptions({
  layout: AdminLayout
})

const props = defineProps({
  talk: Object
})


/* Form */

const form = useForm({

  _method: 'put',

  title: props.talk.title ?? '',
  description: props.talk.description ?? '',
  image: null,
  video: props.talk.video ?? '',
  date: props.talk.date ?? '',
  speaker: props.talk.speaker ?? ''

})


/* Image preview */

const preview = ref(null)

function onFileChange(e){

  const file = e.target.files[0]

  form.image = file

  preview.value = file
    ? URL.createObjectURL(file)
    : null

}


/* Extract YouTube ID safely */

function extractId(url){

  if(!url) return null

  const patterns = [
    /youtu\.be\/([^?&]+)/,
    /youtube\.com.*v=([^&]+)/,
    /youtube\.com\/embed\/([^?&]+)/
  ]

  for(const pattern of patterns){

    const match = url.match(pattern)

    if(match) return match[1]

  }

  return null
}


/* Computed Video ID */

const videoId = computed(() => extractId(form.video))


/* Submit */

function submit(){

  form.post(
    route('admin.inspiration.update', props.talk.id),
    { forceFormData: true }
  )

}

</script>



<style scoped>

.input{
border:1px solid #e5e7eb;
border-radius:.375rem;
padding:.5rem .75rem;
}

.textarea{
border:1px solid #e5e7eb;
border-radius:.375rem;
padding:.5rem .75rem;
min-height:120px;
}

.btn{
padding:.5rem .75rem;
background:#1e40af;
color:white;
border-radius:.375rem;
}

.btn-secondary{
padding:.5rem .75rem;
background:#e5e7eb;
border-radius:.375rem;
}

</style>
```
