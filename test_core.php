<?php
/**
 * Test Core Framework
 * 
 * File này test các core components để đảm bảo mọi thứ hoạt động đúng.
 * Chạy: php test_core.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;
use App\Core\Http\ResponseFactory;
use App\Controllers\HomeController;
use Doctrine\DBAL\Connection;

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║          TEST CORE FRAMEWORK - HRM APPLICATION             ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$passed = 0;
$failed = 0;

function test($name, $callback) {
    global $passed, $failed;
    echo "🧪 Testing: {$name}... ";
    try {
        $result = $callback();
        if ($result) {
            echo "✅ PASSED\n";
            $passed++;
        } else {
            echo "❌ FAILED\n";
            $failed++;
        }
    } catch (\Throwable $e) {
        echo "❌ FAILED: {$e->getMessage()}\n";
        $failed++;
    }
}

// Test 1: Application Bootstrap
test("Application Bootstrap", function() {
    $app = new Application(__DIR__);
    $app->bootstrap();
    return $app->isBootstrapped();
});

// Test 2: Container
test("Container Instance", function() {
    $app = new Application(__DIR__);
    $app->bootstrap();
    $container = $app->getContainer();
    return $container !== null;
});

// Test 3: Config Service
test("Config Service", function() {
    $app = new Application(__DIR__);
    $app->bootstrap();
    $container = $app->getContainer();
    $config = $container->get('config');
    return isset($config->dbname) && isset($config->wwwroot);
});

// Test 4: Database Connection
test("Database Connection", function() {
    $app = new Application(__DIR__);
    $app->bootstrap();
    $container = $app->getContainer();
    $db = $container->get('db');
    return $db instanceof Connection;
});

// Test 5: View Engine
test("View Engine", function() {
    $app = new Application(__DIR__);
    $app->bootstrap();
    $container = $app->getContainer();
    $engine = $container->get('view.engine');
    return $engine instanceof \League\Plates\Engine;
});

// Test 6: View Factory
test("View Factory", function() {
    $app = new Application(__DIR__);
    $app->bootstrap();
    $container = $app->getContainer();
    $viewFactory = $container->get('view');
    return $viewFactory instanceof \App\Core\View\ViewFactory;
});

// Test 7: Response Factory
test("Response Factory", function() {
    $app = new Application(__DIR__);
    $app->bootstrap();
    $container = $app->getContainer();
    $response = $container->get('response');
    return $response instanceof ResponseFactory;
});

// Test 8: Singleton Pattern
test("Singleton Pattern", function() {
    $app = new Application(__DIR__);
    $app->bootstrap();
    $container = $app->getContainer();
    $config1 = $container->get('config');
    $config2 = $container->get('config');
    return $config1 === $config2;
});

// Test 9: Auto-wiring Controller
test("Auto-wiring Controller", function() {
    $app = new Application(__DIR__);
    $app->bootstrap();
    $container = $app->getContainer();
    $controller = $container->make(HomeController::class);
    return $controller instanceof HomeController;
});

// Test 10: Response Factory - JSON
test("Response Factory - JSON", function() {
    $app = new Application(__DIR__);
    $app->bootstrap();
    $container = $app->getContainer();
    $responseFactory = $container->get('response');
    $response = $responseFactory->json(['test' => 'data']);
    $body = (string) $response->getBody();
    return str_contains($body, '"test":"data"');
});

// Test 11: Response Factory - Success
test("Response Factory - Success", function() {
    $app = new Application(__DIR__);
    $app->bootstrap();
    $container = $app->getContainer();
    $responseFactory = $container->get('response');
    $response = $responseFactory->success(['id' => 1], 'Success');
    $body = (string) $response->getBody();
    return str_contains($body, '"success":true');
});

// Test 12: Response Factory - Error
test("Response Factory - Error", function() {
    $app = new Application(__DIR__);
    $app->bootstrap();
    $container = $app->getContainer();
    $responseFactory = $container->get('response');
    $response = $responseFactory->error('Error message');
    $body = (string) $response->getBody();
    return str_contains($body, '"success":false');
});

// Test 13: View Renderer
test("View Renderer", function() {
    $app = new Application(__DIR__);
    $app->bootstrap();
    $container = $app->getContainer();
    $viewFactory = $container->get('view');
    $view = $viewFactory->make();
    $view->setTitle('Test');
    return $view->getTitle() === 'Test';
});

// Test 14: PSR-11 Container Interface
test("PSR-11 Container Interface", function() {
    $app = new Application(__DIR__);
    $app->bootstrap();
    $container = $app->getContainer();
    return $container instanceof \Psr\Container\ContainerInterface;
});

// Summary
echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                      TEST SUMMARY                          ║\n";
echo "╠════════════════════════════════════════════════════════════╣\n";
echo "║  ✅ Passed: " . str_pad($passed, 2, ' ', STR_PAD_LEFT) . "                                                ║\n";
echo "║  ❌ Failed: " . str_pad($failed, 2, ' ', STR_PAD_LEFT) . "                                                ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";

if ($failed === 0) {
    echo "\n🎉 All tests passed! Core framework is working correctly.\n";
    exit(0);
} else {
    echo "\n⚠️  Some tests failed. Please check the errors above.\n";
    exit(1);
}

