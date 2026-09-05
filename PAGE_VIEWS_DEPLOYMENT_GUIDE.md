# Page View Tracking - Deployment Guide

This guide outlines the steps to deploy the page view tracking feature to your live server.

## Summary of Changes

The page view tracking feature allows you to monitor website traffic from the admin panel. It tracks:
- Home page visits
- News article views
- Daily view statistics
- Views by page type

## Files to Upload

### 1. Database Migration
- `database/migrations/2026_07_02_213536_create_page_views_table.php`

### 2. Backend Files
- `app/Models/PageView.php` (NEW FILE)
- `app/Http/Controllers/PageViewController.php` (NEW FILE)
- `app/Http/Middleware/VerifyCsrfToken.php` (NEW FILE - CRITICAL for CSRF exemption)

### 3. Frontend Files
- `resources/js/composables/usePageView.js` (NEW FILE)
- `resources/js/Pages/Home.vue` (MODIFIED - added page view tracking)
- `resources/js/Pages/News/Show.vue` (MODIFIED - added page view tracking)
- `resources/js/Pages/Admin/News/Index.vue` (MODIFIED - added view stats display and button)
- `resources/js/Pages/Admin/PageViews/Index.vue` (NEW FILE)

### 4. Routes
- `routes/web.php` (MODIFIED - added page view tracking routes)

## Deployment Steps

### 1. Upload Files

Upload all the files listed above to your live server in their respective directories.

### 2. Run Database Migration

SSH into your live server and run:

```bash
php artisan migrate
```

This will create the `page_views` table.

### 3. Build Frontend Assets

On your live server, run:

```bash
npm run build
```

This compiles the Vue components with the new page view tracking functionality.

### 4. Clear Caches

Clear all Laravel caches to ensure the new routes and configurations are loaded:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

**IMPORTANT:** The `VerifyCsrfToken.php` middleware file is critical for the page view tracking to work. Without it, you will get a 419 CSRF error when the page view tracking tries to send data to the server.

### 5. Set File Permissions (Linux Server)

If your live server is Linux, ensure proper permissions:

```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
```

## Testing

After deployment, test the following:

1. **Visit the home page** - This should automatically record a page view
2. **Visit a news article** - This should also record a page view
3. **Check admin panel** - Go to `/admin/page-views` to see:
   - Total page views
   - Daily views breakdown
   - Views by page type
4. **Check admin news page** - Go to `/admin/news` to see:
   - View statistics for each news item
   - "View Page Statistics" button linking to the page views dashboard

## How It Works

1. When a client visits the home page or a news article, the `usePageView` composable automatically sends a request to `/api/page-view`
2. The server records the page view with:
   - Page URL
   - Page type (home, news, etc.)
   - Page ID (for news articles)
   - IP address
   - User agent
   - Date
3. The admin dashboard at `/admin/page-views` displays statistics from the database

## Notes

- The page view tracking is client-side, so it only tracks actual page visits
- Views are grouped by date, so you can see daily statistics
- The system tracks up to 30 days of daily view history
- No additional configuration is needed in `.env` file

## Troubleshooting

If page views are not being recorded:

1. Check browser console for JavaScript errors
2. Verify the `/api/page-view` route is accessible
3. Check that the `page_views` table exists in the database
4. Ensure the frontend assets were built successfully
5. Clear caches again

If the admin dashboard shows no data:

1. Visit the home page or a news article first to generate data
2. Check the `page_views` table in the database
3. Verify the API endpoint `/api/page-view/stats` is working
