# St Mark's College Namagoma - website

Framework-free PHP backend + Vue 3 frontends (no Laravel):

- `shared/` - config, database access, models, services, and support code used by both `backend`
  and `public-frontend` (via its JSON API).
- `backend/` - JSON API: `/api/admin/*` (session-cookie auth, CSRF-protected mutations) for the
  admin panel, `/api/public/*` (no auth) for the public site.
- `public-frontend/` - the public-facing site: Vue 3 + TypeScript + Vite, prerendered at build
  time with `vite-ssg` (real static HTML per route for SEO, Vue hydrates for interactivity).
- `admin-frontend/` - Vue 3 + TypeScript + Pinia SPA for the admin panel, built with Vite.

Publishing/editing/deleting public-facing content (news, posts, clubs, campus voices, staff,
gallery, and the other public domains) automatically triggers a `public-frontend` rebuild - see
`shared/src/Support/PublicSiteBuildService`. Logs to `storage/logs/public-site-build.log`.

## Local development

Served from one origin, `http://localhost/stmarkswebsite/`, via the project-root `.htaccess`
(no Apache vhost or hosts-file changes needed):

- `/` and everything not listed below -> `public-frontend/dist/` (prerendered static files,
  falling back to the SPA shell for a route not known at build time)
- `/api/*` -> `backend/public/` (junction)
- `/admin/*` -> `admin-frontend/dist` (junction; production Vite build)
- `/uploads/*` -> `storage/uploads/` (junction; user-uploaded files)

Rebuild `public-frontend` after any `public-frontend/src` change (also happens automatically on
publish, see above):

```
cd public-frontend
npm run build
```

Rebuild the admin SPA after any `admin-frontend/src` change:

```
cd admin-frontend
vite build --base=/stmarkswebsite/admin/
```

(Run this in PowerShell, not Git Bash - Git Bash mangles the leading `/` in the `--base` value.)

For hot-reload iteration, `npm run dev` in either `public-frontend/` or `admin-frontend/` also
works independently (proxies `/api` to the backend).

## Configuration

`shared/.env` holds DB credentials, mail settings, and app config - see `shared/config/Config.php`
for the full list of keys and their defaults.
