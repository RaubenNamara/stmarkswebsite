<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({ layout: AdminLayout })

defineProps({
  talks: Array
})

const form = useForm({})

function destroy(id) {
  if (confirm('Delete this talk?')) {
    form.delete(route('admin.girlboytalk.destroy', id))
  }
}
</script>

<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Girl Boy Talks</h1>

      <Link
        :href="route('admin.girlboytalk.create')"
        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
      >
        Add Talk
      </Link>
    </div>

    <div class="bg-white shadow rounded-lg overflow-x-auto">
      <table class="w-full text-left">
        <thead class="bg-gray-100 text-sm">
          <tr>
            <th class="p-4">Image</th>
            <th class="p-4">Title</th>
            <th class="p-4">Description</th>
            <th class="p-4">Video</th>
            <th class="p-4">Video Link</th>
            <th class="p-4">Actions</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="talk in talks"
            :key="talk.id"
            class="border-t align-top"
          >
            <td class="p-4">
              <img
                v-if="talk.image_url"
                :src="talk.image_url"
                class="w-20 h-20 object-cover"
                alt="Talk image"
              />
            </td>

            <td class="p-4 font-semibold w-40">
              {{ talk.title }}
            </td>

            <td class="p-4 text-sm text-justify max-w-md">
              {{ talk.description }}
            </td>

            <td class="p-4">
              <video
                v-if="talk.video_url"
                controls
                class="w-40"
              >
                <source :src="talk.video_url">
              </video>
            </td>

            <td class="p-4">
              <a
                v-if="talk.video_link"
                :href="talk.video_link"
                target="_blank"
                class="text-blue-600 underline text-sm"
              >
                Watch Video
              </a>
            </td>

            <td class="p-4 flex gap-3">
              <Link
                :href="route('admin.girlboytalk.edit', talk.id)"
                class="text-blue-600 hover:underline"
              >
                Edit
              </Link>

              <button
                @click="destroy(talk.id)"
                class="text-red-600 hover:underline"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>