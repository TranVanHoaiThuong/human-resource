# 🏢 HRM - Human Resource Management System

Hệ thống quản lý nhân sự được xây dựng với PHP 8.3+ và kiến trúc MVC hiện đại.

## ✨ Tính năng Core Framework

- ✅ **Dependency Injection Container** (PSR-11)
- ✅ **Auto-wiring** - Tự động inject dependencies
- ✅ **View Service** - Tách biệt view rendering
- ✅ **Response Factory** - Hỗ trợ HTML, JSON, Redirect
- ✅ **PSR-7 HTTP Messages** - Request/Response chuẩn
- ✅ **Type Safety** - Type hints đầy đủ
- ✅ **Clean Architecture** - Dễ maintain và mở rộng

## 🚀 Yêu cầu

- PHP >= 8.3
- PostgreSQL
- Composer
- Extensions: pdo_pgsql, mbstring

## 📦 Cài đặt

1. Clone repository:
```bash
git clone <repository-url>
cd human-resource/src
```

2. Install dependencies:
```bash
composer install
```

3. Copy và cấu hình file .env:
```bash
cp .env.example .env
```

Cấu hình database trong `.env`:
```env
DB_NAME=your_database
DB_USER=your_username
DB_PASSWORD=your_password
DB_HOST=localhost
DB_PORT=5432
WWWROOT=http://localhost
```

4. Test core framework:
```bash
php test_core.php
```

5. Chạy development server:
```bash
php -S localhost:8000
```

Truy cập: http://localhost:8000

## 📁 Cấu trúc dự án

```
src/
├── core/                    # Core framework
│   ├── Application.php      # Bootstrap ứng dụng
│   ├── Container.php        # DI Container
│   ├── Controller.php       # Base Controller
│   ├── View/               # View services
│   └── Http/               # HTTP services
├── controllers/            # Application controllers
├── models/                 # Data models
├── views/                  # View templates
├── routes/                 # Route definitions
├── services/               # Business logic services
├── index.php              # Entry point
└── test_core.php          # Core tests
```

## 📚 Documentation

- **[CORE_ARCHITECTURE.md](CORE_ARCHITECTURE.md)** - Chi tiết kiến trúc core framework
- **[EXAMPLES.md](EXAMPLES.md)** - Ví dụ và hướng dẫn sử dụng

## 🎯 Quick Start

### Tạo Controller mới

```php
<?php
namespace App\Controllers;

use App\Core\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserController extends Controller
{
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $users = $this->db->fetchAllAssociative('SELECT * FROM users');
        return $this->response->view('users/index', compact('users'), 'Users');
    }
    
    public function api(ServerRequestInterface $request): ResponseInterface
    {
        return $this->response->json(['status' => 'ok']);
    }
}
```

### Đăng ký Routes

```php
// routes/web.php
use App\Controllers\UserController;

return function(Router $router, Container $container) {
    $router->get('/users', route($container, UserController::class, 'index'));
    $router->get('/api/users', route($container, UserController::class, 'api'));
};
```

## 🧪 Testing

Chạy test core framework:

```bash
php test_core.php
```

Test sẽ kiểm tra:
- ✅ Application bootstrap
- ✅ Container và DI
- ✅ Database connection
- ✅ View services
- ✅ Response factory
- ✅ Auto-wiring

## 🔧 Tech Stack

- **PHP 8.3+** - Language
- **PostgreSQL** - Database
- **Doctrine DBAL** - Database abstraction
- **League Route** - Routing
- **League Plates** - Templating
- **PSR-7** - HTTP Messages
- **PSR-11** - Container Interface
- **Bootstrap 5** - Frontend

## 📖 Workflow

1. Request → `index.php`
2. Bootstrap Application với Container
3. Setup Router với auto-wiring
4. Load routes
5. Dispatch request
6. Container auto-inject dependencies vào Controller
7. Controller xử lý và trả về Response
8. Send response to client

## 🎨 Best Practices

1. **Luôn sử dụng type hints** cho parameters và return types
2. **Inject dependencies** qua constructor, không dùng global
3. **Tách business logic** ra Services
4. **Controller chỉ điều phối**, không chứa logic phức tạp
5. **Sử dụng PSR standards** (PSR-7, PSR-11)

## 🤝 Contributing

Khi join vào dự án:

1. Đọc **CORE_ARCHITECTURE.md** để hiểu kiến trúc
2. Xem **EXAMPLES.md** để học cách sử dụng
3. Chạy `php test_core.php` để đảm bảo core hoạt động
4. Follow coding standards và best practices

## 📝 License

[Your License Here]

## 👥 Team

[Your Team Info]

---

**Happy Coding! 🚀**

