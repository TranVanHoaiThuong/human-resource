<?php

namespace App\Core\Providers;

use App\Core\Container;
use App\Core\ServiceProvider;
use App\Core\Http\ResponseFactory;
use App\Core\View\ViewFactory;

/**
 * HTTP Service Provider
 * 
 * Đăng ký HTTP services vào container
 */
class HttpServiceProvider implements ServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(ResponseFactory::class, function ($c) {
            return new ResponseFactory($c->get(ViewFactory::class));
        });

        // Alias ngắn gọn
        $container->singleton('response', fn($c) => $c->get(ResponseFactory::class));
    }

    public function boot(Container $container): void
    {
        // Không cần boot logic
    }
}

