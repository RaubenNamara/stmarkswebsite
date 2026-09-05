import { fileURLToPath, URL } from 'node:url'
import vue from '@vitejs/plugin-vue'
import { defineConfig } from 'vite'
import axios from 'axios'

const SSG_API_BASE = process.env.SSG_API_BASE ?? 'http://localhost/stmarkswebsite/api/public'

/** Enumerates every dynamic slug so vite-ssg prerenders a real static page per article/item, not just the static routes it can already see. */
async function includedRoutes(paths: string[]): Promise<string[]> {
  const [news, posts, campusVoices, clubs] = await Promise.all([
    axios.get(`${SSG_API_BASE}/news`, { params: { limit: 1000 } }).then((r) => r.data.data.news),
    axios.get(`${SSG_API_BASE}/posts`).then((r) => r.data.data.posts),
    axios.get(`${SSG_API_BASE}/campus-voices`).then((r) => r.data.data.articles),
    axios.get(`${SSG_API_BASE}/clubs`).then((r) => r.data.data.clubs),
  ])

  const dynamic = [
    ...news.map((n: { slug: string }) => `/news/${n.slug}`),
    ...posts.map((p: { slug: string }) => `/posts/${p.slug}`),
    ...campusVoices.map((a: { slug: string }) => `/campus-voices/${a.slug}`),
    ...clubs.map((c: { slug: string }) => `/clubs/${c.slug}`),
  ]

  // Drop the raw dynamic-pattern paths (e.g. "/news/:slug") vite-ssg detects from the route table -
  // only the real per-item paths resolved above should actually be rendered.
  const staticPaths = paths.filter((p) => !p.includes(':'))

  return [...staticPaths, ...dynamic]
}

// Deployed at the project-root domain (unlike admin-frontend's /admin/ subpath) - locally
// everything still lives under /stmarkswebsite/, matching the rest of this project's local
// convention (PUBLIC_SITE_BASE_PATH etc.); becomes '/' at the real production domain-root cutover.
export default defineConfig({
  base: '/stmarkswebsite/',
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    proxy: {
      '/stmarkswebsite/api': {
        target: 'http://localhost/stmarkswebsite/backend/public',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/stmarkswebsite\/api/, '/api'),
      },
    },
  },
  ssgOptions: {
    script: 'async',
    formatting: 'minify',
    includedRoutes,
  },
})
