# 🏢 HRM - Human Resource Management System

Hệ thống quản lý nhân sự được xây dựng với PHP 8.3+ và kiến trúc MVC hiện đại.

## 🚀 Yêu cầu hệ thống

- PHP >= 8.3 (Extensions: pdo_pgsql, mbstring)
- PostgreSQL
- Composer
- Node.js >= 18 (để build assets)

---

## 📦 Cài đặt môi trường Development

### 1. Clone & Install dependencies

```bash
git clone <repository-url>
cd human-resource/src

# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 2. Cấu hình môi trường

```bash
cp .env.example .env
```

Sửa file `.env`:

```env
DB_NAME=hrm_dev
DB_USER=postgres
DB_PASSWORD=your_password
DB_HOST=localhost
DB_PORT=5432
WWWROOT=http://localhost:8000
```

### 3. Build Assets

```bash
# Build tất cả (Tailwind + SCSS + JS)
npm run build
```

Hoặc chạy watch mode khi dev:

```bash
npm run watch-tailwind  # Watch Tailwind CSS
npm run watch-js        # Watch JavaScript
npm run watch-sass      # Watch SCSS
```

### 4. Chạy Development Server

```bash
php -S localhost:8000
```

Truy cập: http://localhost:8000

---

## 🏭 Triển khai Production

### 1. Build assets

```bash
npm run build
```

### 2. Cấu hình môi trường

Sửa `.env` với thông tin production:

```env
DB_NAME=hrm_prod
DB_USER=prod_user
DB_PASSWORD=strong_password
DB_HOST=db.example.com
DB_PORT=5432
WWWROOT=https://hrm.example.com
```

### 3. Kiểm tra permissions

```bash
# Đảm bảo logs/ có quyền write
chmod 755 logs/
```

### 4. Cấu hình web server

Trỏ document root vào thư mục chứa source và đảm bảo tất cả requests đi qua `index.php`.

---

## 📁 Cấu trúc dự án

```
src/
├── core/               # Core framework (Application, Container, Controller...)
├── controllers/        # Controllers xử lý requests
├── models/             # Data models
├── views/              # View templates (Plates)
│   └── layouts/        # Layout templates
├── routes/             # Route definitions
│   └── web.php         # Web routes
├── services/           # Business logic services
├── resources/          # Source files (JS, SCSS, CSS) - cần build
│   ├── js/
│   ├── scss/
│   └── css/
├── public/             # Compiled assets (JS, CSS) - browser sử dụng
├── assets/             # Thư viện bên thứ 3 (jQuery, Kendo, Bootstrap...)
├── logs/               # Error logs
└── index.php           # Entry point
```

---

## 🔧 Tech Stack

| Thành phần | Công nghệ |
|------------|-----------|
| Language | PHP 8.3+ |
| Database | PostgreSQL + Doctrine DBAL |
| Routing | League Route |
| Templating | League Plates |
| HTTP | PSR-7 (Laminas Diactoros) |
| Container | PSR-11 Custom Container |
| CSS | Tailwind CSS + SCSS |
| JS | Alpine.js + ES6 |
| Build | esbuild + Sass |

---

## 📚 Documentation

| File | Mô tả |
|------|-------|
| **[DEVELOP.md](DEVELOP.md)** | Hướng dẫn phát triển cho developers |

---

## 📝 License

[Your License Here]

## 👥 Team

[Your Team Info]

