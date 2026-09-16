import { fileURLToPath, URL } from 'node:url'
import vue from '@vitejs/plugin-vue'
import { defineConfig } from 'vite'

// Deployed under the project subdirectory (http://localhost/stmarkswebsite/admin/) locally,
// matching where the root .htaccess's `admin` rewrite points and public-frontend's own
// base:'/stmarkswebsite/' - the dev server mirrors that with a base path plus a proxy so the
// browser only ever talks to one origin, no CORS needed even in local dev.
//
// Overridable via VITE_BASE at build time for a real production deploy at a domain root (cPanel
// etc.), where the admin SPA instead lives at /admin/ with nothing before it - e.g.
// `VITE_BASE=/admin/ npm run build`. api.ts derives its own API base from this same value, so
// setting it here is the only change a root deploy needs on the admin-frontend side.
export default defineConfig({
  base: process.env.VITE_BASE ?? '/stmarkswebsite/admin/',
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    proxy: {
      // api.ts computes its base from BASE_URL, so with base:'/stmarkswebsite/admin/' the app
      // actually requests '/stmarkswebsite/api/...', not bare '/api/...' - matching only the
      // latter here meant every request during `npm run dev` missed this proxy entirely and hit
      // Vite's own dev server instead, which has no route for it. Mirrors public-frontend's
      // vite.config.ts, which already gets this right.
      '/stmarkswebsite/api': {
        target: 'http://localhost/stmarkswebsite/backend/public',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/stmarkswebsite\/api/, '/api'),
      },
      // Assets::resolve() prefixes uploaded file URLs with PUBLIC_SITE_BASE_PATH too (same reason
      // as the api proxy above) - without this, every photo/PDF the admin panel displays 404s
      // during `npm run dev`. Proxied straight to the project root (not backend/public) since
      // that's where the root .htaccess's real `uploads` rewrite lives.
      '/stmarkswebsite/uploads': {
        target: 'http://localhost',
        changeOrigin: true,
      },
    },
  },
})
