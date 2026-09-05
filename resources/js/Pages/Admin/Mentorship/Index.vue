<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  mentorships: {
    type: Array,
    default: () => []
  }
})

function deleteMentorship(id) {
  if (!confirm('Delete this mentorship item?')) return

  router.delete(`/admin/mentorship/delete/${id}`, {
    preserveScroll: true
  })
}

function embedUrl(link) {
  if (!link) return ''

  try {
    if (link.includes('youtube.com/watch')) {
      return link.replace('watch?v=', 'embed/')
    }

    if (link.includes('youtu.be/')) {
      const id = link.split('youtu.be/')[1].split(/[?&]/)[0]
      return `https://www.youtube.com/embed/${id}`
    }

    return link
  } catch {
    return link
  }
}
</script>

<template>
  <div class="p-8">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">Manage Mentorship</h1>
        <p class="text-sm text-gray-500 mt-1">Create, edit, and manage mentorship content.</p>
      </div>

      <Link
        href="/admin/mentorship/create"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow"
      >
        + Create Mentorship
      </Link>
    </div>

    <div class="bg-white shadow rounded-2xl overflow-hidden border border-gray-200">
      <table class="min-w-full">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Title</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Caption</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Media</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="m in mentorships" :key="m.id" class="border-t hover:bg-gray-50/70">
            <td class="px-4 py-4 text-sm font-medium text-gray-800 align-top">
              {{ m.title }}
            </td>

            <td class="px-4 py-4 text-sm text-gray-600 align-top">
              {{ m.caption || '—' }}
            </td>

            <td class="px-4 py-4 text-sm align-top">
              <div class="flex items-center gap-3">
                <template v-if="m.image_url">
                  <img
                    :src="m.image_url"
                    alt="thumb"
                    class="w-20 h-14 object-cover rounded-lg border border-gray-200 shadow-sm"
                  />
                </template>

                <template v-else-if="m.video_url">
                  <video
                    class="w-20 h-14 rounded-lg border border-gray-200 shadow-sm object-cover"
                    muted
                    playsinline
                    preload="metadata"
                  >
                    <source :src="m.video_url" />
                  </video>
                </template>

                <template v-else-if="m.video_link">
                  <span class="text-xs text-gray-500 truncate max-w-[14rem]">
                    {{ m.video_link }}
                  </span>
                </template>

                <template v-else>
                  <span class="text-xs text-gray-400">—</span>
                </template>
              </div>
            </td>

            <td class="px-4 py-4 text-sm align-top">
              <div class="flex items-center gap-3">
                <Link
                  :href="`/admin/mentorship/edit/${m.id}`"
                  class="text-blue-600 hover:underline font-medium"
                >
                  Edit
                </Link>

                <button
                  @click="deleteMentorship(m.id)"
                  class="text-red-600 hover:underline font-medium"
                >
                  Delete
                </button>
              </div>
            </td>
          </tr>

          <tr v-if="mentorships.length === 0">
            <td colspan="4" class="px-4 py-8 text-center text-gray-500">
              No mentorship items yet.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
</style>