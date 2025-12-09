# 🛠️ Developer Guide - Hướng dẫn phát triển

Tài liệu này dành cho developers và người mới join dự án.

---

## 📐 Kiến trúc tổng quan

```
Request → index.php → Application.bootstrap() → Router → Controller → Response
                            ↓
                      Container (DI)
                            ↓
                Auto-inject: ResponseFactory, DB, Services
```

---

## 🛤️ Routes - Khai báo đường dẫn

**File:** `routes/web.php`

```php
use App\Controllers\UserController;
use League\Route\Router;
use App\Core\Container;

return function(Router $router, Container $container) {
    // GET request
    $router->get('/users', route($container, UserController::class, 'index'));
    
    // POST request
    $router->post('/users', route($container, UserController::class, 'store'));
    
    // Route với tham số
    $router->get('/users/{id:number}', route($container, UserController::class, 'show'));
    
    // PUT/DELETE request
    $router->put('/users/{id:number}', route($container, UserController::class, 'update'));
    $router->delete('/users/{id:number}', route($container, UserController::class, 'destroy'));
};
```

**Cú pháp route parameters:**
| Pattern | Mô tả | Ví dụ |
|---------|-------|-------|
| `{id}` | Tham số bất kỳ | `/users/{id}` |
| `{id:number}` | Chỉ số | `/users/{id:number}` |
| `{slug:word}` | Chỉ chữ cái | `/posts/{slug:word}` |

---

## 🎮 Controller - Xử lý request

**File:** `controllers/UserController.php`

```php
<?php
namespace App\Controllers;

use App\Core\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserController extends Controller
{
    // Danh sách
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $users = $this->db->fetchAllAssociative('SELECT * FROM users');
        
        return $this->response->view('users/index', [
            'users' => $users,
            'active_menu' => 'users'
        ], 'Danh sách Users');
    }
    
    // Chi tiết - có tham số từ URL
    public function show(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $user = $this->db->fetchAssociative(
            'SELECT * FROM users WHERE id = ?', 
            [$args['id']]
        );
        
        if (!$user) {
            return $this->response->error('Không tìm thấy', null, 404);
        }
        
        return $this->response->view('users/show', compact('user'), 'Chi tiết');
    }
    
    // Tạo mới (POST)
    public function store(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        
        $this->db->insert('users', [
            'name' => $data['name'],
            'email' => $data['email']
        ]);
        
        return $this->response->redirect('/users');
    }
}
```

**Biến có sẵn trong Controller:**

| Biến | Mô tả |
|------|-------|
| `$this->db` | Database connection (Doctrine DBAL) |
| `$this->response` | Response factory |
| `$this->container` | DI Container |

---

## 🖼️ Views - Giao diện

### Cấu trúc thư mục

```
views/
├── layouts/
│   ├── app.php              # Layout chính (sidebar, header, footer)
│   ├── auth.php             # Layout login/register
│   └── partials/
│       ├── head.php         # CSS links
│       ├── header.php       # Header
│       ├── sidebar.php      # Menu sidebar
│       ├── footer.php       # Footer
│       ├── breadcrumb.php   # Breadcrumb
│       └── scripts.php      # JS scripts
├── users/
│   ├── index.php
│   └── show.php
├── dashboard/
│   └── index.php
└── errors/
    ├── 404.php
    └── 500.php
```

### Tạo view mới

**File:** `views/users/index.php`

```php
<?php $this->layout('layouts/app', $this->data) ?>

<h1>Danh sách Users</h1>

<table class="table">
    <?php foreach ($users as $user): ?>
    <tr>
        <td><?= $this->e($user['name']) ?></td>
        <td><?= $this->e($user['email']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= $this->public('/css/users') ?>">
<?= $this->end() ?>

<?= $this->start('scripts') ?>
<script src="<?= $this->public('/js/users/index') ?>"></script>
<?= $this->end() ?>
```

### Các method View hữu ích

| Method | Mô tả | Ví dụ |
|--------|-------|-------|
| `$this->layout()` | Sử dụng layout | `$this->layout('layouts/app', $this->data)` |
| `$this->e()` | Escape HTML (chống XSS) | `$this->e($user['name'])` |
| `$this->insert()` | Include partial | `$this->insert('layouts/partials/header')` |
| `$this->public()` | Link file trong `public/` | `$this->public('/js/app')` → `/public/js/app.min.js` |
| `$this->asset()` | Link file trong `assets/` | `$this->asset('/kendo/kendo.min.js')` |
| `$this->start()` | Bắt đầu section | `$this->start('scripts')` |
| `$this->end()` | Kết thúc section | `$this->end()` |
| `$this->section()` | Output section | `$this->section('scripts')` |

---

## 🎨 Resources - CSS/JS/SCSS

### Cấu trúc thư mục

```
resources/               # Source files (KHÔNG chạy trực tiếp)
├── css/
│   └── tailwind.css     # Tailwind input
├── js/
│   ├── app.js           # Main JS (Alpine.js)
│   ├── dashboard/
│   │   └── dashboard.js
│   └── helpers/
│       └── utils.js
└── scss/
    ├── app.scss         # Main SCSS (import các file khác)
    ├── _variables.scss  # Biến SCSS
    ├── _mixins.scss     # Mixins
    ├── _base.scss       # Base styles
    ├── _layout.scss     # Layout styles
    └── components/      # Component styles

public/                  # Compiled files (Browser sử dụng)
├── css/
│   ├── tailwind.min.css
│   └── app.min.css
└── js/
    ├── app.min.js
    └── dashboard/
        └── dashboard.min.js
```

### Build commands

```bash
# Build tất cả
npm run build

# Build riêng từng loại
npm run build-tailwind   # Tailwind CSS
npm run build-js         # JavaScript
npm run build-sass       # SCSS

# Watch mode (auto rebuild khi file thay đổi)
npm run watch-tailwind   # → public/css/tailwind.min.css
npm run watch-js         # → public/js/
npm run watch-sass       # → public/css/app.min.css
```

### Tạo JS module mới

1. Tạo file: `resources/js/users/index.js`
2. Build: `npm run build-js`
3. Sử dụng trong view:
   ```php
   <script src="<?= $this->public('/js/users/index') ?>"></script>
   ```

### Tạo SCSS component

1. Tạo file: `resources/scss/components/_button.scss`
2. Import trong `resources/scss/app.scss`:
   ```scss
   @use 'components/button';
   ```
3. Build: `npm run build-sass`

---

## 📊 Response Types

```php
// HTML View
return $this->response->view('users/index', $data, 'Page Title');

// JSON data
return $this->response->json(['key' => 'value']);

// JSON success (API)
return $this->response->success($data, 'Thành công');

// JSON error (API)
return $this->response->error('Lỗi', ['field' => 'error message'], 422);

// Redirect
return $this->response->redirect('/dashboard');

// Plain text
return $this->response->text('Hello World');
```

---

## 🗄️ Database Operations

```php
// Lấy tất cả records
$users = $this->db->fetchAllAssociative('SELECT * FROM users');

// Lấy một record
$user = $this->db->fetchAssociative('SELECT * FROM users WHERE id = ?', [1]);

// Lấy một giá trị
$count = $this->db->fetchOne('SELECT COUNT(*) FROM users');

// Insert
$this->db->insert('users', [
    'name' => 'John',
    'email' => 'john@example.com'
]);
$id = $this->db->lastInsertId();

// Update
$this->db->update('users',
    ['name' => 'Jane'],           // data
    ['id' => 1]                   // where
);

// Delete
$this->db->delete('users', ['id' => 1]);

// Query Builder
$qb = $this->db->createQueryBuilder();
$users = $qb->select('*')
    ->from('users')
    ->where('status = :status')
    ->setParameter('status', 'active')
    ->executeQuery()
    ->fetchAllAssociative();
```

---

## ⚙️ Tạo Service mới

### 1. Tạo Service class

**File:** `services/EmailService.php`

```php
<?php
namespace App\Services;

class EmailService
{
    private \stdClass $config;

    public function __construct(\stdClass $config)
    {
        $this->config = $config;
    }

    public function send(string $to, string $subject, string $body): bool
    {
        // Logic gửi email
        return true;
    }
}
```

### 2. Đăng ký trong Container

**File:** `core/Application.php` - thêm method:

```php
protected function registerCustomServices(): void
{
    $this->container->singleton(\App\Services\EmailService::class, function($c) {
        return new \App\Services\EmailService($c->get('config'));
    });
}
```

Gọi trong `bootstrap()`:

```php
public function bootstrap(): void
{
    // ... existing code
    $this->registerCustomServices();
    $this->bootstrapped = true;
}
```

### 3. Sử dụng trong Controller

```php
use App\Services\EmailService;

class ContactController extends Controller
{
    private EmailService $email;

    public function __construct(
        ResponseFactory $response,
        Connection $db,
        Container $container,
        EmailService $email  // Auto-injected bởi Container
    ) {
        parent::__construct($response, $db, $container);
        $this->email = $email;
    }

    public function send(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        $this->email->send($data['email'], 'Subject', $data['message']);
        return $this->response->success(null, 'Email sent!');
    }
}
```

---

## ✅ Checklist tạo feature mới

- [ ] Tạo Controller trong `controllers/`
- [ ] Đăng ký routes trong `routes/web.php`
- [ ] Tạo views trong `views/`
- [ ] Tạo JS (nếu cần) trong `resources/js/`
- [ ] Tạo SCSS (nếu cần) trong `resources/scss/`
- [ ] Build assets: `npm run build`
- [ ] Test trên browser

---

## ❌ Những điều KHÔNG nên làm

```php
// ❌ Không dùng global
global $DB;

// ❌ Không thiếu type hints
public function index($request) { }

// ❌ Không echo/print trực tiếp
echo $this->templates->render(...);

// ❌ Không tạo instance thủ công
$db = new Connection(...);
```

**✅ Cách đúng:**

```php
// Sử dụng injected dependencies
public function index(ServerRequestInterface $request): ResponseInterface
{
    $users = $this->db->fetchAllAssociative('...');
    return $this->response->view('users/index', compact('users'), 'Users');
}
```

---

## 🆘 Gặp vấn đề?

1. **Check logs:** `logs/errors.log`
2. **Rebuild assets:** `npm run build`
3. Hỏi team members

---

## 📚 Tài liệu tham khảo

- [Doctrine DBAL](https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/)
- [League Route](https://route.thephpleague.com/)
- [League Plates](https://platesphp.com/)
- [PSR-7 HTTP Messages](https://www.php-fig.org/psr/psr-7/)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Alpine.js](https://alpinejs.dev/)

