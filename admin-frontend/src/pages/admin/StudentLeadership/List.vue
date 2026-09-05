<script setup lang="ts">
import ImageVideoCrudPage from '@/components/admin/ImageVideoCrudPage.vue'
</script>

<template>
  <ImageVideoCrudPage
    page-title="Student Leadership"
    endpoint="/admin/student-leadership"
    list-key="student_leadership"
    create-label="Add Student Leader"
    image-field="image_path"
    video-field="video_path"
    :fields="[
      { name: 'content', label: 'Content', type: 'textarea' },
      { name: 'video_link', label: 'Video Link (URL)' },
    ]"
  />
</template>
