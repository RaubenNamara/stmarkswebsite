import type { Router } from 'vue-router'
import { api } from '../services/api'

/**
 * Groups views by top-level path segment (e.g. every /news/:slug counts under 'news'), matching
 * the page_type convention the legacy site's already-imported analytics rows use - so the admin
 * Analytics report has one consistent breakdown instead of two conventions mixed together.
 */
function deriveType(path: string): string {
  const segment = path.split('/').filter(Boolean)[0]
  return segment || 'home'
}

/** Fires a beacon to /api/public/page-view on every client-side navigation, powering the admin
 * Analytics report. Call once, client-side only - SSG's build-time route rendering has no real
 * visitor behind it, so tracking there would poison the counts with one row per generated page. */
export function trackPageViews(router: Router): void {
  router.afterEach((to) => {
    api
      .post('/page-view', {
        page_url: window.location.href,
        page_type: deriveType(to.path),
      })
      .catch(() => {
        // Best-effort hit counter - a failed beacon shouldn't affect the visitor's experience.
      })
  })
}
