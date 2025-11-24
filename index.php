<?php

/**
 * Application Entry Point
 *
 * File này là điểm khởi đầu của ứng dụng.
 * Workflow:
 * 1. Bootstrap Application với Container
 * 2. Setup Router với Container Strategy (auto-inject dependencies)
 * 3. Load routes từ routes/web.php
 * 4. Dispatch request và trả về response
 */

use League\Route\Router;
use Laminas\Diactoros\ServerRequestFactory;
use League\Route\Strategy\ApplicationStrategy;
use App\Core\Application;

require_once __DIR__ . '/vendor/autoload.php';

// 1. Bootstrap Application
$app = new Application(__DIR__);
$app->bootstrap();
$container = $app->getContainer();

// 2. Setup Router với Container Strategy
$strategy = new ApplicationStrategy();
$strategy->setContainer($container);

$router = new Router();
$router->setStrategy($strategy);

// 3. Load routes
$routeSetup = require __DIR__ . '/routes/web.php';
$routeSetup($router, $container);

// 4. Dispatch request
$request = ServerRequestFactory::fromGlobals();
$response = $router->dispatch($request);

// 5. Send response
http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}
echo $response->getBody();