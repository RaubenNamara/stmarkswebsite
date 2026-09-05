import axios, { type AxiosInstance } from 'axios'

/**
 * The one axios instance for the public site's read-only API calls (GET listings/detail pages,
 * POST form submissions) - see admin-frontend/src/services/api.ts for the sibling app's identical
 * single-export rationale. No CSRF header/auth-redirect interceptor here (unlike admin) - every
 * endpoint under /api/public is either unauthenticated GET or a public form POST with no session.
 */
// In the browser, a relative baseURL resolves against the current page's origin - fine. During
// the SSG build (Node, no page/origin to resolve against), axios's Node http adapter needs an
// absolute URL, so build time talks to the actual running backend directly via SSG_API_BASE.
const apiBase = import.meta.env.SSR
  ? (process.env.SSG_API_BASE ?? 'http://localhost/stmarkswebsite/api/public')
  : import.meta.env.BASE_URL.replace(/\/?$/, '') + '/api/public'

export const api: AxiosInstance = axios.create({
  baseURL: apiBase,
  timeout: 30000,
  withCredentials: true,
  headers: {
    Accept: 'application/json',
  },
})

export function apiErrorMessage(error: unknown, fallback = 'Something went wrong'): string {
  if (axios.isAxiosError(error)) {
    return error.response?.data?.message ?? error.message ?? fallback
  }
  return fallback
}

export function apiFieldErrors(error: unknown): Record<string, string> {
  if (axios.isAxiosError(error) && error.response?.data?.errors) {
    return error.response.data.errors as Record<string, string>
  }
  return {}
}
