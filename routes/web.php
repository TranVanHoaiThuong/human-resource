<?php

/**
 * Web Routes
 *
 * Định nghĩa các routes cho ứng dụng.
 * Router sẽ tự động resolve controller từ Container với auto-wiring.
 *
 * Cú pháp:
 * $router->get('/path', route($container, ControllerClass::class, 'method'));
 *
 * Container sẽ tự động inject dependencies vào Controller constructor.
 */

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Core\Auth\AuthMiddleWare;
use League\Route\Router;
use App\Core\Container;

return function(Router $router, Container $container) {
    $auth = $container->get(App\Core\Auth\Auth::class);

    // Auth
    $router->get('/login', route($container, AuthController::class, 'showLogin'));
    $router->post('/login', route($container, AuthController::class, 'login'));
    $router->post('/logout', route($container, AuthController::class, 'logout'));

    $router->group('', function($group) use ($container) {
        // Root redirect to dashboard
        $group->get('/', route($container, DashboardController::class, 'index'));
        $group->get('/dashboard', route($container, DashboardController::class, 'index'));
    })->middleware(new AuthMiddleWare($auth));
};