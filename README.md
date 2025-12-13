# 🏢 HRM - Human Resource Management System

Hệ thống quản lý nhân sự được xây dựng với PHP 8.3+ và kiến trúc MVC hiện đại, sử dụng Service Provider pattern và Dependency Injection.

## 🚀 Yêu cầu hệ thống

- **PHP >= 8.3** (Extensions: `pdo_pgsql`, `mbstring`, `json`)
- **PostgreSQL >= 12**
- **Composer >= 2.0**
- **Node.js >= 18** (để build assets)
- **npm** hoặc **yarn**

---

## 📦 Cài đặt môi trường Development

### 1. Clone repository

```bash
git clone <repository-url>
cd abc
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install Node dependencies

```bash
npm install
```

### 4. Cấu hình môi trường

Tạo file `.env` từ `.env.example` (nếu có):

```bash
cp .env.example .env
```

Sửa file `.env` với thông tin của bạn:

```env
# Database
DB_DRIVER=pgsql
DB_NAME=hrm_dev
DB_USER=postgres
DB_PASSWORD=your_password
DB_HOST=localhost
DB_PORT=5432

# Application
WWWROOT=http://localhost:8000
APP_NAME=HRM
APP_LOCALE=vi
APP_ENV=local
APP_DEBUG=true

# Logging
LOG_LEVEL=debug
APP_LOG=true
```

### 5. Tạo database

```bash
# Kết nối PostgreSQL
psql -U postgres

# Tạo database
CREATE DATABASE hrm_dev;
```

### 6. Chạy migrations

```bash
php migrate.php
```

### 7. Build Assets

```bash
# Build tất cả (Tailwind + SCSS + JS)
npm run build
```

Hoặc chạy watch mode khi dev (tự động rebuild khi file thay đổi):

```bash
# Terminal 1: Watch Tailwind CSS
npm run watch-tailwind

# Terminal 2: Watch JavaScript
npm run watch-js

# Terminal 3: Watch SCSS
npm run watch-sass
```

### 8. Chạy Development Server

```bash
php -S localhost:8000
```

Truy cập: **http://localhost:8000**

### 9. Kiểm tra logs

Logs được lưu trong thư mục `logs/`:
- `YYYY-MM-DD.log` - Logs chung (info, debug, warning)
- `YYYY-MM-DD-error.log` - Error logs
- `YYYY-MM-DD-critical.log` - Critical logs

---

## 🏭 Triển khai Production

### 1. Cài đặt dependencies

```bash
# PHP dependencies (không cài dev dependencies)
composer install --no-dev --optimize-autoloader

# Node dependencies
npm install
```

### 2. Build assets cho production

```bash
npm run build
```

### 3. Cấu hình môi trường

Sửa `.env` với thông tin production:

```env
# Database
DB_DRIVER=pgsql
DB_NAME=hrm_prod
DB_USER=prod_user
DB_PASSWORD=strong_password_here
DB_HOST=db.example.com
DB_PORT=5432

# Application
WWWROOT=https://hrm.example.com
APP_NAME=HRM
APP_LOCALE=vi
APP_ENV=production
APP_DEBUG=false

# Logging
LOG_LEVEL=error
APP_LOG=true
```

### 4. Chạy migrations

```bash
php migrate.php
```

### 5. Kiểm tra permissions

```bash
# Đảm bảo logs/ có quyền write
chmod 755 logs/
chmod 644 logs/*.log
```

### 6. Cấu hình web server

#### Apache (.htaccess)

Đảm bảo tất cả requests đi qua `index.php`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [QSA,L]
</IfModule>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name hrm.example.com;
    root /path/to/abc;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\. {
        deny all;
    }
}
```

### 7. Tối ưu hóa

```bash
# Optimize Composer autoloader
composer dump-autoload --optimize --classmap-authoritative

# Clear logs cũ (nếu cần)
find logs/ -name "*.log" -mtime +30 -delete
```

---

## 📁 Cấu trúc dự án

```
├── assets/                 # Thư viện bên thứ 3 (jQuery, Kendo, Bootstrap, FontAwesome)
│   ├── jquery/
│   ├── kendo/
│   ├── bootstrap-5/
│   └── fontawesome/
├── controllers/            # Controllers xử lý requests
│   ├── AuthController.php
│   └── DashboardController.php
├── core/                   # Core framework
│   ├── Application.php     # Application bootstrap
│   ├── Container.php       # DI Container
│   ├── Controller.php      # Base Controller
│   ├── Model.php           # Base Model
│   ├── ServiceProvider.php # Service Provider interface
│   ├── Providers/          # Service Providers
│   │   ├── ConfigServiceProvider.php
│   │   ├── LoggingServiceProvider.php
│   │   ├── DatabaseServiceProvider.php
│   │   ├── AuthServiceProvider.php
│   │   ├── ViewServiceProvider.php
│   │   ├── HttpServiceProvider.php
│   │   └── TranslatorServiceProvider.php
│   ├── Logging/            # Logging system
│   │   ├── Logger.php
│   │   └── FileLogger.php
│   ├── Database/           # Database helpers
│   │   └── Transaction.php
│   ├── Auth/               # Authentication
│   │   ├── Auth.php
│   │   └── AuthMiddleWare.php
│   ├── Http/               # HTTP utilities
│   │   ├── ResponseFactory.php
│   │   └── HttpException.php
│   ├── View/               # View system
│   │   ├── ViewFactory.php
│   │   ├── ViewRenderer.php
│   │   └── Extensions/
│   ├── helpers.php         # Helper functions
│   └── ...
├── lang/                   # Translation files
│   ├── en/
│   └── vi/
├── logs/                   # Application logs
├── migrations/             # Database migrations
├── models/                 # Data models
│   └── User.php
├── public/                 # Compiled assets (JS, CSS) - browser sử dụng
│   ├── css/
│   └── js/
├── resources/              # Source files (JS, SCSS, CSS) - cần build
│   ├── css/
│   ├── js/
│   └── scss/
├── routes/                 # Route definitions
│   └── web.php
├── views/                  # View templates (Plates)
│   ├── layouts/
│   ├── auth/
│   ├── dashboard/
│   └── errors/
├── vendor/                 # Composer dependencies
├── composer.json
├── composer.lock
├── package.json
├── index.php               # Entry point
├── migrate.php             # Migration runner
└── README.md
```

---

## 🔧 Tech Stack

| Thành phần | Công nghệ | Mô tả |
|------------|-----------|-------|
| **Language** | PHP 8.3+ | Backend language |
| **Database** | PostgreSQL + Doctrine DBAL | Database abstraction layer |
| **Routing** | League Route | HTTP routing |
| **Templating** | League Plates | Template engine |
| **HTTP** | PSR-7 (Laminas Diactoros) | HTTP message interfaces |
| **Container** | PSR-11 Custom Container | Dependency Injection với circular dependency detection |
| **CSS** | Tailwind CSS + SCSS | Utility-first CSS framework |
| **JS** | Alpine.js + ES6 | Lightweight JavaScript framework |
| **Build Tools** | esbuild + Sass | JavaScript và SCSS compiler |
| **Logging** | Custom FileLogger | File-based logging với rotation |

---

## 📚 Documentation

| File | Mô tả |
|------|-------|
| **[DEVELOP.md](DEVELOP.md)** | Hướng dẫn phát triển chi tiết cho developers |
| **README.md** | Tài liệu setup và deployment (file này) |

---

## 🛠️ Development Commands

```bash
# PHP
composer install              # Install PHP dependencies
composer dump-autoload        # Regenerate autoloader
php migrate.php               # Run migrations
php -S localhost:8000        # Start dev server

# Node.js
npm install                  # Install Node dependencies
npm run build                # Build all assets
npm run build-js             # Build JavaScript only
npm run build-sass           # Build SCSS only
npm run build-tailwind       # Build Tailwind CSS only
npm run watch-js             # Watch JavaScript files
npm run watch-sass           # Watch SCSS files
npm run watch-tailwind       # Watch Tailwind CSS files
```

---

## 🔍 Troubleshooting

### Database connection failed
- Kiểm tra PostgreSQL đang chạy: `pg_isready`
- Kiểm tra thông tin trong `.env` (DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD)
- Kiểm tra firewall cho phép kết nối PostgreSQL

### Assets không load
- Chạy `npm run build` để build assets
- Kiểm tra file tồn tại trong `public/css/` và `public/js/`
- Kiểm tra permissions của thư mục `public/`

### Logs không được ghi
- Kiểm tra thư mục `logs/` có quyền write: `chmod 755 logs/`
- Kiểm tra `APP_LOG=true` trong `.env`
- Kiểm tra `LOG_LEVEL` trong `.env` (phải >= level của log message)

### Circular dependency error
- Kiểm tra dependencies trong constructor
- Sử dụng `alias()` để bind interface với concrete class
- Xem logs để biết dependency chain gây circular

---

## 📝 License

[Your License Here]

## 👥 Team

[Your Team Info]
