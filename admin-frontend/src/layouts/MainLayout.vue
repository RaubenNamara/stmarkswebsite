<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { navItems, type NavItem } from '@/router/navItems'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()
const mobileOpen = ref(false)

function isActive(item: NavItem): boolean {
  return route.path === item.path
}

const pageTitle = computed(() => {
  if (route.name === 'ComingSoon') {
    return navItems.find((item) => item.key === route.params.key)?.label ?? 'Coming soon'
  }
  return (route.meta.title as string | undefined) ?? 'Dashboard'
})

async function handleLogout() {
  await auth.logout()
  router.push({ name: 'Login' })
}
</script>

<template>
  <div class="min-h-screen flex bg-gray-100 text-gray-900">
    <transition name="fade">
      <div v-if="mobileOpen" class="fixed inset-0 z-30 bg-black/40 lg:hidden" @click="mobileOpen = false" />
    </transition>

    <aside
      :class="[
        'z-40 fixed inset-y-0 left-0 transform w-64 bg-blue-950 text-white flex flex-col shadow-lg transition-transform duration-300',
        mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
      ]"
    >
      <div class="px-6 py-6 border-b border-blue-800">
        <h1 class="text-2xl font-extrabold tracking-tight">Admin Panel</h1>
        <p class="text-sm text-blue-300 mt-1">St. Mark's College</p>
      </div>

      <nav class="flex-1 px-3 py-6 overflow-y-auto space-y-2">
        <p class="px-3 text-xs font-semibold text-blue-300 uppercase tracking-wide">Main</p>

        <router-link
          v-for="item in navItems"
          :key="item.key"
          :to="item.path"
          :class="[
            'flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-150',
            isActive(item) ? 'bg-blue-800 text-white shadow' : 'text-blue-200 hover:bg-blue-900 hover:text-white',
          ]"
        >
          <span class="w-6 h-6 flex items-center justify-center">{{ item.icon }}</span>
          <span class="truncate">{{ item.label }}</span>
        </router-link>
      </nav>

      <div class="p-4 border-t border-blue-800">
        <button
          type="button"
          class="w-full bg-red-600 hover:bg-red-700 py-2 rounded-lg text-white font-semibold"
          @click="handleLogout"
        >
          Logout
        </button>
      </div>
    </aside>

    <div class="flex-1 lg:pl-64 min-h-screen flex flex-col">
      <header class="w-full bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 flex items-center h-16 gap-4">
          <button
            class="lg:hidden p-2 rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200"
            type="button"
            aria-label="Toggle navigation menu"
            :aria-expanded="mobileOpen"
            @click="mobileOpen = !mobileOpen"
          >
            ☰
          </button>

          <div class="flex-1 flex items-center gap-4">
            <h2 class="text-lg font-semibold text-gray-800">{{ pageTitle }}</h2>
          </div>

          <div class="hidden sm:block text-sm text-gray-600">
            Signed in as <span class="font-medium text-gray-800">{{ auth.user?.name }}</span>
          </div>
        </div>
      </header>

      <main class="flex-1 bg-gray-50">
        <router-view />
      </main>
    </div>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
