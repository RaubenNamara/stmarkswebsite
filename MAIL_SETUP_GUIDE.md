# Gmail SMTP Setup Guide for Contact Form Emails

## Step 1: Generate Gmail App Password

1. Go to https://myaccount.google.com/security
2. Enable 2-Factor Authentication if not already enabled
3. Go to "2-Step Verification" → "App passwords"
4. Click "Create" → Enter a name (e.g., "St Marks Website")
5. Click "Generate"
6. Copy the 16-character password (it will look like: `abcd efgh ijkl mnop`)

## Step 2: Add to .env File

Add these lines to your `.env` file:

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=robstech10@gmail.com
MAIL_PASSWORD=PASTE_YOUR_APP_PASSWORD_HERE
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=robstech10@gmail.com
MAIL_FROM_NAME="St Marks College"
```

**Important:** Replace `PASTE_YOUR_APP_PASSWORD_HERE` with the actual app password you generated (remove any spaces).

## Step 3: Clear Config Cache

Run this command in your terminal:

```bash
php artisan config:clear
```

## Step 4: Test

Submit a test message through the contact form at:
http://localhost/stmarkswebsite/public/contact

The email will be sent to both:
- info@stmark.sc.ug
- robstech10@gmail.com

## Troubleshooting

If emails don't send:
1. Check that the app password is correct (no spaces)
2. Ensure 2-factor authentication is enabled on your Gmail
3. Check Laravel logs: `storage/logs/laravel.log`
4. Run `php artisan config:clear` again after .env changes
