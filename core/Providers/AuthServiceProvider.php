<?php

namespace App\Core\Providers;

use App\Core\Container;
use App\Core\ServiceProvider;
use App\Core\Auth\Auth;
use App\Core\Auth\AuthMiddleWare;

/**
 * Auth Service Provider
 * 
 * Đăng ký authentication services vào container
 */
class AuthServiceProvider implements ServiceProvider
{
    public function register(Container $container): void
    {
        // Start session (cần cho intended URL)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $container->singleton(Auth::class, function ($c) {
            $logger = $c->has('logger') ? $c->get('logger') : null;
            return new Auth($c->get('db'), $logger);
        });
        
        // Alias
        $container->singleton('auth', fn($c) => $c->get(Auth::class));
        
        // Middleware
        $container->bind(AuthMiddleWare::class, function ($c) {
            return new AuthMiddleWare($c->get(Auth::class));
        });
    }

    public function boot(Container $container): void
    {
        // Không cần boot logic
    }
}

