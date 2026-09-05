import { fileURLToPath, URL } from 'node:url'
import vue from '@vitejs/plugin-vue'
import { defineConfig } from 'vite'

// Deployed at same-origin /admin/ at cutover (see the plan's decision to avoid eSpace's
// base:'/eSpace/' subpath complexity beyond what's actually needed) - the dev server mirrors that
// with a base path plus a proxy so the browser only ever talks to one origin, no CORS needed even
// in local dev.
export default defineConfig({
  base: '/admin/',
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost/stmarkswebsite/backend/public',
        changeOrigin: true,
      },
    },
  },
})
