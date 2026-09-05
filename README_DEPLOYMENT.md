# St. Marks Website - Deployment Guide

## Overview
This is a Laravel 12 application with Vue.js frontend, ready for production deployment.

## Hosting Requirements

### Server Requirements
- **PHP**: 8.2 or higher
- **Web Server**: Apache or Nginx
- **Database**: MySQL 8.0+ or PostgreSQL
- **Node.js**: 18+ (for asset building)
- **Composer**: Latest version
- **Redis** (recommended for caching/queues)

### PHP Extensions Required
- OpenSSL
- PDO
- Mbstring
- Tokenizer
- XML
- Ctype
- JSON
- BCMath
- Fileinfo
- Redis (if using Redis)

## Deployment Options

### 1. Shared Hosting (Easy)
- **Recommended**: DigitalOcean App Platform, Heroku, or Laravel Forge
- **Pros**: Easy setup, managed infrastructure
- **Cons**: Limited control, higher cost

### 2. VPS/Cloud Server (Recommended)
- **Providers**: DigitalOcean, Vultr, Linode, AWS EC2
- **Pros**: Full control, better performance, cost-effective
- **Cons**: Requires server management

### 3. PaaS (Platform as a Service)
- **Options**: Laravel Vapor, AWS Elastic Beanstalk
- **Pros**: Scalable, serverless options
- **Cons**: More complex setup

## Quick Deployment Steps

### 1. Prepare Your Environment
```bash
# Copy production environment file
cp production.env.example .env

# Generate application key
php artisan key:generate

# Update .env with your database and other credentials
```

### 2. Run Deployment Script
```bash
chmod +x deploy.sh
./deploy.sh
```

### 3. Configure Web Server

#### Apache Configuration
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /path/to/your/project/public
    
    <Directory /path/to/your/project/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/your/project/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.ht {
        deny all;
    }
}
```

### 4. SSL Certificate (Recommended)
```bash
# Install Let's Encrypt SSL
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d yourdomain.com
```

## Environment Variables Checklist

Update these variables in your `.env` file:

- `APP_URL`: Your production domain
- `DB_*`: Database connection details
- `MAIL_*`: Email configuration
- `REDIS_*`: Redis connection (if used)
- `AWS_*`: S3 configuration (if using cloud storage)

## Post-Deployment Tasks

1. **Test the application** thoroughly
2. **Set up monitoring** (Uptime monitoring, error tracking)
3. **Configure backups** (Database and files)
4. **Set up SSL certificate**
5. **Configure domain DNS**
6. **Set up cron job** for Laravel scheduler:
   ```bash
   * * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
   ```

## Troubleshooting

### Common Issues
- **500 Error**: Check file permissions and `.env` configuration
- **Database Connection**: Verify database credentials and connectivity
- **Assets not loading**: Run `npm run build` and check `APP_URL`
- **Cache issues**: Clear cache with `php artisan cache:clear`

### Debug Mode
Never enable `APP_DEBUG=true` in production. Use logs instead:
```bash
php artisan log:clear
tail -f storage/logs/laravel.log
```

## Security Recommendations

1. Keep dependencies updated
2. Use HTTPS everywhere
3. Implement rate limiting
4. Regular security audits
5. Backup strategy
6. Monitor error logs

## Performance Optimization

1. Enable OPcache
2. Use Redis for caching/sessions
3. Configure CDN for assets
4. Enable Gzip compression
5. Use database connection pooling

## Support

For deployment issues:
1. Check Laravel documentation
2. Review server logs
3. Verify environment configuration
4. Test database connectivity

---

**Note**: This deployment guide assumes you have SSH access to your server and basic Linux administration skills.
