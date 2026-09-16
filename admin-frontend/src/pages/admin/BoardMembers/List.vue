<script setup lang="ts">
import SinglePhotoCrudPage from '@/components/admin/SinglePhotoCrudPage.vue'
</script>

<template>
  <SinglePhotoCrudPage
    page-title="Board Members"
    endpoint="/admin/board-members"
    list-key="board_members"
    create-label="Add Board Member"
    title-field="name"
    subtitle-field="position"
    :reorderable="true"
    :fields="[
      { name: 'name', label: 'Name', required: true },
      { name: 'position', label: 'Position', required: true },
    ]"
  />
</template>
