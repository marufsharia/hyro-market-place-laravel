# Production Deployment Checklist

This checklist ensures all production environment configurations are properly set up before deploying the Hyro Marketplace application.

## Pre-Deployment Configuration

### Environment Variables

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate new `APP_KEY` with `php artisan key:generate`
- [ ] Set `APP_URL` to production domain
- [ ] Set `APP_TIMEZONE` to appropriate timezone

### Database Configuration

- [ ] Configure production database connection (MySQL/PostgreSQL)
- [ ] Set `DB_CONNECTION=mysql` (or pgsql)
- [ ] Configure `DB_HOST`, `DB_PORT`, `DB_DATABASE`
- [ ] Set secure `DB_USERNAME` and `DB_PASSWORD`
- [ ] Configure connection pooling: `DB_POOL_MIN=2`, `DB_POOL_MAX=10`
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Seed initial data if needed: `php artisan db:seed --force`

### Redis Configuration

- [ ] Install and configure Redis server
- [ ] Set `SESSION_DRIVER=redis`
- [ ] Set `CACHE_STORE=redis`
- [ ] Set `QUEUE_CONNECTION=redis`
- [ ] Configure Redis connection: `REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD`
- [ ] Set `REDIS_DB=0` and `REDIS_CACHE_DB=1`
- [ ] Set `REDIS_PREFIX=hyro_marketplace`
- [ ] Test Redis connection: `redis-cli ping`

### File Storage Configuration

- [ ] Set `FILESYSTEM_DISK=s3` for production
- [ ] Configure AWS credentials: `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`
- [ ] Set `AWS_DEFAULT_REGION` and `AWS_BUCKET`
- [ ] Set `AWS_URL` if using custom domain
- [ ] Test S3 upload: `php artisan tinker` → `Storage::disk('s3')->put('test.txt', 'test')`
- [ ] Run `php artisan storage:link` if using local storage

### Session & Cookie Configuration

- [ ] Set `SESSION_SECURE_COOKIE=true` (requires HTTPS)
- [ ] Set `SESSION_HTTP_ONLY=true`
- [ ] Set `SESSION_SAME_SITE=lax`
- [ ] Set `SESSION_DOMAIN` to your domain (e.g., `.example.com`)
- [ ] Set `SESSION_LIFETIME=120` (or desired value in minutes)

### Security Configuration

- [ ] Set `TRUSTED_PROXIES=*` or specific proxy IPs
- [ ] Configure `CORS_ALLOWED_ORIGINS` (comma-separated URLs)
- [ ] Set `CSP_ENABLED=true` for Content Security Policy
- [ ] Ensure CSRF protection is enabled (default in Laravel)
- [ ] Configure rate limiting on authentication routes
- [ ] Set secure `BCRYPT_ROUNDS=12` (or higher)

### Mail Configuration

- [ ] Configure mail driver: `MAIL_MAILER` (smtp, ses, mailgun, etc.)
- [ ] Set `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`
- [ ] Set `MAIL_FROM_ADDRESS` and `MAIL_FROM_NAME`
- [ ] Test email sending: `php artisan tinker` → `Mail::raw('Test', fn($m) => $m->to('test@example.com'))`

### Logging Configuration

- [ ] Set `LOG_CHANNEL=daily` or `stack`
- [ ] Set `LOG_LEVEL=error` or `warning` for production
- [ ] Configure log rotation (see production-php-configuration.md)
- [ ] Set up error tracking (Sentry, Bugsnag, etc.)

### Queue Configuration

- [ ] Configure Supervisor for queue workers (see production-php-configuration.md)
- [ ] Set `QUEUE_CONNECTION=redis`
- [ ] Start queue workers: `sudo supervisorctl start hyro-marketplace-worker:*`
- [ ] Verify workers are running: `sudo supervisorctl status`

### CDN Configuration (Optional)

- [ ] Set `CDN_URL` if using CDN for static assets
- [ ] Configure CDN to pull from origin server
- [ ] Test CDN asset delivery

## PHP Configuration

- [ ] Enable OPcache in php.ini
- [ ] Set `opcache.enable=1`
- [ ] Set `opcache.memory_consumption=256`
- [ ] Set `opcache.max_accelerated_files=20000`
- [ ] Set `opcache.validate_timestamps=0` for maximum performance
- [ ] Set `memory_limit=512M`
- [ ] Set `upload_max_filesize=10M`
- [ ] Set `post_max_size=10M`
- [ ] Set `expose_php=Off`
- [ ] Restart PHP-FPM: `sudo systemctl restart php8.2-fpm`

## Laravel Optimization

- [ ] Run `composer install --optimize-autoloader --no-dev`
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Run `php artisan event:cache`
- [ ] Build frontend assets: `npm run build`
- [ ] Clear old caches if needed: `php artisan optimize:clear`

## Web Server Configuration

### Nginx

- [ ] Configure virtual host (see production-php-configuration.md)
- [ ] Enable gzip compression
- [ ] Configure static asset caching
- [ ] Set up SSL/TLS certificate (Let's Encrypt recommended)
- [ ] Configure HTTP to HTTPS redirect
- [ ] Test configuration: `sudo nginx -t`
- [ ] Reload Nginx: `sudo systemctl reload nginx`

### Apache

- [ ] Configure virtual host (see production-php-configuration.md)
- [ ] Enable mod_rewrite: `sudo a2enmod rewrite`
- [ ] Enable mod_deflate: `sudo a2enmod deflate`
- [ ] Enable mod_expires: `sudo a2enmod expires`
- [ ] Set up SSL/TLS certificate
- [ ] Test configuration: `sudo apachectl configtest`
- [ ] Reload Apache: `sudo systemctl reload apache2`

## Database Optimization

- [ ] Verify all indexes are created (run migrations)
- [ ] Optimize tables: `php artisan db:optimize` (if available)
- [ ] Set up automated backups (see production-php-configuration.md)
- [ ] Test backup restoration process
- [ ] Configure database monitoring

## Security Hardening

- [ ] Set proper file permissions:
  - [ ] `chmod -R 755 /var/www/hyro-marketplace`
  - [ ] `chmod -R 775 storage bootstrap/cache`
  - [ ] `chown -R www-data:www-data /var/www/hyro-marketplace`
- [ ] Disable directory listing in web server
- [ ] Configure firewall (UFW or iptables):
  - [ ] Allow port 80 (HTTP)
  - [ ] Allow port 443 (HTTPS)
  - [ ] Allow port 22 (SSH) - restrict to specific IPs if possible
  - [ ] Deny all other incoming traffic
- [ ] Set up fail2ban for SSH protection
- [ ] Disable root SSH login
- [ ] Keep system packages updated: `sudo apt update && sudo apt upgrade`
- [ ] Remove .env file from version control
- [ ] Ensure .git directory is not accessible via web

## Monitoring & Maintenance

- [ ] Set up application monitoring (New Relic, Scout, etc.)
- [ ] Set up error tracking (Sentry, Bugsnag, etc.)
- [ ] Set up uptime monitoring (Pingdom, UptimeRobot, etc.)
- [ ] Configure log aggregation (ELK Stack, Papertrail, etc.)
- [ ] Set up database backup monitoring
- [ ] Configure health check endpoint monitoring (`/up`)
- [ ] Set up alerts for critical errors
- [ ] Document incident response procedures

## Testing in Production

- [ ] Test user registration and email verification
- [ ] Test user login and authentication
- [ ] Test plugin creation and file upload
- [ ] Test review submission
- [ ] Test favorite functionality
- [ ] Test admin dashboard access
- [ ] Test admin plugin approval workflow
- [ ] Test rate limiting on authentication endpoints
- [ ] Test CSRF protection
- [ ] Test error pages (404, 403, 500)
- [ ] Test SSL/TLS certificate
- [ ] Test CDN asset delivery (if configured)
- [ ] Test queue job processing
- [ ] Test cache functionality
- [ ] Verify no sensitive data in error responses

## Performance Testing

- [ ] Run load tests to verify performance under load
- [ ] Check OPcache hit rate: `php -i | grep opcache.statistics`
- [ ] Monitor Redis memory usage: `redis-cli info memory`
- [ ] Check database query performance
- [ ] Verify cache hit rates
- [ ] Test page load times
- [ ] Check for N+1 query problems
- [ ] Monitor server resource usage (CPU, memory, disk)

## Rollback Plan

- [ ] Document current version/commit hash
- [ ] Keep previous version available for quick rollback
- [ ] Test rollback procedure in staging environment
- [ ] Document rollback steps:
  1. Switch to previous code version
  2. Run migrations rollback if needed
  3. Clear caches
  4. Restart services
- [ ] Keep database backup before deployment

## Post-Deployment

- [ ] Monitor error logs for 24-48 hours
- [ ] Monitor application performance metrics
- [ ] Check queue job processing
- [ ] Verify scheduled tasks are running (if any)
- [ ] Monitor user feedback and bug reports
- [ ] Document any issues and resolutions
- [ ] Update deployment documentation if needed

## Admin Setup

- [ ] Create admin user account
- [ ] Set `is_admin=true` for admin user in database
- [ ] Test admin dashboard access
- [ ] Test admin plugin approval workflow
- [ ] Test admin review deletion
- [ ] Test admin category management

## Compliance & Legal

- [ ] Ensure privacy policy is up to date
- [ ] Ensure terms of service are up to date
- [ ] Verify GDPR compliance (if applicable)
- [ ] Verify data retention policies
- [ ] Document data backup and recovery procedures
- [ ] Ensure cookie consent is implemented (if required)

## Documentation

- [ ] Update README.md with production setup instructions
- [ ] Document environment variables in .env.example
- [ ] Document deployment process
- [ ] Document rollback procedures
- [ ] Document monitoring and alerting setup
- [ ] Document backup and recovery procedures
- [ ] Document incident response procedures

## Sign-Off

- [ ] Development team sign-off
- [ ] QA team sign-off
- [ ] Security team sign-off
- [ ] Operations team sign-off
- [ ] Product owner sign-off

---

**Deployment Date:** _______________

**Deployed By:** _______________

**Version/Commit:** _______________

**Notes:**

_______________________________________________________________________________

_______________________________________________________________________________

_______________________________________________________________________________
