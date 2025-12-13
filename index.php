<?php
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

http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}
echo $response->getBody();