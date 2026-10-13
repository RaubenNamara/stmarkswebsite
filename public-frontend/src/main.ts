import { ViteSSG } from 'vite-ssg'
import { createPinia } from 'pinia'
import App from './App.vue'
import { routes } from './router'
import { vReveal } from './directives/reveal'
import { trackPageViews } from './utils/trackPageView'
// Self-hosted font (no request to Google): Plus Jakarta Sans, a modern geometric sans, for all text.
import '@fontsource-variable/plus-jakarta-sans/wght.css'
import './style.css'

// vite-ssg ships @unhead/vue v2 internally - useHead() in components just works, no manual
// createHead()/app.use() needed (see vite-ssg's README "Document head" section).
//
// base MUST be set explicitly - vue-router defaults to '/' otherwise, which is wrong here since
// the site is deployed under /stmarkswebsite/ locally (production is domain-root, where
// BASE_URL is '/' anyway, so this stays correct at cutover too). Without this every
// <router-link> renders as e.g. href="/about" instead of "/stmarkswebsite/about", sending every
// in-app navigation to the wrong place entirely (a different app/directory outside this project).
export const createApp = ViteSSG(
  App,
  {
    routes,
    base: import.meta.env.BASE_URL,
    // Lets links like /staff#staff-42 (the Home page's team cards) land scrolled to that
    // element instead of just the top of the page - offset clears the sticky header (h-14/h-16
    // logo plus padding, so header runs up to ~90px tall on wide screens).
    //
    // The target route's data loads via a top-level await inside <Suspense> (see App.vue), so
    // right after navigation the #staff-N element usually doesn't exist yet - vue-router's one-
    // shot `el` lookup then fails silently and the browser just keeps the previous page's scroll
    // position, which on a much longer page like Home overshoots straight to the bottom of the
    // shorter destination page. Polling until the element actually appears (or giving up after
    // ~4s and scrolling to top instead of leaving that stale position) fixes both.
    scrollBehavior(to, from, savedPosition) {
      if (savedPosition) return savedPosition
      if (to.hash) {
        return new Promise((resolve) => {
          let attempts = 0
          const poll = () => {
            if (document.querySelector(to.hash)) {
              resolve({ el: to.hash, top: 100, behavior: 'smooth' })
            } else if (attempts++ < 40) {
              setTimeout(poll, 100)
            } else {
              resolve({ top: 0 })
            }
          }
          poll()
        })
      }
      return { top: 0 }
    },
  },
  ({ app, router }) => {
    app.use(createPinia())
    app.directive('reveal', vReveal)
    if (!import.meta.env.SSR) trackPageViews(router)
  },
)
