<?php

namespace App\Core\Providers;

use App\Core\Container;
use App\Core\ServiceProvider;
use App\Core\Translator;

/**
 * Translator Service Provider
 * 
 * Đăng ký translation service vào container
 */
class TranslatorServiceProvider implements ServiceProvider
{
    protected string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    public function register(Container $container): void
    {
        $container->singleton(Translator::class, function ($c) {
            $config = $c->get('config');
            $locale = $config->app_locale ?? 'vi';
            return new Translator($this->basePath, $locale);
        });
        
        // Alias
        $container->singleton('translator', fn($c) => $c->get(Translator::class));
    }

    public function boot(Container $container): void
    {
        // Không cần boot logic
    }
}

