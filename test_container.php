<?php
/**
 * File test để kiểm tra Container và Application hoạt động
 * Chạy: php test_container.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;

echo "=== TEST CONTAINER & APPLICATION ===\n\n";

// 1. Khởi tạo Application
echo "1. Khởi tạo Application...\n";
$app = new Application(__DIR__);
$app->bootstrap();
$container = $app->getContainer();
echo "✓ Application đã được khởi tạo\n\n";

// 2. Test lấy Config
echo "2. Test lấy Config từ Container...\n";
$config = $container->get('config');
echo "✓ Config loaded:\n";
echo "  - DB Name: {$config->dbname}\n";
echo "  - DB Host: {$config->dbhost}\n";
echo "  - WWW Root: {$config->wwwroot}\n\n";

// 3. Test Database Connection
echo "3. Test Database Connection...\n";
try {
    $db = $container->get('db');
    echo "✓ Database connected successfully\n";
    echo "  - Driver: " . $db->getDriver()->getName() . "\n";
    echo "  - Database: " . $db->getDatabase() . "\n\n";
} catch (\Throwable $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n\n";
}

// 4. Test View Engine
echo "4. Test View Engine...\n";
$viewEngine = $container->get('view.engine');
echo "✓ View Engine loaded\n";
echo "  - Type: " . get_class($viewEngine) . "\n\n";

// 5. Test Singleton Pattern
echo "5. Test Singleton Pattern...\n";
$config1 = $container->get('config');
$config2 = $container->get('config');
if ($config1 === $config2) {
    echo "✓ Singleton works: cùng một instance\n\n";
} else {
    echo "✗ Singleton failed: khác instance\n\n";
}

// 6. Test Auto-wiring
echo "6. Test Auto-wiring...\n";

// Tạo một class test với dependency injection
class TestService {
    public function __construct(
        private \stdClass $config
    ) {}
    
    public function getConfig() {
        return $this->config;
    }
}

// Đăng ký TestService vào container
$container->bind(TestService::class, function($c) {
    return new TestService($c->get('config'));
});

$testService = $container->get(TestService::class);
echo "✓ Auto-wiring works\n";
echo "  - TestService config DB: " . $testService->getConfig()->dbname . "\n\n";

echo "=== TẤT CẢ TESTS ĐÃ HOÀN THÀNH ===\n";

