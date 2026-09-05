import { ViteSSG } from 'vite-ssg'
import { createPinia } from 'pinia'
import App from './App.vue'
import { routes } from './router'
import './style.css'

// vite-ssg ships @unhead/vue v2 internally - useHead() in components just works, no manual
// createHead()/app.use() needed (see vite-ssg's README "Document head" section).
export const createApp = ViteSSG(App, { routes }, ({ app }) => {
  app.use(createPinia())
})
