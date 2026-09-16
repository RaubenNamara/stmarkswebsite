# Deploying to the root of a cPanel account

This project currently runs locally at `http://localhost/stmarkswebsite/...` (everything nested
under a subfolder of XAMPP's `htdocs`). This guide gets the same codebase running at the **root**
of a cPanel hosting account instead (`https://yourdomain.com/...`, no subfolder).

The four pieces (`public-frontend/`, `admin-frontend/`, `backend/`, `shared/`) are all
environment-driven already - nothing in the PHP or Vue source is hardcoded to `/stmarkswebsite/`
except two `vite.config.ts` `base` values and the root `.htaccess`, all three of which this guide
overrides. No other code changes are needed.

## 1. What goes where in `public_html`

Upload these directories/files so that cPanel's `public_html` **is** the project root:

```
public_html/
├── .htaccess                  <- deploy/cpanel-root.htaccess, renamed
├── admin-frontend/
│   └── dist/                  <- built with VITE_BASE=/admin/ (step 3)
├── backend/
│   ├── app/, routes/, public/
│   └── vendor/                <- from `composer install` run ON THE SERVER (step 4)
├── public-frontend/
│   └── dist/                  <- built with VITE_BASE=/ (step 3)
├── shared/
│   ├── src/, config/, database/
│   └── .env                   <- deploy/.env.cpanel-root.example, filled in (step 2)
└── storage/
    └── uploads/                <- every uploaded photo/PDF/video; copy this whole folder over
```

Do **not** upload `node_modules/`, `admin-frontend/src` or `public-frontend/src` (source, not
needed at runtime), or the local `backend/vendor` folder as-is - see step 4, it contains a
Windows-absolute symlink that only makes sense on this machine.

## 2. Database

1. In cPanel, create a MySQL database and a database user (MySQL Databases page), and note the
   real database name, username and password cPanel gives you (it prefixes both with your cPanel
   username, e.g. `myuser_stmarkswebsite`).
2. Export the current local database: in phpMyAdmin, select `stmarkswebsite` → Export → Quick →
   Go. This gives you a `.sql` file with all current content (news, staff, gallery, etc.).
3. In cPanel's phpMyAdmin, select the new empty database → Import → choose that `.sql` file → Go.
4. Copy `deploy/.env.cpanel-root.example` to `shared/.env` and fill in the real
   `DB_NAME`/`DB_USER`/`DB_PASS` from step 1, plus real mail credentials (cPanel's Email Accounts
   page, or keep using the existing Gmail app-password setup - copy it from the local `shared/.env`
   rather than retyping it). Update `ADMIN_FRONTEND_URL` to `https://yourdomain.com/admin`.

## 3. Build both frontends

Run these **locally** (or in any Node environment - not on the cPanel server, which typically has
no Node), then upload only the resulting `dist/` folders.

```bash
# Admin SPA - served at /admin/ with nothing before it, unlike local dev's /stmarkswebsite/admin/
cd admin-frontend
VITE_BASE=/admin/ npm run build
# -> upload admin-frontend/dist/

# Public site - served at the domain root
cd ../public-frontend
VITE_BASE=/ SSG_API_BASE=https://yourdomain.com/api/public npm run build
# -> upload public-frontend/dist/
```

`SSG_API_BASE` matters here: the public site is statically prerendered (vite-ssg), so the build
process itself fetches news/clubs/campus-voices/etc. from a live API to know which pages to
generate. That means **the backend must already be uploaded and reachable at
`https://yourdomain.com/api/...` before this build step**, or point `SSG_API_BASE` at whatever
your backend answers on (e.g. an IP or staging subdomain) and rebuild once the real domain is
live if the URL changes. Get steps 1, 2, 4 and 5 done first, confirm
`https://yourdomain.com/api/health` returns `{"success":true}`, then come back and build the
public frontend last.

## 4. Backend PHP dependencies

`backend/composer.json` links to `shared/` via a Composer **path repository with a symlink**
(`"options": {"symlink": true}` - see the file). That symlink is regenerated relative to
wherever it's installed, so it must be created **on the server**, not copied from this Windows
machine (the local one points at an absolute Windows path that means nothing on Linux hosting).

**If your cPanel plan has SSH or a Terminal app** (Setup → Terminal, or "Terminal" in cPanel's
Advanced section - common on most paid plans):

```bash
cd public_html/backend
composer install --no-dev --optimize-autoloader
```

This both installs the small set of real dependencies (PHPMailer) and creates a correct
`vendor/stmarks/shared` symlink pointing at your uploaded `shared/` folder.

**If your plan has no shell access at all**, Composer can't run server-side. Instead, generate
`vendor/` locally with the symlink disabled so it's a real copied folder that survives a plain
file upload:

```bash
cd backend
# Edit composer.json's repositories entry: "symlink": true -> "symlink": false, temporarily
composer install --no-dev --optimize-autoloader
# revert composer.json back to "symlink": true afterwards, for local dev
```

Then upload the resulting `backend/vendor/` folder as-is - `vendor/stmarks/shared` will be a real
directory, not a symlink, so it works regardless of host.

## 5. File permissions and PHP version

- Set PHP to **8.2 or newer** in cPanel's "Select PHP Version" tool (this codebase requires it -
  see `backend/composer.json`).
- `storage/uploads/` must be writable by the web server user (cPanel's default `755` on the
  directory is usually already fine; only change it if uploads start failing with a permissions
  error in `backend`'s error log).

## 6. Go live checklist

- `https://yourdomain.com/api/health` → `{"success":true,"message":"ok"}`
- `https://yourdomain.com/` → the public home page renders with real content and images
- `https://yourdomain.com/admin` → admin login page loads, and logging in works
- Submit the public Contact form once → confirm the email arrives (tests `MAIL_*` config)
- Open `/admin/analytics` and confirm it loads (may show 0 views until real visitors arrive)
- Click a page on the public site, then check `/admin/analytics` again a minute later - the view
  count should have incremented, confirming the tracking beacon reaches the live backend

## Rolling back a mistake

None of this touches local dev - `admin-frontend/vite.config.ts` and
`public-frontend/vite.config.ts` still default to the `/stmarkswebsite/...` paths when `VITE_BASE`
isn't set, `shared/.env` (local) is untouched, and the project-root `.htaccess` used by local
XAMPP is untouched (`deploy/cpanel-root.htaccess` is a separate file). `npm run dev` and
`npm run build` locally continue to work exactly as before.
