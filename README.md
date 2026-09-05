# St Mark's College Namagoma - website

Framework-free PHP + Vue 3 SPA stack (no Laravel):

- `shared/` - config, database access, models, services, and support code used by both `backend`
  and `public-site`.
- `backend/` - JSON API for the admin panel (session-cookie auth, CSRF-protected mutations).
- `public-site/` - server-rendered public-facing site (plain PHP templates).
- `admin-frontend/` - Vue 3 + TypeScript + Pinia SPA for the admin panel, built with Vite.

## Local development

Served from one origin, `http://localhost/stmarkswebsite/`, via the project-root `.htaccess`
(no Apache vhost or hosts-file changes needed):

- `/` and everything not listed below -> `public-site/public/index.php`
- `/api/*` -> `backend/public/` (junction)
- `/admin/*` -> `admin-frontend/dist` (junction; production Vite build)
- `/uploads/*` -> `storage/uploads/` (junction; user-uploaded files)
- `/assets/*` -> `public-site/public/assets/` (junction)

Rebuild the admin SPA after any `admin-frontend/src` change:

```
cd admin-frontend
vite build --base=/stmarkswebsite/admin/
```

(Run this in PowerShell, not Git Bash - Git Bash mangles the leading `/` in the `--base` value.)

For hot-reload iteration on the admin SPA, `npm run dev` in `admin-frontend/` also works
independently (proxies `/api` to the backend).

## Configuration

`shared/.env` holds DB credentials, mail settings, and app config - see `shared/config/Config.php`
for the full list of keys and their defaults.
