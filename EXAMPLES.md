# 📖 Examples - Hướng dẫn sử dụng Core Framework

## 1. Tạo Controller mới

### Controller đơn giản

```php
<?php
// File: controllers/ProductController.php

namespace App\Controllers;

use App\Core\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProductController extends Controller
{
    /**
     * Hiển thị danh sách sản phẩm
     */
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        // Lấy data từ database
        $products = $this->db->fetchAllAssociative('SELECT * FROM products');
        
        // Render view với data
        return $this->response->view(
            'products/index',           // Template path
            ['products' => $products],  // Data
            'Danh sách sản phẩm'       // Page title
        );
    }
    
    /**
     * Hiển thị chi tiết sản phẩm
     */
    public function show(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $id = $args['id'];
        
        $product = $this->db->fetchAssociative(
            'SELECT * FROM products WHERE id = ?',
            [$id]
        );
        
        if (!$product) {
            return $this->response->error('Sản phẩm không tồn tại', null, 404);
        }
        
        return $this->response->view(
            'products/show',
            ['product' => $product],
            $product['name']
        );
    }
}
```

### Đăng ký routes

```php
// File: routes/web.php

use App\Controllers\ProductController;

return function(Router $router, Container $container) {
    // Danh sách sản phẩm
    $router->get('/products', route($container, ProductController::class, 'index'));

    // Chi tiết sản phẩm
    $router->get('/products/{id:number}', route($container, ProductController::class, 'show'));
};
```

## 2. API Controller (JSON Response)

```php
<?php
// File: controllers/Api/UserApiController.php

namespace App\Controllers\Api;

use App\Core\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserApiController extends Controller
{
    /**
     * GET /api/users - Lấy danh sách users
     */
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $users = $this->db->fetchAllAssociative('SELECT id, name, email FROM users');
        
        return $this->response->success($users, 'Lấy danh sách thành công');
    }
    
    /**
     * POST /api/users - Tạo user mới
     */
    public function store(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        
        // Validate
        if (empty($data['name']) || empty($data['email'])) {
            return $this->response->error(
                'Validation failed',
                ['name' => 'Name is required', 'email' => 'Email is required'],
                422
            );
        }
        
        // Insert vào database
        $this->db->insert('users', [
            'name' => $data['name'],
            'email' => $data['email'],
        ]);
        
        $userId = $this->db->lastInsertId();
        
        return $this->response->success(
            ['id' => $userId],
            'User created successfully',
            201
        );
    }
    
    /**
     * DELETE /api/users/{id} - Xóa user
     */
    public function destroy(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $id = $args['id'];
        
        $deleted = $this->db->delete('users', ['id' => $id]);
        
        if (!$deleted) {
            return $this->response->error('User not found', null, 404);
        }
        
        return $this->response->success(null, 'User deleted successfully');
    }
}
```

### API Routes

```php
// File: routes/web.php

use App\Controllers\Api\UserApiController;

return function(Router $router, Container $container) {
    // API routes
    $router->get('/api/users', route($container, UserApiController::class, 'index'));
    $router->post('/api/users', route($container, UserApiController::class, 'store'));
    $router->delete('/api/users/{id:number}', route($container, UserApiController::class, 'destroy'));
};
```

## 3. Inject Custom Service vào Controller

### Tạo Service

```php
<?php
// File: services/EmailService.php

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

### Đăng ký Service vào Container

```php
// File: core/Application.php

use App\Services\EmailService;

protected function registerServices(): void
{
    $this->container->singleton(EmailService::class, function($c) {
        return new EmailService($c->get('config'));
    });
}
```

Gọi method này trong `bootstrap()`:

```php
public function bootstrap(): void
{
    // ... existing code
    $this->registerServices();
    $this->bootstrapped = true;
}
```

### Sử dụng trong Controller

```php
<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Http\ResponseFactory;
use App\Services\EmailService;
use Doctrine\DBAL\Connection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ContactController extends Controller
{
    private EmailService $emailService;
    
    public function __construct(
        ResponseFactory $response,
        Connection $db,
        EmailService $emailService  // Auto-injected
    ) {
        parent::__construct($response, $db);
        $this->emailService = $emailService;
    }
    
    public function send(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        
        $this->emailService->send(
            $data['email'],
            'Contact Form',
            $data['message']
        );
        
        return $this->response->success(null, 'Email sent successfully');
    }
}
```

## 4. Working with Database

```php
// Fetch all
$users = $this->db->fetchAllAssociative('SELECT * FROM users');

// Fetch one
$user = $this->db->fetchAssociative('SELECT * FROM users WHERE id = ?', [1]);

// Fetch single value
$count = $this->db->fetchOne('SELECT COUNT(*) FROM users');

// Insert
$this->db->insert('users', [
    'name' => 'John',
    'email' => 'john@example.com'
]);
$id = $this->db->lastInsertId();

// Update
$this->db->update('users', ['name' => 'Jane'], ['id' => 1]);

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

## 5. Response Types

```php
// HTML View
return $this->response->view('home/index', $data, 'Home');

// JSON
return $this->response->json(['key' => 'value']);

// Success JSON
return $this->response->success($data, 'Success message');

// Error JSON
return $this->response->error('Error message', $errors, 400);

// Redirect
return $this->response->redirect('/dashboard');

// Plain text
return $this->response->text('Hello World');
```

## 6. Accessing Container trong code khác

```php
// Trong index.php hoặc nơi có $app
$container = $app->getContainer();

// Lấy service
$db = $container->get('db');
$config = $container->get('config');
$viewFactory = $container->get('view');

// Make instance với auto-wiring
$service = $container->make(SomeService::class);
```

## 🎯 Tips

1. **Luôn type hint** parameters và return types
2. **Sử dụng PSR-7 Request/Response** interfaces
3. **Inject dependencies** qua constructor, không dùng global
4. **Đăng ký services** trong Application.php
5. **Tách logic** ra Services, Controller chỉ điều phối

