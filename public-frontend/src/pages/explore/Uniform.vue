<script setup lang="ts">
import { useHead } from '@unhead/vue'
import PageHeader from '../../components/PageHeader.vue'

useHead({ title: 'Uniform' })
</script>

<template>
  <PageHeader title="School Uniform" subtitle="Our uniform reflects discipline, identity, and excellence." />

  <section class="mx-auto max-w-6xl space-y-6 px-6 py-14">
    <div class="card">
      <h2 class="text-xl font-bold text-gray-900">Academic Uniform</h2>
      <div class="mt-6 grid gap-6 sm:grid-cols-3">
        <div><h3 class="font-semibold text-gray-900">O-Level Students</h3><ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-gray-600"><li>Cream Shirts / Blouses</li><li>Navy Blue Trousers / Skirts</li></ul></div>
        <div><h3 class="font-semibold text-gray-900">A-Level Students</h3><ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-gray-600"><li>Blue Shirts / Blouses</li><li>Navy Blue Trousers / Skirts</li></ul></div>
        <div><h3 class="font-semibold text-gray-900">School Sweater</h3><ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-gray-600"><li>Navy Blue Sweater</li><li>Light Blue Stripes at the Neck and Sleeves</li></ul></div>
      </div>
    </div>

    <div class="card">
      <h2 class="text-xl font-bold text-gray-900">Out-of-Class Uniform</h2>
      <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-gray-600">
        <li>Plain Red T-Shirts (Different colors per class)</li>
        <li>Black Skirts / Trousers</li>
        <li>Students should bring only skirts/trousers — strict adherence required</li>
      </ul>
    </div>

    <div class="alert alert-error">
      <strong>Important Notice:</strong> No other form of dressing is allowed in school apart from that indicated above.
    </div>

    <div class="card">
      <h2 class="text-xl font-bold text-gray-900">Uniform Package (Available at School)</h2>
      <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-gray-600">
        <li>School Sweater</li>
        <li>2 Shirts / Blouses</li>
        <li>2 Trousers / Skirts</li>
        <li>Sunday Wear</li>
        <li>Sports Wear</li>
        <li>3 T-Shirts for Out-of-Class Wear</li>
      </ul>
    </div>
  </section>
</template>
