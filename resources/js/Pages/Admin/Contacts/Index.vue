
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

defineOptions({
layout: AdminLayout
})

const props = defineProps({
contacts: Array,
pageTitle: String
})

const viewing = ref(null)

/* MARK AS READ */
function markRead(id){
router.post(`/admin/contacts/${id}/mark-read`)
}

/* DELETE */
function remove(id){
if(confirm('Delete this message?')){
router.delete(`/admin/contacts/${id}`)
}
}

/* OPEN MESSAGE */
function openMessage(contact){
viewing.value = contact
}

/* CLOSE MODAL */
function closeMessage(){
viewing.value = null
}

/* FORMAT DATE */
function formatDate(date){
if(!date) return ''
return new Date(date).toLocaleString('en-GB',{
day:'numeric',
month:'short',
year:'numeric',
hour:'2-digit',
minute:'2-digit'
})
}
</script>


<template>

<div class="space-y-6">

<!-- PAGE HEADER -->
<div class="flex items-center justify-between">

<h1 class="text-3xl font-bold text-gray-800">
Contact Messages
</h1>

<div class="text-sm text-gray-500">
{{ contacts.length }} Messages
</div>

</div>



<!-- CONTACT TABLE -->

<div class="bg-white shadow-lg rounded-xl overflow-hidden border">

<table class="w-full text-sm">

<thead class="bg-gray-100 text-gray-700 uppercase text-xs">

<tr>
<th class="p-4 text-left">Sender</th>
<th class="p-4 text-left">Email</th>
<th class="p-4 text-left">Message</th>
<th class="p-4 text-left">Date</th>
<th class="p-4 text-left">Actions</th>
</tr>

</thead>


<tbody>

<tr
v-for="c in contacts"
:key="c.id"
class="border-t hover:bg-gray-50 transition"
>

<!-- NAME -->

<td class="p-4">

<div class="flex items-center gap-2">

<span
:class="c.is_read
? 'text-gray-600'
: 'font-semibold text-indigo-700'"
>
{{ c.name }}
</span>

<span
v-if="!c.is_read"
class="text-xs bg-red-600 text-white px-2 py-0.5 rounded"
>
New
</span>

</div>

</td>


<!-- EMAIL -->

<td class="p-4">

<a
:href="'mailto:' + c.email"
class="text-indigo-600 hover:underline"
>
{{ c.email }}
</a>

</td>


<!-- MESSAGE -->

<td class="p-4">

<div class="line-clamp-2 text-gray-700">
{{ c.message }}
</div>

</td>


<!-- DATE -->

<td class="p-4 text-gray-600">
{{ formatDate(c.created_at) }}
</td>


<!-- ACTIONS -->

<td class="p-4 flex gap-2">

<button
v-if="!c.is_read"
@click="markRead(c.id)"
class="px-3 py-1 text-xs bg-green-600 hover:bg-green-700 text-white rounded-md"
>
✓ Read
</button>


<button
@click="openMessage(c)"
class="px-3 py-1 text-xs bg-indigo-600 hover:bg-indigo-700 text-white rounded-md"
>
👁 View
</button>


<button
@click="remove(c.id)"
class="px-3 py-1 text-xs bg-red-600 hover:bg-red-700 text-white rounded-md"
>
🗑 Delete
</button>

</td>

</tr>


<tr v-if="contacts.length === 0">

<td colspan="5" class="p-8 text-center text-gray-500">
No contact messages yet
</td>

</tr>

</tbody>

</table>

</div>



<!-- VIEW MESSAGE MODAL -->

<div
v-if="viewing"
class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
>

<div class="bg-white w-full max-w-2xl rounded-xl shadow-xl p-6 max-h-[90vh] overflow-y-auto">

<div class="flex justify-between items-center mb-4">

<h2 class="text-xl font-bold text-gray-800">
Message from {{ viewing.name }}
</h2>

<button
@click="closeMessage"
class="text-gray-500 hover:text-gray-700"
>
✕
</button>

</div>


<p class="text-sm text-gray-500 mb-2">
{{ viewing.email }}
</p>

<p v-if="viewing.telephone" class="text-sm text-gray-500 mb-4">
{{ viewing.telephone }}
</p>


<div class="bg-gray-50 p-4 rounded-lg text-gray-700 whitespace-pre-wrap break-words">
{{ viewing.message }}
</div>


<div class="flex justify-end mt-6">

<button
@click="closeMessage"
class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md"
>
Close
</button>

</div>

</div>

</div>


</div>

</template>


<style scoped>

.line-clamp-2{
display:-webkit-box;
-webkit-line-clamp:2;
-webkit-box-orient:vertical;
overflow:hidden;
}

</style>
```
