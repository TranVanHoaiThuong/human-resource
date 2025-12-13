# 🛠️ Developer Guide - Hướng dẫn phát triển

Tài liệu này dành cho developers và người mới join dự án. Đọc kỹ để hiểu rõ kiến trúc, luồng xử lý và cách code đúng chuẩn.

---

## 📐 Kiến trúc tổng quan

### Luồng xử lý request

```
HTTP Request
    ↓
index.php (Entry Point)
    ↓
Application::bootstrap()
    ├── Load .env
    ├── Register Service Providers
    │   ├── ConfigServiceProvider
    │   ├── LoggingServiceProvider
    │   ├── DatabaseServiceProvider
    │   ├── AuthServiceProvider
    │   ├── ViewServiceProvider
    │   ├── HttpServiceProvider
    │   └── TranslatorServiceProvider
    └── Boot Service Providers
    ↓
Router (League Route)
    ├── Load routes/web.php
    ├── Match route pattern
    └── Resolve Controller từ Container (auto-wiring)
    ↓
Controller
    ├── Process request
    ├── Call Model/Service
    └── Return Response (HTML/JSON/Redirect)
    ↓
Response → Browser
```

### Dependency Injection Flow

```
Container
    ├── Singleton: Config, DB, Logger, Auth, View, Response
    ├── Factory: ViewRenderer, AuthMiddleware
    └── Auto-wiring: Controllers, Services
```

---

## 🏗️ Cấu trúc Source Code

### 1. Entry Point (`index.php`)

File này là điểm khởi đầu của ứng dụng:

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

// 1. Bootstrap Application
$app = new Application(__DIR__);
$app->bootstrap();
$container = $app->getContainer();

// 2. Setup Error Handler
$errorHandler = new ErrorHandler(__DIR__);
$errorHandler->register();
$errorHandler->setViewEngine($container->get('view.engine'));

// 3. Setup Router
$strategy = new ApplicationStrategy();
$strategy->setContainer($container);

$router = new Router();
$router->setStrategy($strategy);

// 4. Load Routes
$routeSetup = require __DIR__ . '/routes/web.php';
$routeSetup($router, $container);

// 5. Dispatch Request
$request = ServerRequestFactory::fromGlobals();
$response = $router->dispatch($request);

// 6. Send Response
http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}
echo $response->getBody();
```

### 2. Application Bootstrap (`core/Application.php`)

Application class quản lý việc khởi tạo và đăng ký services thông qua Service Providers:

```php
class Application
{
    protected array $providers = [
        ConfigServiceProvider::class,      // Đăng ký config
        LoggingServiceProvider::class,     // Đăng ký logger
        DatabaseServiceProvider::class,    // Đăng ký database
        AuthServiceProvider::class,        // Đăng ký auth
        ViewServiceProvider::class,        // Đăng ký view engine
        HttpServiceProvider::class,        // Đăng ký HTTP services
        TranslatorServiceProvider::class,  // Đăng ký translator
    ];

    public function bootstrap(): void
    {
        $this->loadEnvironment();           // Load .env
        $this->registerServiceProviders();  // Đăng ký services
        $this->bootServiceProviders();      // Boot services
    }
}
```

### 3. Service Provider Pattern

Mỗi Service Provider chịu trách nhiệm đăng ký một nhóm services liên quan:

```php
class DatabaseServiceProvider implements ServiceProvider
{
    public function register(Container $container): void
    {
        // Đăng ký database connection
        $container->singleton('db', function ($c) {
            $config = $c->get('config');
            return DriverManager::getConnection([...]);
        });
    }

    public function boot(Container $container): void
    {
        // Thực hiện các tác vụ sau khi tất cả providers đã register
        register_shutdown_function(function () use ($container) {
            // Close database connection
        });
    }
}
```

**Thêm Service Provider mới:**

1. Tạo file `core/Providers/MyServiceProvider.php`
2. Implement `ServiceProvider` interface
3. Thêm vào `$providers` array trong `Application.php`

---

## 🛤️ Routes - Khai báo đường dẫn

**File:** `routes/web.php`

### Cú pháp cơ bản

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
    
    // Route group với middleware
    $router->group('', function($group) use ($container) {
        $group->get('/dashboard', route($container, DashboardController::class, 'index'));
    })->middleware(new AuthMiddleWare($container->get('auth')));
};
```

### Route Parameters

| Pattern | Mô tả | Ví dụ |
|---------|-------|-------|
| `{id}` | Tham số bất kỳ | `/users/{id}` → `/users/123` |
| `{id:number}` | Chỉ số | `/users/{id:number}` → `/users/123` |
| `{slug:word}` | Chỉ chữ cái | `/posts/{slug:word}` → `/posts/my-post` |

### Helper function `route()`

Function `route()` tạo closure để resolve controller từ Container với auto-wiring:

```php
function route(Container $container, string $controller, string $method): Closure
{
    return function($request, array $args = []) use ($container, $controller, $method) {
        $instance = $container->make($controller);  // Auto-wiring dependencies
        return $instance->$method($request, $args);
    };
}
```

---

## 🎮 Controller - Xử lý request

### Cấu trúc Controller

**File:** `controllers/UserController.php`

```php
<?php
namespace App\Controllers;

use App\Core\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Doctrine\DBAL\Connection;
use App\Core\Http\ResponseFactory;
use App\Core\Container;

class UserController extends Controller
{
    // Controller tự động nhận dependencies qua constructor
    // Container sẽ auto-inject: ResponseFactory, Connection, Container
    
    /**
     * Danh sách users
     */
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $users = $this->db->fetchAllAssociative('SELECT * FROM users ORDER BY id DESC');
        
        return $this->response->view('users/index', [
            'users' => $users,
            'active_menu' => 'users'
        ], 'Danh sách Users');
    }
    
    /**
     * Chi tiết user - có tham số từ URL
     */
    public function show(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $id = (int)$args['id'];
        $user = $this->db->fetchAssociative(
            'SELECT * FROM users WHERE id = ?', 
            [$id]
        );
        
        if (!$user) {
            return $this->response->error('Không tìm thấy user', null, 404);
        }
        
        return $this->response->view('users/show', compact('user'), 'Chi tiết User');
    }
    
    /**
     * Tạo user mới (POST)
     */
    public function store(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        
        // Validate (sẽ implement sau)
        // ...
        
        $this->db->insert('users', [
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
        ]);
        
        return $this->response->redirect('/users');
    }
}
```

### Biến có sẵn trong Controller

| Biến | Type | Mô tả |
|------|------|-------|
| `$this->db` | `Connection` | Database connection (Doctrine DBAL) |
| `$this->response` | `ResponseFactory` | Response factory để tạo responses |
| `$this->container` | `Container` | DI Container |

### Inject thêm dependencies

```php
use App\Core\Logging\Logger;
use App\Models\User;

class UserController extends Controller
{
    protected Logger $logger;
    protected User $userModel;

    public function __construct(
        ResponseFactory $response,
        Connection $db,
        Container $container,
        Logger $logger,        // Auto-injected
        User $userModel       // Auto-injected
    ) {
        parent::__construct($response, $db, $container);
        $this->logger = $logger;
        $this->userModel = $userModel;
    }
}
```

---

## 🖼️ Views - Giao diện

### Cấu trúc thư mục

```
views/
├── layouts/
│   ├── app.php              # Layout chính (sidebar, header, footer)
│   ├── auth.php             # Layout login/register
│   └── partials/
│       ├── head.php         # CSS links, meta tags
│       ├── header.php       # Header navigation
│       ├── sidebar.php      # Menu sidebar
│       ├── footer.php       # Footer
│       ├── breadcrumb.php   # Breadcrumb navigation
│       └── scripts.php      # JS scripts
├── users/
│   ├── index.php           # Danh sách users
│   └── show.php            # Chi tiết user
├── dashboard/
│   └── index.php
└── errors/
    ├── 404.php
    └── 500.php
```

### Tạo view mới

**File:** `views/users/index.php`

```php
<?php 
// Sử dụng layout
$this->layout('layouts/app', $this->data) 
?>

<div class="container">
    <h1>Danh sách Users</h1>
    
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $this->e($user['id']) ?></td>
                <td><?= $this->e($user['username']) ?></td>
                <td><?= $this->e($user['email']) ?></td>
                <td>
                    <a href="/users/<?= $user['id'] ?>">Xem</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?= $this->start('styles') ?>
<link rel="stylesheet" href="<?= $this->public('/css/users') ?>">
<?= $this->end() ?>

<?= $this->start('scripts') ?>
<script src="<?= $this->public('/js/users/index') ?>"></script>
<?= $this->end() ?>
```

### View Methods

| Method | Mô tả | Ví dụ |
|--------|-------|-------|
| `$this->layout()` | Sử dụng layout | `$this->layout('layouts/app', $this->data)` |
| `$this->e()` | Escape HTML (chống XSS) | `$this->e($user['name'])` |
| `$this->insert()` | Include partial | `$this->insert('layouts/partials/header')` |
| `$this->public()` | Link file trong `public/` | `$this->public('/js/app')` → `/public/js/app.min.js?v=123456` |
| `$this->asset()` | Link file trong `assets/` | `$this->asset('/kendo/kendo.all.min.js')` → `/assets/kendo/kendo.all.min.js?v=123456` |
| `$this->start()` | Bắt đầu section | `$this->start('scripts')` |
| `$this->end()` | Kết thúc section | `$this->end()` |
| `$this->section()` | Output section | `$this->section('scripts')` |

### Layout Example

**File:** `views/layouts/app.php`

```php
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($title ?? 'HRM') ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= $this->public('/css/tailwind') ?>">
    <link rel="stylesheet" href="<?= $this->public('/css/app') ?>">
    <?= $this->section('styles') ?>
</head>
<body>
    <?= $this->insert('layouts/partials/header') ?>
    
    <div class="flex">
        <?= $this->insert('layouts/partials/sidebar') ?>
        
        <main class="flex-1 p-6">
            <?= $this->insert('layouts/partials/breadcrumb') ?>
            <?= $this->section('content') ?>
        </main>
    </div>
    
    <?= $this->insert('layouts/partials/footer') ?>
    
    <!-- JS -->
    <script src="<?= $this->asset('/jquery/jquery.min.js') ?>"></script>
    <script src="<?= $this->asset('/kendo/kendo.all.min.js') ?>"></script>
    <script src="<?= $this->public('/js/app') ?>"></script>
    <?= $this->section('scripts') ?>
</body>
</html>
```

### View Data

Data được truyền từ Controller:

```php
// Controller
return $this->response->view('users/index', [
    'users' => $users,
    'title' => 'Danh sách Users'
], 'Page Title');

// View - có thể truy cập:
// - $users
// - $title
// - $this->data['users']
// - $this->data['title']
```

---

## 🎨 Assets - CSS/JS/SCSS

### Cấu trúc thư mục

```
resources/               # Source files (KHÔNG chạy trực tiếp)
├── css/
│   └── tailwind.css     # Tailwind input file
├── js/
│   ├── app.js           # Main JS (Alpine.js setup)
│   ├── dashboard/
│   │   └── dashboard.js
│   └── helpers/
│       └── autoload.js
└── scss/
    ├── app.scss         # Main SCSS (import các file khác)
    ├── _variables.scss  # Biến SCSS
    ├── _mixins.scss     # Mixins
    ├── _base.scss       # Base styles
    ├── _layout.scss     # Layout styles
    └── components/      # Component styles
        └── _button.scss

public/                  # Compiled files (Browser sử dụng)
├── css/
│   ├── tailwind.min.css
│   └── app.min.css
└── js/
    ├── app.min.js
    └── dashboard/
        └── dashboard.min.js
```

### Build Commands

```bash
# Build tất cả
npm run build

# Build riêng từng loại
npm run build-tailwind   # Tailwind CSS → public/css/tailwind.min.css
npm run build-js         # JavaScript → public/js/
npm run build-sass       # SCSS → public/css/app.min.css

# Watch mode (auto rebuild khi file thay đổi)
npm run watch-tailwind   # Watch Tailwind CSS
npm run watch-js         # Watch JavaScript
npm run watch-sass       # Watch SCSS
```

### Tạo JavaScript module mới

1. **Tạo file:** `resources/js/users/index.js`

```javascript
// resources/js/users/index.js
document.addEventListener('DOMContentLoaded', function() {
    // Your code here
    console.log('Users page loaded');
    
    // Kendo Grid example
    $('#users-grid').kendoGrid({
        dataSource: {
            transport: {
                read: '/api/users'
            }
        },
        columns: [
            { field: 'id', title: 'ID' },
            { field: 'username', title: 'Username' },
            { field: 'email', title: 'Email' }
        ]
    });
});
```

2. **Build:** `npm run build-js` (hoặc watch mode)

3. **Sử dụng trong view:**

```php
<?= $this->start('scripts') ?>
<script src="<?= $this->public('/js/users/index') ?>"></script>
<?= $this->end() ?>
```

Output: `<script src="/public/js/users/index.min.js?v=1234567890"></script>`

### Tạo SCSS component

1. **Tạo file:** `resources/scss/components/_button.scss`

```scss
// resources/scss/components/_button.scss
.btn {
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
    font-weight: 500;
    
    &-primary {
        background-color: #3b82f6;
        color: white;
    }
    
    &-secondary {
        background-color: #6b7280;
        color: white;
    }
}
```

2. **Import trong:** `resources/scss/app.scss`

```scss
// resources/scss/app.scss
@use 'components/button';
```

3. **Build:** `npm run build-sass`

### Sử dụng trong View

```php
<!-- CSS -->
<link rel="stylesheet" href="<?= $this->public('/css/tailwind') ?>">
<link rel="stylesheet" href="<?= $this->public('/css/app') ?>">

<!-- JS -->
<script src="<?= $this->asset('/jquery/jquery.min.js') ?>"></script>
<script src="<?= $this->asset('/kendo/kendo.all.min.js') ?>"></script>
<script src="<?= $this->public('/js/app') ?>"></script>
```

**Lưu ý:**
- `$this->public()` - cho files trong `public/` (đã build)
- `$this->asset()` - cho files trong `assets/` (thư viện bên thứ 3)
- Tự động thêm version query string (`?v=timestamp`) để cache busting

---

## 📊 Response Types

### HTML View

```php
return $this->response->view('users/index', [
    'users' => $users
], 'Danh sách Users');
```

### JSON Response

```php
// JSON data
return $this->response->json(['key' => 'value']);

// JSON success (API)
return $this->response->success($data, 'Thành công');

// JSON error (API)
return $this->response->error('Lỗi', ['field' => 'error message'], 422);
```

### Redirect

```php
return $this->response->redirect('/dashboard');
```

### Plain Text

```php
return $this->response->text('Hello World');
```

---

## 🗄️ Database Operations

### Sử dụng trực tiếp DBAL

```php
// Lấy tất cả records
$users = $this->db->fetchAllAssociative('SELECT * FROM users');

// Lấy một record
$user = $this->db->fetchAssociative('SELECT * FROM users WHERE id = ?', [1]);

// Lấy một giá trị
$count = $this->db->fetchOne('SELECT COUNT(*) FROM users');

// Insert
$this->db->insert('users', [
    'username' => 'john',
    'email' => 'john@example.com'
]);
$id = $this->db->lastInsertId();

// Update
$this->db->update('users',
    ['email' => 'newemail@example.com'],  // data
    ['id' => 1]                            // where
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

### Sử dụng Model

```php
use App\Models\User;

$userModel = new User($this->db);

// CRUD operations
$users = $userModel->all();
$user = $userModel->find(1);
$user = $userModel->findBy(['email' => 'john@example.com']);
$users = $userModel->where(['status' => 'active']);

$id = $userModel->create([
    'username' => 'john',
    'email' => 'john@example.com'
]);

$userModel->update(1, ['email' => 'newemail@example.com']);
$userModel->delete(1);
```

### Transaction Support

```php
use App\Models\User;

$userModel = new User($this->db);

// Chạy callback trong transaction (tự động rollback nếu có exception)
$userId = $userModel->transaction(function($db) use ($data) {
    $id = $userModel->create($data);
    
    // Nếu có exception ở đây, transaction sẽ tự động rollback
    // ...
    
    return $id;
});

// Hoặc sử dụng transaction helper trực tiếp
$transaction = $userModel->getTransaction();
$transaction->begin();
try {
    $userModel->create($data);
    $transaction->commit();
} catch (\Exception $e) {
    $transaction->rollback();
    throw $e;
}
```

---

## 📝 Logging

### Sử dụng Logger

```php
use App\Core\Logging\Logger;

class UserController extends Controller
{
    protected Logger $logger;

    public function __construct(
        ResponseFactory $response,
        Connection $db,
        Container $container,
        Logger $logger
    ) {
        parent::__construct($response, $db, $container);
        $this->logger = $logger;
    }

    public function store(ServerRequestInterface $request): ResponseInterface
    {
        $this->logger->info('Creating new user', [
            'username' => $data['username'],
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);
        
        // ...
        
        $this->logger->error('Failed to create user', [
            'error' => $e->getMessage()
        ]);
    }
}
```

### Log Levels

```php
$logger->debug('Debug message', ['context' => 'data']);
$logger->info('Info message', ['context' => 'data']);
$logger->warning('Warning message', ['context' => 'data']);
$logger->error('Error message', ['context' => 'data']);
$logger->critical('Critical message', ['context' => 'data']);
```

### Log Files

- `logs/YYYY-MM-DD.log` - Logs chung (info, debug, warning)
- `logs/YYYY-MM-DD-error.log` - Error logs
- `logs/YYYY-MM-DD-critical.log` - Critical logs

---

## ⚙️ Tạo Service mới

### 1. Tạo Service class

**File:** `services/EmailService.php`

```php
<?php
namespace App\Services;

use App\Core\Logging\Logger;
use stdClass;

class EmailService
{
    protected stdClass $config;
    protected ?Logger $logger;

    public function __construct(stdClass $config, ?Logger $logger = null)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    public function send(string $to, string $subject, string $body): bool
    {
        if ($this->logger) {
            $this->logger->info('Sending email', ['to' => $to]);
        }
        
        // Logic gửi email
        return true;
    }
}
```

### 2. Tạo Service Provider

**File:** `core/Providers/EmailServiceProvider.php`

```php
<?php
namespace App\Core\Providers;

use App\Core\Container;
use App\Core\ServiceProvider;
use App\Services\EmailService;

class EmailServiceProvider implements ServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(EmailService::class, function ($c) {
            $logger = $c->has('logger') ? $c->get('logger') : null;
            return new EmailService($c->get('config'), $logger);
        });
    }

    public function boot(Container $container): void
    {
        // Không cần boot logic
    }
}
```

### 3. Đăng ký trong Application

**File:** `core/Application.php`

```php
protected array $providers = [
    // ... existing providers
    EmailServiceProvider::class,
];
```

### 4. Sử dụng trong Controller

```php
use App\Services\EmailService;

class ContactController extends Controller
{
    protected EmailService $email;

    public function __construct(
        ResponseFactory $response,
        Connection $db,
        Container $container,
        EmailService $email  // Auto-injected
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

## 🔐 Authentication

### Sử dụng Auth

```php
use App\Core\Auth\Auth;

// Trong Controller
public function __construct(
    ResponseFactory $response,
    Connection $db,
    Container $container,
    Auth $auth
) {
    parent::__construct($response, $db, $container);
    $this->auth = $auth;
}

// Check authentication
if ($this->auth->check()) {
    $user = $this->auth->user();
    $userId = $this->auth->id();
}

// Login
if ($this->auth->attempt($username, $password, $remember)) {
    // Success
}

// Logout
$this->auth->logout();
```

### Helper functions

```php
// Trong views hoặc helpers
if (auth()->check()) {
    $user = user();
    $userId = user_id();
}
```

---

## ✅ Checklist tạo feature mới

- [ ] Tạo Controller trong `controllers/`
- [ ] Tạo Model (nếu cần) trong `models/`
- [ ] Đăng ký routes trong `routes/web.php`
- [ ] Tạo views trong `views/`
- [ ] Tạo JS (nếu cần) trong `resources/js/`
- [ ] Tạo SCSS (nếu cần) trong `resources/scss/`
- [ ] Build assets: `npm run build`
- [ ] Test trên browser
- [ ] Kiểm tra logs nếu có lỗi

---

## ❌ Coding Rules - Những điều KHÔNG nên làm

```php
// ❌ Không dùng global variables
global $DB;
global $config;

// ❌ Không thiếu type hints
public function index($request) { }

// ❌ Không echo/print trực tiếp
echo $this->templates->render(...);
print_r($data);

// ❌ Không tạo instance thủ công
$db = new Connection(...);
$auth = new Auth(...);

// ❌ Không hardcode paths
require_once '/var/www/app/config.php';

// ❌ Không bỏ qua error handling
$user = $this->db->fetchAssociative('SELECT * FROM users WHERE id = ?', [$id]);
$user['name']; // Có thể null
```

**✅ Cách đúng:**

```php
// ✅ Sử dụng injected dependencies
public function index(ServerRequestInterface $request): ResponseInterface
{
    $users = $this->db->fetchAllAssociative('SELECT * FROM users');
    return $this->response->view('users/index', compact('users'), 'Users');
}

// ✅ Type hints đầy đủ
public function show(ServerRequestInterface $request, array $args): ResponseInterface

// ✅ Error handling
$user = $this->db->fetchAssociative('SELECT * FROM users WHERE id = ?', [$id]);
if (!$user) {
    return $this->response->error('User not found', null, 404);
}

// ✅ Sử dụng helper functions
$path = $this->public('/css/app');
```

---

## 🆘 Troubleshooting

### Circular dependency error

```
Circular dependency detected: ClassA -> ClassB -> ClassA
```

**Giải pháp:**
- Kiểm tra dependencies trong constructor
- Sử dụng `alias()` để bind interface với concrete class
- Xem logs để biết dependency chain

### Assets không load

- Chạy `npm run build` để build assets
- Kiểm tra file tồn tại trong `public/css/` và `public/js/`
- Kiểm tra permissions của thư mục `public/`

### Logs không được ghi

- Kiểm tra thư mục `logs/` có quyền write: `chmod 755 logs/`
- Kiểm tra `APP_LOG=true` trong `.env`
- Kiểm tra `LOG_LEVEL` trong `.env`

---

## 📚 Tài liệu tham khảo

- [Doctrine DBAL](https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/)
- [League Route](https://route.thephpleague.com/)
- [League Plates](https://platesphp.com/)
- [PSR-7 HTTP Messages](https://www.php-fig.org/psr/psr-7/)
- [PSR-11 Container](https://www.php-fig.org/psr/psr-11/)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Alpine.js](https://alpinejs.dev/)
- [Kendo UI](https://docs.telerik.com/kendo-ui)

---

**Happy Coding! 🚀**
