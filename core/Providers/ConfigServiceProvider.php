<?php

namespace App\Core\Providers;

use App\Core\Container;
use App\Core\ServiceProvider;
use stdClass;

/**
 * Config Service Provider
 * 
 * Đăng ký config service vào container
 */
class ConfigServiceProvider implements ServiceProvider
{
    protected string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    public function register(Container $container): void
    {
        $container->singleton('config', function ($c) {
            $config = new stdClass();
            
            $config->dirroot = $this->basePath;
            $config->dbdriver = $_ENV['DB_DRIVER'] ?? 'pgsql';
            $config->dbname = $_ENV['DB_NAME'];
            $config->dbuser = $_ENV['DB_USER'];
            $config->dbpass = $_ENV['DB_PASSWORD'];
            $config->dbhost = $_ENV['DB_HOST'];
            $config->dbport = (int)($_ENV['DB_PORT'] ?? 5432);
            $config->wwwroot = $_ENV['WWWROOT'];
            $config->app_name = $_ENV['APP_NAME'] ?? 'HRM';
            $config->app_locale = $_ENV['APP_LOCALE'] ?? 'vi';
            $config->app_debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $config->app_env = $_ENV['APP_ENV'] ?? 'production';
            
            return $config;
        });
    }

    public function boot(Container $container): void
    {
        // Không cần boot logic
    }
}

