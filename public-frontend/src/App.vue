<script setup lang="ts">
import { defineAsyncComponent, onMounted, ref } from 'vue'
import { useHead } from '@unhead/vue'
import AppHeader from './components/AppHeader.vue'
import AppFooter from './components/AppFooter.vue'
import PostsTicker from './components/PostsTicker.vue'
import WhatsAppFloat from './components/WhatsAppFloat.vue'

// School assistant: loaded as a separate chunk and only mounted in the browser once the page is
// idle, so it adds nothing to the prerendered HTML or to the page's first paint.
const ChatbotWidget = defineAsyncComponent(() => import('./components/chatbot/ChatbotWidget.vue'))
const showChatbot = ref(false)
onMounted(() => {
  const mount = () => { showChatbot.value = true }
  if ('requestIdleCallback' in window) window.requestIdleCallback(mount, { timeout: 3000 })
  else setTimeout(mount, 1500)
})

useHead({
  titleTemplate: (title) => (title ? `${title} - St. Mark's College Namagoma` : "St. Mark's College Namagoma"),
})
</script>

<template>
  <!-- overflow-x-clip (not hidden: that would break the sticky header) stops off-screen entrance
       animations, e.g. Home's School Leadership cards, from widening the page on phones - which
       would push every fixed bottom-right button (chat assistant, WhatsApp) partly off-screen. -->
  <div class="flex min-h-screen flex-col overflow-x-clip bg-gray-50 text-gray-900">
    <AppHeader />
    <Suspense>
      <PostsTicker />
    </Suspense>
    <main class="flex-1">
      <router-view v-slot="{ Component, route }">
        <Suspense>
          <component :is="Component" :key="route.fullPath" />
          <template #fallback>
            <div class="flex min-h-[70vh] items-center justify-center">
              <div class="h-10 w-10 animate-spin rounded-full border-4 border-brand-navy/20 border-t-brand-navy" />
            </div>
          </template>
        </Suspense>
      </router-view>
    </main>
    <AppFooter />
    <WhatsAppFloat />
    <ChatbotWidget v-if="showChatbot" />
  </div>
</template>
