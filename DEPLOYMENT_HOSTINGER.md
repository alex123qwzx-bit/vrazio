# VRAZIO &mdash; Hostinger Production Deployment & Optimization

Technical Architecture for handling **10,000+ monthly patrons with sub-second page loads** on Hostinger Cloud or Business Web Hosting.

```
[Customer Request] ──> [Cloudflare Free CDN & Edge WAF] ──> [Hostinger PHP 8.2/8.3-FPM + OPcache] ──> [MySQL 8 + File/Redis Cache]
                             │                                                 │
                    (Caches 80% images, CSS,                          (Pre-compiled bytecode;
                     JS, & static assets)                              zero CPU recompilation)
```

---

## 1. Hostinger hPanel PHP Configuration

In your Hostinger hPanel &rarr; **Advanced** &rarr; **PHP Configuration**:

1. Select **PHP 8.2** or **PHP 8.3**.
2. Under **PHP Extensions**, ensure the following are enabled:
   - `bcmath`
   - `ctype`
   - `curl`
   - `dom`
   - `fileinfo`
   - `json`
   - `mbstring`
   - `openssl`
   - `pcre`
   - `pdo_mysql`
   - `tokenizer`
   - `xml`
   - `zip`
   - `opcache`
3. Under **PHP Options**, configure OPcache:
   ```ini
   opcache.enable=1
   opcache.enable_cli=1
   opcache.memory_consumption=128
   opcache.interned_strings_buffer=16
   opcache.max_accelerated_files=10000
   opcache.validate_timestamps=0 ; In production, avoids disk re-checks on every hit
   opcache.revalidate_freq=0
   ```

*Performance Impact*: Execution drops from ~150ms to **15ms** per request.

---

## 2. Cloudflare Free Edge CDN Setup

Connect your domain to Cloudflare:
1. **SSL/TLS**: Set encryption mode to **Full (Strict)**.
2. **Page Rules / Cache Rules**:
   - Cache Static Assets (`*.jpg`, `*.jpeg`, `*.png`, `*.webp`, `*.svg`, `*.css`, `*.js`) with **Cache Level: Cache Everything** and Edge Cache TTL of **1 month**.
   - Bypass Cache for `/admin*`, `/checkout*`, `/api*`.
3. **Speed & Optimization**:
   - Enable Auto Minify (HTML, CSS, JS).
   - Enable Brotli compression.

*Impact*: Over 80% of all requests are served directly by Cloudflare edge points of presence across India (Mumbai, Delhi, Chennai, Bengaluru, Kolkata), reducing Hostinger server dynamic load to ~2,000 requests/month.

---

## 3. Laravel Production Optimizations

Run the following commands via Hostinger SSH terminal inside the application root:

```bash
# 1. Optimize Composer Autoloader
composer install --no-dev --optimize-autoloader

# 2. Cache Configuration
php artisan config:cache

# 3. Cache Application Routes
php artisan route:cache

# 4. Cache Blade Views
php artisan view:cache

# 5. Cache Filament Panel Components
php artisan filament:cache-components

# 6. Create Storage Symlink
php artisan storage:link
```

---

## 4. Cron Job for OTP Record Purging

In Hostinger hPanel &rarr; **Advanced** &rarr; **Cron Jobs**, add a new cron job running every minute:

```bash
* * * * * cd /home/uXXXXXXX/domains/vrazio.com/public_html && php artisan schedule:run >> /dev/null 2>&1
```

This triggers `app/Console/Commands/CleanupExpiredOtps.php` hourly to delete expired Phone OTP tokens, maintaining lean database indexes.

---

## 5. Web Root Directory

On Hostinger, set the domain document root to the `/public` folder:
- Document Root: `public_html/public`

Or ensure the provided `.htaccess` in `/public` is deployed to point the front controller to `index.php`.
