<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'

defineOptions({ layout: AdminLayout })

const form = useForm({
  title: '',
  choir: '',
  date: '',
  description: '',
  video: '',
  image: null
})

const preview = ref(null)

function onFileChange(e) {
  const file = e.target.files[0]
  form.image = file || null
  preview.value = file ? URL.createObjectURL(file) : null
}

function submit() {
  form.post('/admin/christmas-cantata', {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      preview.value = null
      // navigate back to index to see new item
      router.visit('/admin/christmas-cantata')
    }
  })
}
</script>

<template>
  <div class="page">
    <h1 class="title">Create Cantata</h1>

    <div class="form">
      <input v-model="form.title" placeholder="Cantata Title" />
      <p v-if="form.errors.title" class="error">{{ form.errors.title }}</p>

      <input v-model="form.choir" placeholder="Choir" />
      <input type="date" v-model="form.date" />

      <textarea v-model="form.description" placeholder="Description"></textarea>

      <input v-model="form.video" placeholder="YouTube Link" />

      <input type="file" @change="onFileChange" />
      <p v-if="form.errors.image" class="error">{{ form.errors.image }}</p>

      <div v-if="preview" class="preview">
        <img :src="preview" style="max-width:240px; border-radius:6px" />
      </div>

      <div class="btn-row">
        <button @click.prevent="submit" :disabled="form.processing" class="btn-save">
          <span v-if="form.processing">Saving...</span>
          <span v-else>Save Cantata</span>
        </button>

        <Link href="/admin/christmas-cantata" class="btn-cancel">Cancel</Link>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page{max-width:1100px;margin:auto;padding:40px;}
.title{font-size:30px;font-weight:bold;margin-bottom:30px;color:#4338ca;}
.form{background:white;padding:25px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);margin-bottom:40px;}
.form input,.form textarea{width:100%;padding:10px;margin-bottom:12px;border:1px solid #ccc;border-radius:5px;}
.form textarea{min-height:120px;}
.btn-save{background:#4f46e5;color:white;padding:10px 20px;border:none;border-radius:5px;cursor:pointer;}
.btn-cancel{background:#6b7280;color:white;padding:10px 20px;border:none;border-radius:5px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;}
.error{color:#dc2626;font-size:13px;margin-top:-8px;margin-bottom:10px;}
.preview{margin-bottom:10px;}
.btn-row{display:flex;gap:10px;margin-top:10px;}
</style>