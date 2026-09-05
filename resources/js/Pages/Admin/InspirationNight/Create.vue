<template>
  <div class="p-6 max-w-3xl">
    <h1 class="text-xl font-bold mb-4">Create Inspiration Night</h1>

    <form @submit.prevent="submit" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="block mb-1">Title</label>
        <input v-model="form.title" class="input w-full" />
        <div v-if="form.errors.title" class="text-red-600 text-sm">{{ form.errors.title }}</div>
      </div>

      <div class="mb-3">
        <label class="block mb-1">Description</label>
        <textarea v-model="form.description" class="textarea w-full"></textarea>
        <div v-if="form.errors.description" class="text-red-600 text-sm">{{ form.errors.description }}</div>
      </div>

      <div class="mb-3">
        <label class="block mb-1">Image</label>
        <input type="file" @change="onFileChange" />
        <div v-if="preview" class="mt-2">
          <img :src="preview" class="w-48 h-32 object-cover rounded" />
        </div>
        <div v-if="form.errors.image" class="text-red-600 text-sm">{{ form.errors.image }}</div>
      </div>

      <div class="mb-3">
        <label class="block mb-1">Video (URL)</label>
        <input v-model="form.video" class="input w-full" />
        <div v-if="form.errors.video" class="text-red-600 text-sm">{{ form.errors.video }}</div>
      </div>

      <div class="mb-3">
        <label class="block mb-1">Date</label>
        <input type="date" v-model="form.date" class="input" />
        <div v-if="form.errors.date" class="text-red-600 text-sm">{{ form.errors.date }}</div>
      </div>

      <div class="mb-3">
        <label class="block mb-1">Speaker</label>
        <input v-model="form.speaker" class="input w-full" />
        <div v-if="form.errors.speaker" class="text-red-600 text-sm">{{ form.errors.speaker }}</div>
      </div>

      <div class="flex gap-2">
        <button type="submit" class="btn" :disabled="form.processing">Save</button>
        <Link :href="route('admin.inspiration.index')" class="btn-secondary">Cancel</Link>
      </div>
    </form>
  </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ref } from 'vue'

defineOptions({ layout: AdminLayout })

const form = useForm({
  title: '',
  description: '',
  image: null,
  video: '',
  date: '',
  speaker: ''
})

const preview = ref(null)

function onFileChange(e) {
  const file = e.target.files[0]
  form.image = file
  preview.value = file ? URL.createObjectURL(file) : null
}

function submit() {
  form.post(route('admin.inspiration.store'))
}
</script>

<style scoped>
.input { border: 1px solid #e5e7eb; border-radius: .375rem; padding: .5rem .75rem; }
.textarea { border: 1px solid #e5e7eb; border-radius: .375rem; padding: .5rem .75rem; min-height: 120px; }
.btn { padding: .5rem .75rem; background:#1e40af; color: #fff; border-radius:.375rem; }
.btn-secondary { padding: .5rem .75rem; background:#e5e7eb; border-radius:.375rem; }
</style>