<script setup>
import { computed, ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { router } from '@inertiajs/vue3'

defineOptions({
  layout: AdminLayout
})

const props = defineProps({
  leaders: {
    type: Array,
    default: () => []
  }
})

const search = ref('')
const deletingId = ref(null)

const filteredLeaders = computed(() => {
  const q = search.value.trim().toLowerCase()

  return props.leaders.filter((item) => {
    const title = (item.title || '').toLowerCase()
    const content = (item.content || '').toLowerCase()
    return !q || title.includes(q) || content.includes(q)
  })
})

function mediaUrl(path) {
  if (!path) return ''
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  if (path.startsWith('/')) return path
  return `/storage/${path}`
}

function embedUrl(url) {
  if (!url) return ''

  if (url.includes('/embed/')) return url

  try {
    const parsed = new URL(url)

    if (parsed.hostname.includes('youtu.be')) {
      const id = parsed.pathname.replace('/', '')
      return `https://www.youtube.com/embed/${id}`
    }

    if (parsed.hostname.includes('youtube.com')) {
      const videoId = parsed.searchParams.get('v')
      if (videoId) {
        return `https://www.youtube.com/embed/${videoId}`
      }
    }
  } catch (e) {}

  return url
}

function editItem(item) {
  router.get(route('admin.student-leadership.edit', item.id))
}

function deleteItem(item) {
  if (!confirm('Delete this item?')) return

  deletingId.value = item.id

  router.delete(route('admin.student-leadership.destroy', item.id), {
    preserveScroll: true,
    onFinish: () => {
      deletingId.value = null
    }
  })
}
</script>

<template>
  <div class="p-10">
    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <h2 class="text-3xl font-bold text-gray-800">Student Leadership</h2>
        <p class="text-gray-500 mt-1">
          Manage items with photo first, then article content, then video or link.
        </p>
      </div>

      <button
        @click="router.get(route('admin.student-leadership.create'))"
        class="bg-blue-900 hover:bg-blue-800 text-white px-6 py-2 rounded-lg transition"
      >
        + Add Item
      </button>
    </div>

    <div class="mb-8 bg-white p-6 rounded-2xl shadow-md border">
      <label class="block text-sm font-medium mb-2">Search</label>
      <input
        v-model="search"
        type="text"
        placeholder="Search by title or content..."
        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-900 focus:outline-none"
      />
    </div>

    <div v-if="filteredLeaders.length === 0" class="text-center text-gray-500 mt-10">
      No student leadership items found.
    </div>

    <div v-else class="grid grid-cols-1 gap-8">
      <article
        v-for="item in filteredLeaders"
        :key="item.id"
        class="bg-white shadow-md hover:shadow-xl transition border overflow-hidden"
      >
        <div class="bg-gray-100">

  <!-- VIDEO FILE -->
  <video
    v-if="item.video_path"
    controls
    class="w-full h-72 object-cover bg-black"
  >
    <source :src="mediaUrl(item.video_path)" />
  </video>

  <!-- YOUTUBE LINK -->
  <iframe
    v-else-if="item.video_link"
    :src="embedUrl(item.video_link)"
    class="w-full h-72 border-0 bg-black"
    allowfullscreen
  ></iframe>

  <!-- IMAGE -->
  <img
    v-else-if="item.image_path"
    :src="mediaUrl(item.image_path)"
    :alt="item.title"
    class="w-full h-72 object-cover"
  />

  <!-- NOTHING -->
  <div
    v-else
    class="w-full h-72 flex items-center justify-center text-gray-400"
  >
    No media
  </div>

</div>

        <div class="p-6">
          <h3 class="text-2xl font-bold text-gray-800">
            {{ item.title }}
          </h3>

          <p v-if="item.content" class="mt-4 text-gray-700 leading-relaxed">
            {{ item.content }}
          </p>

          <div v-if="item.video_path" class="mt-5">
            <video controls class="w-full rounded-xl bg-black">
              <source :src="mediaUrl(item.video_path)" />
              Your browser does not support the video tag.
            </video>
          </div>

          <div v-if="item.video_link" class="mt-5">
            <iframe
              :src="embedUrl(item.video_link)"
              class="w-full h-56 rounded-xl border-0"
              title="Student leadership video"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowfullscreen
            ></iframe>
          </div>

          <div class="mt-6 flex justify-center gap-3">
            <button
              @click.prevent="editItem(item)"
              class="px-3 py-1 rounded-md border border-blue-800 text-blue-800 text-sm hover:bg-blue-50"
            >
              Edit
            </button>

            <button
              @click.prevent="deleteItem(item)"
              class="px-3 py-1 rounded-md bg-red-600 text-white text-sm hover:bg-red-700"
              :disabled="deletingId === item.id"
            >
              <span v-if="deletingId === item.id">Deleting…</span>
              <span v-else>Delete</span>
            </button>
          </div>
        </div>
      </article>
    </div>
  </div>
</template>

<style scoped>
button[disabled] {
  cursor: not-allowed;
  opacity: 0.6;
}
</style>