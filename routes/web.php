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
use League\Route\Router;
use App\Core\Container;

return function(Router $router, Container $container) {
    // Root redirect to dashboard
    $router->get('/', route($container, DashboardController::class, 'index'));
    
    // Dashboard route
    $router->get('/dashboard', route($container, DashboardController::class, 'index'));

    // Auth
    $router->get('/login', route($container, AuthController::class, 'index'));
};