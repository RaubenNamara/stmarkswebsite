<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useForm, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

defineOptions({
  layout: AdminLayout
})

const props = defineProps({
  members: {
    type: Array,
    default: () => []
  }
})

/*
|--------------------------------------------------------------------------|
| Sort members so the first-ever (smallest id) is always first             |
|--------------------------------------------------------------------------|
*/
const sortedMembers = computed(() => {
  // defensive: ensure we have plain objects and avoid mutating props
  return [...props.members].sort((a, b) => {
    // handle missing id gracefully
    const ai = Number(a?.id ?? Infinity)
    const bi = Number(b?.id ?? Infinity)
    return ai - bi
  })
})

/*
|--------------------------------------------------------------------------|
| Form (Create)
|--------------------------------------------------------------------------|
*/
const form = useForm({
  name: '',
  position: '',
  photo: null
})

function submit() {
  form.post(route('admin.board-members.store'), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => form.reset()
  })
}

function onFileChange(e) {
  form.photo = e.target.files?.[0] ?? null
}

/*
|--------------------------------------------------------------------------|
| Delete
|--------------------------------------------------------------------------|
*/
const deletingId = ref(null)

function deleteMember(member) {
  if (!confirm(`Delete ${member.name}?`)) return

  // mark deleting id (use .value in script)
  deletingId.value = member.id

  router.delete(route('admin.board-members.destroy', member.id), {
    preserveScroll: true,
    onFinish: () => (deletingId.value = null)
  })
}
</script>

<template>
  <div class="p-8">

    <!-- Page Title -->
    <h2 class="text-2xl font-semibold mb-6">
      Manage Board Members
    </h2>

    <!-- Create Form -->
    <form
      @submit.prevent="submit"
      class="bg-white p-6 rounded shadow mb-10 space-y-4 max-w-md"
      enctype="multipart/form-data"
    >
      <div>
        <label class="block text-sm font-medium mb-1">Full Name</label>
        <input
          v-model="form.name"
          type="text"
          class="border p-2 w-full rounded"
          required
        />
        <p v-if="form.errors.name" class="text-red-500 text-sm">
          {{ form.errors.name }}
        </p>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Position</label>
        <input
          v-model="form.position"
          type="text"
          class="border p-2 w-full rounded"
          required
        />
        <p v-if="form.errors.position" class="text-red-500 text-sm">
          {{ form.errors.position }}
        </p>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Photo</label>
        <input
          type="file"
          accept="image/*"
          @change="onFileChange"
        />
        <p v-if="form.errors.photo" class="text-red-500 text-sm">
          {{ form.errors.photo }}
        </p>
      </div>

      <button
        type="submit"
        :disabled="form.processing"
        class="bg-blue-900 text-white px-4 py-2 rounded hover:bg-blue-800"
      >
        <span v-if="form.processing">Saving...</span>
        <span v-else>Add Member</span>
      </button>
    </form>

    <!-- Members List -->
    <div class="grid md:grid-cols-3 gap-6">
      <!-- iterate sortedMembers, so smallest id (first entered) appears first -->
      <div
        v-for="member in sortedMembers"
        :key="member.id"
        class="bg-white p-6 rounded-xl shadow text-center"
      >
        <img
          v-if="member.photo"
          :src="`/storage/${member.photo}`"
          class="w-24 h-24 rounded-full object-cover mx-auto mb-4"
          alt="Member photo"
        />

        <h3 class="font-bold text-gray-800">
          {{ member.name }}
        </h3>

        <p class="text-gray-500 mb-4">
          {{ member.position }}
        </p>

        <div class="flex justify-center gap-3">
          <Link
            :href="route('admin.board-members.edit', member.id)"
            class="px-3 py-1 border border-blue-800 text-blue-800 rounded text-sm"
          >
            Edit
          </Link>

          <button
            @click="deleteMember(member)"
            class="px-3 py-1 bg-red-600 text-white rounded text-sm"
            :disabled="deletingId === member.id"
          >
            <!-- template auto-unwraps ref so comparing deletingId works -->
            <span v-if="deletingId === member.id">Deleting…</span>
            <span v-else>Delete</span>
          </button>
        </div>

      </div>
    </div>

  </div>
</template>

<style scoped>
/* keep your styles or paste your premium styles here */
</style>