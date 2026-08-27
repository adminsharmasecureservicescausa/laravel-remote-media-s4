# Secure Media Streaming Platform (Laravel 11 + Tailwind + MEGA S4)

A lightweight, enterprise-hardened media streaming portal built on **Laravel 11**, **Tailwind CSS**, **Alpine.js**, **Plyr.js**, and private **S3/MEGA S4** object storage.

## Features

- **Zero-Trust Media Delivery:** Media files are never exposed publicly; short-lived AWS V4 pre-signed signatures (15m TTL) are generated on demand.
- **Mutual TLS (mTLS) & Session Auth:** Dual authentication support via client TLS certificates (`.p12` / PKCS#12) or standard email/password credentials.
- **Expansive Theater & Carousel View:** Full-window responsive video and photo theater with floating carousel arrows and keyboard shortcut navigation (`←`, `→`, `Esc`).
- **HTTP Byte-Range & Streaming Support:** Direct partial content (HTTP 206) video streaming with Plyr playback controls, speed selection, and automatic token renewal before expiration.
- **Security Hardening:** Enterprise CSP headers, reverse-proxy trust, strict CORS rules, rate-limiting (60 req/min), and denied direct public object access.
- **S3 / MEGA S4 Catalog Importer:** Built-in Artisan CLI command (`php artisan media:import-s4`) for scanning and synchronizing bucket assets.

## Tech Stack

- **Backend:** PHP 8.3, Laravel 11, AWS SDK for PHP / Flysystem AWS S3 v3
- **Frontend:** Blade, Tailwind CSS, Alpine.js, Plyr.js, Vite
- **Database:** SQLite / MySQL / PostgreSQL
- **Web Server:** Nginx 1.24, PHP-FPM 8.3, Let's Encrypt SSL/TLS

## Quick Start

### 1. Installation

```bash
composer install
npm install && npm run build
```

### 2. Environment Configuration

Copy `.env.example` to `.env` and fill in your storage credentials:

```bash
cp .env.example .env
php artisan key:generate
```

Key environment variables:

```ini
APP_NAME="SecureStream"
APP_ENV=production
APP_URL=https://stream.yourdomain.com

FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=ca-vancouver-1
AWS_BUCKET=your-bucket-name
AWS_ENDPOINT=https://s3.ca-vancouver-1.megas4.com
AWS_USE_PATH_STYLE_ENDPOINT=true

MEDIA_STREAM_TTL_MINUTES=15
```

### 3. Database Migration & Asset Import

```bash
php artisan migrate
php artisan media:import-s4 --video-limit=100 --image-limit=500
```

### 4. Running Tests

```bash
php artisan test
```

## Security Documentation

See `docs/` for security policies:
- `docs/s3-bucket-policy.json`: Zero public access IAM bucket policy.
- `docs/s3-cors-configuration.json`: Allowed origins and HTTP methods for media ranges.

## License

Open-sourced software under the [MIT license](LICENSE).
