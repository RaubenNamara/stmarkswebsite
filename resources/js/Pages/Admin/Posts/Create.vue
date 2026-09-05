<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  title: '',
  content: ''
})

function submit() {
  form.post(route('admin.posts.store'))
}
</script>

<template>
  <div class="p-8">
    <h1 class="text-2xl font-bold mb-6">Create Post</h1>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="block font-semibold">Title</label>
        <input v-model="form.title" class="border p-2 w-full rounded" />
      </div>

      <div>
        <label class="block font-semibold">Content</label>
        <textarea v-model="form.content" class="border p-2 w-full rounded"></textarea>
      </div>

      <button class="bg-blue-900 text-white px-4 py-2 rounded">
        Save Post
      </button>
    </form>
  </div>
</template>