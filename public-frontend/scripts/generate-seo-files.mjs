// Post-build step: writes dist/robots.txt and dist/sitemap.xml as build artifacts (replaces
// public-site's SitemapController, which generated the same thing on the fly per-request - here
// it's baked in at build time instead, refreshed on every rebuild so new content still appears).
import { writeFileSync } from 'node:fs'
import { resolve } from 'node:path'
import axios from 'axios'

const SSG_API_BASE = process.env.SSG_API_BASE ?? 'http://localhost/stmarkswebsite/api/public'
const distDir = resolve(import.meta.dirname, '..', 'dist')

const STATIC_PATHS = [
  '/', '/academics', '/academics/curriculum', '/academics/uneb-results',
  '/academics/circulars', '/academics/school-calendar', '/academics/co-curricular',
  '/academics/high-achievers', '/admissions', '/staff', '/board-members', '/core-values',
  '/school-anthem', '/college-name', '/headteacher', '/director1', '/director2',
  '/empowerment-programmes', '/empowerment/chaplaincy', '/empowerment/mentorship',
  '/empowerment/girl-boy-talk', '/empowerment/inspiration-night', '/empowerment/smosa-alumni',
  '/empowerment/christmas-cantata', '/smosa-feedback', '/apply', '/contact', '/news',
  '/fee-structures', '/performance', '/explore/career', '/explore/personal-needs',
  '/explore/uniform', '/explore/gallery', '/explore/student-leadership', '/clubs',
  '/campus-voices', '/posts',
]

const [news, campusVoices, clubs, posts] = await Promise.all([
  axios.get(`${SSG_API_BASE}/news`, { params: { limit: 1000 } }).then((r) => r.data.data.news),
  axios.get(`${SSG_API_BASE}/campus-voices`).then((r) => r.data.data.articles),
  axios.get(`${SSG_API_BASE}/clubs`).then((r) => r.data.data.clubs),
  axios.get(`${SSG_API_BASE}/posts`).then((r) => r.data.data.posts),
])

const urls = [
  ...STATIC_PATHS.map((loc) => ({ loc })),
  ...news.map((n) => ({ loc: `/news/${n.slug}`, lastmod: n.updated_at })),
  ...campusVoices.map((a) => ({ loc: `/campus-voices/${a.slug}` })),
  ...clubs.map((c) => ({ loc: `/clubs/${c.slug}` })),
  ...posts.map((p) => ({ loc: `/posts/${p.slug}` })),
]

const escape = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')

const sitemap = [
  '<?xml version="1.0" encoding="UTF-8"?>',
  '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
  ...urls.map((u) => `  <url><loc>${escape(u.loc)}</loc>${u.lastmod ? `<lastmod>${u.lastmod.slice(0, 10)}</lastmod>` : ''}</url>`),
  '</urlset>',
].join('\n')

writeFileSync(resolve(distDir, 'sitemap.xml'), sitemap)

const robots = [
  'User-agent: *',
  'Allow: /',
  'Disallow: /contact',
  'Disallow: /apply',
  'Disallow: /smosa-feedback',
  '',
  'Sitemap: /sitemap.xml',
  '',
].join('\n')

writeFileSync(resolve(distDir, 'robots.txt'), robots)

console.log(`Generated sitemap.xml (${urls.length} URLs) and robots.txt`)
