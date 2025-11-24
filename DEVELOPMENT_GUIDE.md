# 🛠️ Development Guide

Hướng dẫn phát triển features mới trong HRM system.

## 📋 Checklist khi tạo Feature mới

### 1. Planning
- [ ] Xác định requirements
- [ ] Thiết kế database schema (nếu cần)
- [ ] Xác định routes cần thiết
- [ ] Xác định views cần thiết

### 2. Database (nếu cần)
- [ ] Tạo migration/SQL script
- [ ] Tạo Model class trong `models/`
- [ ] Test database queries

### 3. Service Layer (nếu có business logic phức tạp)
- [ ] Tạo Service class trong `services/`
- [ ] Đăng ký Service vào Container (trong `Application.php`)
- [ ] Implement business logic

### 4. Controller
- [ ] Tạo Controller trong `controllers/`
- [ ] Extend từ `App\Core\Controller`
- [ ] Inject dependencies qua constructor
- [ ] Implement các methods với type hints
- [ ] Sử dụng `$this->response` để trả về responses
- [ ] Sử dụng `$this->db` để query database

### 5. Views
- [ ] Tạo view templates trong `views/`
- [ ] Sử dụng layout chung nếu có
- [ ] Pass data từ controller vào view

### 6. Routes
- [ ] Đăng ký routes trong `routes/web.php`
- [ ] Sử dụng helper `route()`: `route($container, Controller::class, 'method')`
- [ ] Test routes hoạt động

### 7. Testing
- [ ] Test manually qua browser
- [ ] Test API endpoints (nếu có)
- [ ] Verify database operations
- [ ] Check error handling

## 🎯 Coding Standards

### Controller Standards

```php
<?php
namespace App\Controllers;

use App\Core\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Controller description
 */
class ExampleController extends Controller
{
    /**
     * Method description
     * 
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        // Implementation
    }
}
```

### Service Standards

```php
<?php
namespace App\Services;

/**
 * Service description
 */
class ExampleService
{
    private \stdClass $config;
    
    public function __construct(\stdClass $config)
    {
        $this->config = $config;
    }
    
    public function doSomething(): bool
    {
        // Business logic
        return true;
    }
}
```

### Model Standards

```php
<?php
namespace App\Models;

use Doctrine\DBAL\Connection;

/**
 * Model description
 */
class User
{
    private Connection $db;
    
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }
    
    public function findById(int $id): ?array
    {
        return $this->db->fetchAssociative(
            'SELECT * FROM users WHERE id = ?',
            [$id]
        );
    }
    
    public function all(): array
    {
        return $this->db->fetchAllAssociative('SELECT * FROM users');
    }
}
```

## 🔧 Common Patterns

### Pattern 1: CRUD Controller

```php
class UserController extends Controller
{
    // List all
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $users = $this->db->fetchAllAssociative('SELECT * FROM users');
        return $this->response->view('users/index', compact('users'), 'Users');
    }
    
    // Show create form
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        return $this->response->view('users/create', [], 'Create User');
    }
    
    // Store new record
    public function store(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        $this->db->insert('users', $data);
        return $this->response->redirect('/users');
    }
    
    // Show single record
    public function show(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $user = $this->db->fetchAssociative('SELECT * FROM users WHERE id = ?', [$args['id']]);
        return $this->response->view('users/show', compact('user'), 'User Details');
    }
    
    // Show edit form
    public function edit(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $user = $this->db->fetchAssociative('SELECT * FROM users WHERE id = ?', [$args['id']]);
        return $this->response->view('users/edit', compact('user'), 'Edit User');
    }
    
    // Update record
    public function update(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $data = $request->getParsedBody();
        $this->db->update('users', $data, ['id' => $args['id']]);
        return $this->response->redirect('/users');
    }
    
    // Delete record
    public function destroy(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $this->db->delete('users', ['id' => $args['id']]);
        return $this->response->redirect('/users');
    }
}
```

### Pattern 2: API Controller

```php
class UserApiController extends Controller
{
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $users = $this->db->fetchAllAssociative('SELECT * FROM users');
        return $this->response->success($users);
    }
    
    public function store(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        
        // Validation
        if (empty($data['email'])) {
            return $this->response->error('Email is required', null, 422);
        }
        
        $this->db->insert('users', $data);
        return $this->response->success(['id' => $this->db->lastInsertId()], 'Created', 201);
    }
}
```

### Pattern 3: Service Injection

```php
class OrderController extends Controller
{
    private OrderService $orderService;
    
    public function __construct(
        ResponseFactory $response,
        Connection $db,
        OrderService $orderService
    ) {
        parent::__construct($response, $db);
        $this->orderService = $orderService;
    }
    
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        $order = $this->orderService->createOrder($data);
        return $this->response->success($order);
    }
}
```

## 🚫 Common Mistakes to Avoid

❌ **DON'T:**
```php
// Không dùng global
global $DB;

// Không khởi tạo dependencies thủ công
$db = new Connection(...);

// Không bỏ type hints
public function index($request) { }

// Không render view trực tiếp
echo $this->templates->render(...);
```

✅ **DO:**
```php
// Sử dụng injected dependencies
$this->db->...

// Container tự động resolve
// (đã được inject vào constructor)

// Luôn có type hints
public function index(ServerRequestInterface $request): ResponseInterface { }

// Sử dụng ResponseFactory
return $this->response->view(...);
```

## 📚 Resources

- [CORE_ARCHITECTURE.md](CORE_ARCHITECTURE.md) - Kiến trúc chi tiết
- [EXAMPLES.md](EXAMPLES.md) - Ví dụ cụ thể
- [Doctrine DBAL Docs](https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/)
- [League Route Docs](https://route.thephpleague.com/)
- [PSR-7 HTTP Messages](https://www.php-fig.org/psr/psr-7/)

## 🆘 Getting Help

Nếu gặp vấn đề:
1. Check documentation trong `CORE_ARCHITECTURE.md`
2. Xem examples trong `EXAMPLES.md`
3. Chạy `php test_core.php` để verify core hoạt động
4. Hỏi team members

---

**Happy Coding! 🚀**

