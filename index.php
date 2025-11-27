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
use App\Core\ErrorHandler;

require_once __DIR__ . '/vendor/autoload.php';

$app = new Application(__DIR__);
$app->bootstrap();
$container = $app->getContainer();

$errorHandler = new ErrorHandler(__DIR__);
$errorHandler->register();
$errorHandler->setViewEngine($container->get('view.engine'));

$strategy = new ApplicationStrategy();
$strategy->setContainer($container);

$router = new Router();
$router->setStrategy($strategy);

$routeSetup = require __DIR__ . '/routes/web.php';
$routeSetup($router, $container);

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