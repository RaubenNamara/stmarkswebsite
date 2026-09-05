# Live Server Deployment Guide

## Changes Made to Contact Form

### 1. Database Changes
**File:** `database/migrations/2026_07_02_173100_add_telephone_to_contacts_table.php`

**Action Required on Live Server:**
- Upload the migration file to: `database/migrations/`
- Run: `php artisan migrate`

This will add the `telephone` column to the `contacts` table.

---

### 2. Frontend Files to Upload

#### Contact Form
**File:** `resources/js/Pages/Contact.vue`
- Added telephone field above email field
- Changed form submission to use named route

#### Admin Contacts View
**File:** `resources/js/Pages/Admin/Contacts/Index.vue`
- Added telephone display in modal
- Fixed modal scrolling for long messages
- Increased modal width

**Action Required on Live Server:**
- Upload both files to their respective locations
- Run: `npm run build` to compile the assets

---

### 3. Backend Files to Upload

#### Contact Model
**File:** `app/Models/Contact.php`
- Added 'telephone' to fillable array

#### Contact Controller
**File:** `app/Http/Controllers/ContactController.php`
- Added telephone validation
- Added email sending logic with error handling
- Added imports for Mail and ContactMessage

#### Email Mailable
**File:** `app/Mail/ContactMessage.php` (NEW FILE)
- Created new mailable class for contact emails

#### Email Template
**File:** `resources/views/emails/contact.blade.php` (NEW FILE)
- Created email template for contact notifications

**Action Required on Live Server:**
- Upload all files to their respective locations
- No additional commands needed

---

### 4. Configuration Changes

#### .env File
Add/Update these lines in your live server's `.env` file:

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=robstech10@gmail.com
MAIL_PASSWORD=rcyzrkivjtbpomcu
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=robstech10@gmail.com
MAIL_FROM_NAME="St Marks College"
```

**Action Required on Live Server:**
- Update .env file with the above settings
- Run: `php artisan config:clear`

---

### 5. Cache Clearing

After uploading all files and updating .env, run these commands:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

---

### 6. File Permissions (Linux Server)

If your live server is Linux, ensure proper permissions:

```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
```

---

### 7. Testing

After deployment, test:
1. Visit the contact form page
2. Submit a test message with telephone number
3. Check admin dashboard to see the message
4. Verify email is received at both:
   - info@stmark.sc.ug
   - robstech10@gmail.com

---

## Summary Checklist

- [ ] Upload migration file to `database/migrations/`
- [ ] Run `php artisan migrate`
- [ ] Upload `resources/js/Pages/Contact.vue`
- [ ] Upload `resources/js/Pages/Admin/Contacts/Index.vue`
- [ ] Upload `app/Models/Contact.php`
- [ ] Upload `app/Http/Controllers/ContactController.php`
- [ ] Upload `app/Mail/ContactMessage.php` (NEW)
- [ ] Upload `resources/views/emails/contact.blade.php` (NEW)
- [ ] Update `.env` with mail settings
- [ ] Run `npm run build`
- [ ] Run `php artisan config:clear`
- [ ] Run `php artisan cache:clear`
- [ ] Run `php artisan view:clear`
- [ ] Run `php artisan route:clear`
- [ ] Test contact form submission
- [ ] Verify email delivery
