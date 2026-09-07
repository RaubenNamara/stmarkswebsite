<script setup lang="ts">
import { useHead } from '@unhead/vue'
import AppHeader from './components/AppHeader.vue'
import AppFooter from './components/AppFooter.vue'
import PostsTicker from './components/PostsTicker.vue'

useHead({
  titleTemplate: (title) => (title ? `${title} - St Mark's College Namagoma` : "St Mark's College Namagoma"),
})
</script>

<template>
  <div class="flex min-h-screen flex-col bg-gray-50 text-gray-900">
    <AppHeader />
    <Suspense>
      <PostsTicker />
    </Suspense>
    <main class="flex-1">
      <router-view v-slot="{ Component, route }">
        <Suspense>
          <component :is="Component" :key="route.fullPath" />
        </Suspense>
      </router-view>
    </main>
    <AppFooter />
  </div>
</template>
