import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { api, apiErrorMessage } from '@/services/api'
import type { ApiResponse, User } from '@/types'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => user.value !== null)

  function persist(newUser: User, csrfToken: string): void {
    user.value = newUser
    sessionStorage.setItem('user', JSON.stringify(newUser))
    sessionStorage.setItem('csrf_token', csrfToken)
  }

  function clear(): void {
    user.value = null
    sessionStorage.removeItem('user')
    sessionStorage.removeItem('csrf_token')
  }

  /** Rehydrate from sessionStorage on a hard refresh, before the router guard resolves. */
  function restoreFromStorage(): void {
    const raw = sessionStorage.getItem('user')
    if (!raw) return
    try {
      user.value = JSON.parse(raw) as User
    } catch {
      clear()
    }
  }

  async function login(email: string, password: string): Promise<boolean> {
    isLoading.value = true
    error.value = null
    try {
      const { data } = await api.post<ApiResponse<{ user: User; csrf_token: string }>>('/auth/login', {
        email,
        password,
      })
      if (!data.success || !data.data) {
        error.value = data.message
        return false
      }
      persist(data.data.user, data.data.csrf_token)
      return true
    } catch (e) {
      error.value = apiErrorMessage(e, 'Login failed')
      return false
    } finally {
      isLoading.value = false
    }
  }

  async function logout(): Promise<void> {
    try {
      await api.post('/auth/logout')
    } catch {
      // Clear local state regardless of whether the server call succeeded.
    }
    clear()
  }

  /** Revalidate the session with the server (e.g. on app boot). */
  async function fetchMe(): Promise<boolean> {
    try {
      const { data } = await api.get<ApiResponse<{ user: User }>>('/auth/me')
      if (data.success && data.data) {
        user.value = data.data.user
        sessionStorage.setItem('user', JSON.stringify(data.data.user))
        return true
      }
    } catch {
      // Not logged in / session expired.
    }
    clear()
    return false
  }

  return { user, isAuthenticated, isLoading, error, restoreFromStorage, login, logout, fetchMe }
})
