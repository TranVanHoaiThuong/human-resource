# 🏗️ Core Architecture Documentation

## Tổng quan

Dự án sử dụng kiến trúc MVC với Dependency Injection Container, giúp code dễ maintain, test và mở rộng.

## 📁 Cấu trúc thư mục

```
src/
├── core/                    # Core framework
│   ├── Application.php      # Bootstrap ứng dụng
│   ├── Container.php        # DI Container (PSR-11)
│   ├── Controller.php       # Base Controller
│   ├── View/               # View services
│   │   ├── ViewInterface.php
│   │   ├── ViewRenderer.php
│   │   └── ViewFactory.php
│   └── Http/               # HTTP services
│       └── ResponseFactory.php
├── controllers/            # Application controllers
├── models/                 # Data models
├── views/                  # View templates (Plates)
├── routes/                 # Route definitions
│   └── web.php
└── index.php              # Entry point
```

## 🔧 Core Components

### 1. Container (DI Container)

**File:** `core/Container.php`

Container quản lý tất cả dependencies trong ứng dụng. Implement PSR-11 ContainerInterface.

**Tính năng:**
- ✅ Singleton: Instance duy nhất, tái sử dụng
- ✅ Factory: Tạo instance mới mỗi lần
- ✅ Auto-wiring: Tự động inject dependencies

**Ví dụ:**

```php
// Đăng ký singleton
$container->singleton('db', function($c) {
    return new Database($c->get('config'));
});

// Đăng ký factory
$container->bind('mailer', function($c) {
    return new Mailer();
});

// Lấy instance
$db = $container->get('db');

// Auto-resolve với dependencies
$controller = $container->make(UserController::class);
```

### 2. Application

**File:** `core/Application.php`

Class chính để bootstrap ứng dụng.

**Chức năng:**
- Load environment variables (.env)
- Khởi tạo Container
- Đăng ký các services: Config, Database, View, HTTP

**Services được đăng ký:**
- `config` - Application configuration
- `db` / `Connection::class` - Database connection
- `view.engine` / `Engine::class` - Plates template engine
- `view` / `ViewFactory::class` - View factory
- `response` / `ResponseFactory::class` - Response factory

### 3. View Service

**Files:** `core/View/`

Tách biệt logic rendering view khỏi Controller.

**ViewRenderer** - Render views với data
**ViewFactory** - Tạo ViewRenderer instances

**Ví dụ:**

```php
$view = $viewFactory->make();
$html = $view->setTitle('Home Page')
             ->with('user', $user)
             ->render('home/index');
```

### 4. Response Factory

**File:** `core/Http/ResponseFactory.php`

Factory để tạo các loại PSR-7 Response.

**Methods:**
- `view()` - HTML response từ template
- `json()` - JSON response
- `success()` - JSON success response
- `error()` - JSON error response
- `redirect()` - Redirect response
- `text()` - Plain text response

**Ví dụ:**

```php
// HTML
return $response->view('home/index', ['user' => $user], 'Home');

// JSON
return $response->json(['status' => 'ok']);

// Success
return $response->success($data, 'Operation successful');

// Error
return $response->error('Not found', null, 404);

// Redirect
return $response->redirect('/dashboard');
```

### 5. Controller

**File:** `core/Controller.php`

Base controller với dependency injection.

**Dependencies được inject:**
- `$this->response` - ResponseFactory
- `$this->db` - Database connection

**Ví dụ:**

```php
class UserController extends Controller {
    public function index(ServerRequestInterface $request): ResponseInterface {
        $users = $this->db->fetchAllAssociative('SELECT * FROM users');
        return $this->response->view('users/index', ['users' => $users], 'Users');
    }
    
    public function api(ServerRequestInterface $request): ResponseInterface {
        return $this->response->json(['status' => 'ok']);
    }
}
```

### 6. Helper Functions

**File:** `core/helpers.php`

Global helper functions để sử dụng trong ứng dụng.

**Available Helpers:**

**`route($container, $controller, $method)`**
- Tạo route handler với auto-wiring
- Container tự động resolve và inject dependencies

```php
$router->get('/users', route($container, UserController::class, 'index'));
```

**`env($key, $default = null)`**
- Lấy environment variable
- Fallback về default nếu không tìm thấy

```php
$dbHost = env('DB_HOST', 'localhost');
```

**`dd(...$vars)`**
- Dump and die - Debug helper
- Dump variables và dừng execution

```php
dd($user, $products);
```

**`dump(...$vars)`**
- Dump variables - Debug helper
- Dump variables nhưng không dừng execution

```php
dump($data);
```

## 🛣️ Routing

**File:** `routes/web.php`

Routes được định nghĩa với helper function `route()`. Container tự động resolve dependencies.

```php
use App\Controllers\HomeController;

return function(Router $router, Container $container) {
    $router->get('/', route($container, HomeController::class, 'index'));
    $router->get('/about', route($container, HomeController::class, 'about'));
    $router->post('/api/users', route($container, UserController::class, 'store'));
};
```

**Helper `route()`:**
- Tự động resolve controller từ Container
- Auto-inject dependencies vào controller
- Hỗ trợ route parameters qua `$args`

## 🚀 Workflow

1. **Request đến** → `index.php`
2. **Bootstrap Application** → Load config, services
3. **Setup Router** với Container strategy
4. **Load routes** từ `routes/web.php`
5. **Dispatch request** → Router tìm controller
6. **Container resolve** → Tự động inject dependencies vào controller
7. **Controller xử lý** → Trả về Response
8. **Send response** → Client nhận kết quả

## ✨ Best Practices

### Tạo Controller mới

```php
namespace App\Controllers;

use App\Core\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProductController extends Controller {
    public function index(ServerRequestInterface $request): ResponseInterface {
        $products = $this->db->fetchAllAssociative('SELECT * FROM products');
        return $this->response->view('products/index', compact('products'), 'Products');
    }
}
```

### Inject thêm dependencies

```php
use App\Services\EmailService;

class UserController extends Controller {
    private EmailService $emailService;
    
    public function __construct(
        ResponseFactory $response,
        Connection $db,
        EmailService $emailService  // Thêm dependency
    ) {
        parent::__construct($response, $db);
        $this->emailService = $emailService;
    }
}
```

### Đăng ký Service mới

Trong `Application.php`:

```php
protected function registerServices(): void {
    $this->container->singleton(EmailService::class, function($c) {
        return new EmailService($c->get('config'));
    });
}
```

## 🎯 Lợi ích

✅ **Dependency Injection** - Dễ test, dễ thay thế dependencies  
✅ **Separation of Concerns** - View, Controller, Response tách biệt  
✅ **PSR Standards** - PSR-7 (HTTP), PSR-11 (Container)  
✅ **Auto-wiring** - Tự động inject dependencies  
✅ **Type Safety** - Type hints cho tất cả methods  
✅ **Dễ mở rộng** - Thêm services mới rất đơn giản  

## 📝 Migration từ code cũ

**Trước:**
```php
class HomeController extends Controller {
    public function index() {
        global $DB, $CONFIG;
        $this->setPageTitle('Home');
        return $this->view('home/index', ['data' => $data]);
    }
}
```

**Sau:**
```php
class HomeController extends Controller {
    public function index(ServerRequestInterface $request): ResponseInterface {
        // $this->db và $this->response đã được inject
        return $this->response->view('home/index', ['data' => $data], 'Home');
    }
}
```

