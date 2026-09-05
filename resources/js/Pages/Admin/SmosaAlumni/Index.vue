<script setup>
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({
  layout: AdminLayout
})

const props = defineProps({
  alumni: {
    type: Array,
    default: () => []
  }
})

function remove(id) {
  if (!confirm('Delete this alumni?')) return
  router.delete(route('admin.smosa.destroy', id))
}
</script>

<template>

<div>

<div class="flex justify-between items-center mb-6">
<h1 class="text-2xl font-bold">SMOSA Alumni</h1>

<Link
:href="route('admin.smosa.create')"
class="bg-blue-600 text-white px-4 py-2 rounded"
>
Add Alumni
</Link>

</div>

<table class="w-full bg-white shadow rounded">

<thead class="bg-gray-100">
<tr>
<th class="p-3 text-left">Photo</th>
<th class="p-3 text-left">Name</th>
<th class="p-3 text-left">Profession</th>
<th class="p-3 text-right">Actions</th>
</tr>
</thead>

<tbody>

<tr v-if="!alumni.length">
<td colspan="4" class="p-6 text-center text-gray-500">
No alumni yet
</td>
</tr>

<tr v-for="a in alumni" :key="a.id" class="border-t">

<!-- ✅ FIXED PHOTO -->
<td class="p-3">
<img
v-if="a.photo_url"
:src="a.photo_url"
alt=""
class="w-16 h-12 object-cover"
/>
</td>

<td class="p-3">{{ a.name }}</td>
<td class="p-3">{{ a.profession }}</td>

<td class="p-3 text-right">

<Link
:href="route('admin.smosa.show', a.id)"
class="text-gray-700 mr-3"
>
View
</Link>

<Link
:href="route('admin.smosa.edit', a.id)"
class="text-blue-600 mr-3"
>
Edit
</Link>

<button
@click="remove(a.id)"
class="text-red-600"
>
Delete
</button>

</td>

</tr>

</tbody>

</table>

</div>

</template>