<?php

namespace App\Core\Providers;

use App\Core\Container;
use App\Core\ServiceProvider;
use App\Core\View\Extensions\AssetsPath;
use App\Core\View\Extensions\PublicPath;
use App\Core\View\ViewFactory;
use App\Core\View\ViewRenderer;
use League\Plates\Engine;

/**
 * View Service Provider
 * 
 * Đăng ký view services vào container
 */
class ViewServiceProvider implements ServiceProvider
{
    protected string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    public function register(Container $container): void
    {
        // View Engine
        $container->singleton('view.engine', function ($c) {
            $engine = new Engine($this->basePath . '/views');
            $engine->loadExtension(new PublicPath($this->basePath));
            $engine->loadExtension(new AssetsPath());
            return $engine;
        });

        // Alias cho Engine class
        $container->singleton(Engine::class, fn($c) => $c->get('view.engine'));

        // ViewFactory
        $container->singleton(ViewFactory::class, function ($c) {
            return new ViewFactory(
                $c->get('view.engine'),
                $c->get('config')
            );
        });

        // ViewRenderer (factory - mỗi lần get sẽ tạo instance mới)
        $container->bind(ViewRenderer::class, function ($c) {
            return $c->get(ViewFactory::class)->make();
        });

        // Alias ngắn gọn
        $container->singleton('view', fn($c) => $c->get(ViewFactory::class));
    }

    public function boot(Container $container): void
    {
        // Không cần boot logic
    }
}

