import { ViteSSG } from 'vite-ssg'
import { createPinia } from 'pinia'
import App from './App.vue'
import { routes } from './router'
import './style.css'

// vite-ssg ships @unhead/vue v2 internally - useHead() in components just works, no manual
// createHead()/app.use() needed (see vite-ssg's README "Document head" section).
//
// base MUST be set explicitly - vue-router defaults to '/' otherwise, which is wrong here since
// the site is deployed under /stmarkswebsite/ locally (production is domain-root, where
// BASE_URL is '/' anyway, so this stays correct at cutover too). Without this every
// <router-link> renders as e.g. href="/about" instead of "/stmarkswebsite/about", sending every
// in-app navigation to the wrong place entirely (a different app/directory outside this project).
export const createApp = ViteSSG(App, { routes, base: import.meta.env.BASE_URL }, ({ app }) => {
  app.use(createPinia())
})
