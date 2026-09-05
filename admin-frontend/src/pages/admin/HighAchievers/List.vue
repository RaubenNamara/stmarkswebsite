<script setup lang="ts">
import SinglePhotoCrudPage from '@/components/admin/SinglePhotoCrudPage.vue'
</script>

<template>
  <SinglePhotoCrudPage
    page-title="High Achievers"
    endpoint="/admin/high-achievers"
    list-key="high_achievers"
    create-label="Add High Achiever"
    title-field="name"
    :fields="[
      { name: 'name', label: 'Name', required: true },
      { name: 'year', label: 'Year', required: true },
      { name: 'exam', label: 'Exam', required: true },
      { name: 'division', label: 'Division' },
      { name: 'description', label: 'Description', type: 'textarea' },
    ]"
  />
</template>
