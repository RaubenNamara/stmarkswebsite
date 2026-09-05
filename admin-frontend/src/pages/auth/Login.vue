<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()

const email = ref('')
const password = ref('')

async function handleSubmit() {
  const ok = await auth.login(email.value, password.value)
  if (ok) {
    router.push({ path: '/dashboard' })
  }
}
</script>

<template>
  <div>
    <h1 class="text-xl font-semibold text-slate-900">Admin sign in</h1>
    <p class="mt-1 text-sm text-slate-500">St Mark's College Namagoma</p>

    <form class="mt-6 space-y-4" @submit.prevent="handleSubmit">
      <div v-if="auth.error" class="rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">
        {{ auth.error }}
      </div>

      <div>
        <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
        <input
          id="email"
          v-model="email"
          type="email"
          required
          class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none"
        />
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
        <input
          id="password"
          v-model="password"
          type="password"
          required
          class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none"
        />
      </div>

      <button
        type="submit"
        :disabled="auth.isLoading"
        class="w-full rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 disabled:opacity-50"
      >
        {{ auth.isLoading ? 'Signing in…' : 'Sign in' }}
      </button>
    </form>
  </div>
</template>
