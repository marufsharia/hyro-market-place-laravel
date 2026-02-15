# Production PHP Configuration Guide

This document provides recommended PHP configuration settings for production deployment of the Hyro Marketplace application.

## OPcache Configuration

OPcache is a PHP extension that improves performance by storing precompiled script bytecode in shared memory. This eliminates the need for PHP to load and parse scripts on each request.

### Recommended php.ini Settings

Add or update these settings in your `php.ini` file:

```ini
; Enable OPcache
opcache.enable=1
opcache.enable_cli=0

; Memory and file settings
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000

; Revalidation settings
opcache.revalidate_freq=60
opcache.validate_timestamps=1

; Performance optimization
opcache.fast_shutdown=1
opcache.enable_file_override=1

; For production, consider disabling timestamp validation for maximum performance
; opcache.validate_timestamps=0
; Note: When disabled, you must manually clear OPcache after deployments
```

### Verifying OPcache

Check if OPcache is enabled:

```bash
php -i | grep opcache.enable
```

Or create a PHP info page:

```php
<?php phpinfo(); ?>
```

### Clearing OPcache

After deployment, clear OPcache:

```bash
# Using PHP CLI
php artisan optimize:clear

# Or restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

## Additional PHP Performance Settings

```ini
; Increase memory limit for large operations
memory_limit=512M

; Increase max execution time for long-running operations
max_execution_time=60

; File upload limits (for plugin logos)
upload_max_filesize=10M
post_max_size=10M

; Realpath cache (improves file system performance)
realpath_cache_size=4096K
realpath_cache_ttl=600

; Disable expose_php for security
expose_php=Off
```

## Laravel Optimization Commands

Run these commands after deployment:

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Cache events
php artisan event:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Clear all caches (if needed)
php artisan optimize:clear
```

## Redis Configuration

For optimal Redis performance, configure these settings in `redis.conf`:

```conf
# Memory management
maxmemory 256mb
maxmemory-policy allkeys-lru

# Persistence (adjust based on your needs)
save 900 1
save 300 10
save 60 10000

# Performance
tcp-backlog 511
timeout 0
tcp-keepalive 300

# Logging
loglevel notice
logfile /var/log/redis/redis-server.log
```

## Web Server Configuration

### Nginx Configuration Example

```nginx
server {
    listen 80;
    server_name marketplace.example.com;
    root /var/www/hyro-marketplace/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Enable gzip compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### Apache Configuration Example

```apache
<VirtualHost *:80>
    ServerName marketplace.example.com
    DocumentRoot /var/www/hyro-marketplace/public

    <Directory /var/www/hyro-marketplace/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Enable compression
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
    </IfModule>

    # Cache static assets
    <IfModule mod_expires.c>
        ExpiresActive On
        ExpiresByType image/jpg "access plus 1 year"
        ExpiresByType image/jpeg "access plus 1 year"
        ExpiresByType image/gif "access plus 1 year"
        ExpiresByType image/png "access plus 1 year"
        ExpiresByType text/css "access plus 1 month"
        ExpiresByType application/javascript "access plus 1 month"
    </IfModule>

    ErrorLog ${APACHE_LOG_DIR}/marketplace-error.log
    CustomLog ${APACHE_LOG_DIR}/marketplace-access.log combined
</VirtualHost>
```

## Queue Worker Configuration

### Supervisor Configuration

Create `/etc/supervisor/conf.d/hyro-marketplace-worker.conf`:

```ini
[program:hyro-marketplace-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/hyro-marketplace/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/hyro-marketplace/storage/logs/worker.log
stopwaitsecs=3600
```

Start the worker:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start hyro-marketplace-worker:*
```

## Monitoring and Maintenance

### Health Checks

The application includes a health check endpoint at `/up` for load balancer monitoring.

### Log Rotation

Configure log rotation in `/etc/logrotate.d/hyro-marketplace`:

```
/var/www/hyro-marketplace/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
    postrotate
        php /var/www/hyro-marketplace/artisan optimize:clear > /dev/null 2>&1
    endscript
}
```

### Database Backups

Set up automated daily backups:

```bash
#!/bin/bash
# /usr/local/bin/backup-marketplace-db.sh

BACKUP_DIR="/var/backups/hyro-marketplace"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="hyro_marketplace"

mkdir -p $BACKUP_DIR

# MySQL backup
mysqldump -u root -p$DB_PASSWORD $DB_NAME | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

# Keep only last 30 days of backups
find $BACKUP_DIR -name "db_backup_*.sql.gz" -mtime +30 -delete
```

Add to crontab:

```bash
0 2 * * * /usr/local/bin/backup-marketplace-db.sh
```

## Performance Monitoring

Consider implementing:

- **Application Performance Monitoring (APM)**: New Relic, Scout, or Blackfire
- **Error Tracking**: Sentry or Bugsnag
- **Log Aggregation**: ELK Stack or Papertrail
- **Uptime Monitoring**: Pingdom or UptimeRobot

## CDN Configuration

For serving static assets via CDN:

1. Set `CDN_URL` in `.env`
2. Configure your CDN to pull from your origin server
3. Update asset URLs in views to use CDN

Example with CloudFront:

```env
CDN_URL=https://d1234567890.cloudfront.net
```

## Security Checklist

- [ ] OPcache enabled and configured
- [ ] PHP expose_php disabled
- [ ] File upload limits configured
- [ ] Redis password set (if exposed)
- [ ] Database credentials secured
- [ ] SSL/TLS certificate installed
- [ ] Firewall configured (only ports 80, 443, 22 open)
- [ ] Regular security updates applied
- [ ] Backup system tested and verified

## Performance Checklist

- [ ] OPcache enabled
- [ ] Laravel caches generated (config, routes, views)
- [ ] Redis configured for cache and sessions
- [ ] Queue workers running via Supervisor
- [ ] Database indexes created
- [ ] CDN configured for static assets
- [ ] Gzip compression enabled
- [ ] Browser caching configured
- [ ] Database connection pooling configured
- [ ] Log rotation configured

## Troubleshooting

### High Memory Usage

- Check OPcache memory consumption
- Review queue worker memory limits
- Monitor Redis memory usage
- Check for memory leaks in long-running processes

### Slow Response Times

- Enable query logging to identify slow queries
- Check Redis connection
- Review OPcache hit rate
- Monitor database connection pool
- Check for N+1 query problems

### Queue Jobs Not Processing

- Check Supervisor status: `sudo supervisorctl status`
- Review worker logs: `tail -f storage/logs/worker.log`
- Check Redis connection
- Verify queue configuration in `.env`

## Additional Resources

- [Laravel Deployment Documentation](https://laravel.com/docs/11.x/deployment)
- [PHP OPcache Documentation](https://www.php.net/manual/en/book.opcache.php)
- [Redis Configuration](https://redis.io/docs/management/config/)
- [Nginx Performance Tuning](https://www.nginx.com/blog/tuning-nginx/)
