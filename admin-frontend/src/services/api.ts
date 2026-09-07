import axios, { type AxiosInstance } from 'axios'

/**
 * The one axios instance the whole app uses - a single named export, imported everywhere,
 * so interceptors are never silently bypassed (unlike eSpace's services/api.ts, which exports
 * both a `default` raw axios instance and a named `apiService` wrapper under confusingly similar
 * names, and where ~50 pages bypass both entirely by importing raw axios directly).
 *
 * Auth is a PHP session cookie (withCredentials) plus a CSRF token header on mutating requests -
 * no bearer-token code path, since eSpace's exists but is dead (nothing ever sets the
 * localStorage key its interceptor reads).
 */
// Derived from BASE_URL rather than hardcoded '/api', so the same build works whether the app is
// deployed at domain root (BASE_URL '/admin/' -> '/api') or under a subdirectory during the
// parallel-build period (BASE_URL '/stmarkswebsite/admin/' -> '/stmarkswebsite/api') - matches
// wherever the backend actually landed alongside it, without a rebuild-time env var to keep in sync.
const apiBase = import.meta.env.BASE_URL.replace(/admin\/?$/, 'api')

// No default Content-Type header here (unlike an earlier version of this file) - axios already
// sets 'application/json' on its own for plain object bodies (login, logout, etc.), and presetting
// it globally broke every FormData upload across the app: axios's transformRequest special-cases
// a FormData body by JSON.stringify-ing it instead of sending it as multipart/form-data whenever
// the request already has a JSON content type, so PHP never saw the uploaded file at all.
export const api: AxiosInstance = axios.create({
  baseURL: apiBase,
  timeout: 30000,
  withCredentials: true,
  headers: {
    Accept: 'application/json',
  },
})

api.interceptors.request.use((config) => {
  const csrfToken = sessionStorage.getItem('csrf_token')
  if (csrfToken && config.headers) {
    config.headers['X-CSRF-Token'] = csrfToken
  }
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401 && !window.location.pathname.endsWith('/login')) {
      sessionStorage.removeItem('csrf_token')
      window.location.href = `${import.meta.env.BASE_URL}login`
    }
    return Promise.reject(error)
  },
)

export function apiErrorMessage(error: unknown, fallback = 'Something went wrong'): string {
  if (axios.isAxiosError(error)) {
    return error.response?.data?.message ?? error.message ?? fallback
  }
  return fallback
}
