import { fileURLToPath, URL } from 'node:url'
import vue from '@vitejs/plugin-vue'
import { defineConfig } from 'vite'

// Deployed under the project subdirectory (http://localhost/stmarkswebsite/admin/), matching
// where the root .htaccess's `admin` junction actually points and public-frontend's own
// base:'/stmarkswebsite/' - the dev server mirrors that with a base path plus a proxy so the
// browser only ever talks to one origin, no CORS needed even in local dev.
export default defineConfig({
  base: '/stmarkswebsite/admin/',
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
