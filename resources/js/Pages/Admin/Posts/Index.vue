<script setup>
import { usePage, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
defineOptions({ layout: AdminLayout })

/*
|--------------------------------------------------------------------------
| Get Posts From Inertia
|--------------------------------------------------------------------------
*/
const page = usePage()

// Make posts reactive so we can modify them instantly
const posts = ref(page.props.posts || [])

/*
|--------------------------------------------------------------------------
| Delete Post (Instant UI Update)
|--------------------------------------------------------------------------
*/
function deletePost(id) {
  if (!confirm('Are you sure you want to delete this post?')) return

  router.delete(route('admin.posts.destroy', id), {
    preserveScroll: true,
    onSuccess: () => {
      // Remove deleted post instantly from UI
      posts.value = posts.value.filter(post => post.id !== id)
    }
  })
}
</script>

<template>
  <div class="p-8">

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Posts</h1>

      <Link
        :href="route('admin.posts.create')"
        class="bg-blue-900 text-white px-4 py-2 rounded hover:bg-blue-800"
      >
        + Create Post
      </Link>
    </div>

    <!-- Posts Table -->
    <div class="bg-white rounded shadow overflow-hidden">
      <table class="w-full">
        
        <!-- Table Header -->
        <thead class="bg-gray-100">
          <tr>
            <th class="text-left p-3">Title</th>
            <th class="text-left p-3">Created</th>
            <th class="text-left p-3">Actions</th>
          </tr>
        </thead>

        <!-- Table Body -->
        <tbody>
          <tr
            v-for="post in posts"
            :key="post.id"
            class="border-t hover:bg-gray-50 transition"
          >
            
            <!-- Title -->
            <td class="p-3">
              {{ post.title }}
            </td>

            <!-- Date -->
            <td class="p-3 text-sm text-gray-500">
              {{ post.created_at }}
            </td>

            <!-- Actions -->
            <td class="p-3 flex gap-2">
              
              <!-- Edit -->
              <Link
                :href="route('admin.posts.edit', post.id)"
                class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600"
              >
                Edit
              </Link>

              <!-- Delete -->
              <button
                @click="deletePost(post.id)"
                class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700"
              >
                Delete
              </button>

            </td>
          </tr>

          <!-- Empty State -->
          <tr v-if="posts.length === 0">
            <td colspan="3" class="p-4 text-center text-gray-500">
              No posts found.
            </td>
          </tr>

        </tbody>
      </table>
    </div>

  </div>
</template>