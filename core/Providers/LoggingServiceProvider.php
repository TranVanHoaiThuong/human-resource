<?php

namespace App\Core\Providers;

use App\Core\Container;
use App\Core\ServiceProvider;
use App\Core\Logging\Logger;
use App\Core\Logging\FileLogger;

/**
 * Logging Service Provider
 * 
 * Đăng ký logging service vào container
 */
class LoggingServiceProvider implements ServiceProvider
{
    protected string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    public function register(Container $container): void
    {
        $container->singleton(Logger::class, function ($c) {
            $config = $c->get('config');
            $logPath = $this->basePath . '/logs/app';
            $logLevel = $_ENV['LOG_LEVEL'] ?? 'info';
            $writelog = $config->app_env !== 'production';
            
            return new FileLogger($logPath, $logLevel, $writelog);
        });

        // Alias
        $container->singleton('logger', fn($c) => $c->get(Logger::class));
    }

    public function boot(Container $container): void
    {
        // Không cần boot logic
    }
}

