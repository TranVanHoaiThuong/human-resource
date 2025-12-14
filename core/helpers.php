<?php

/**
 * Helper Functions
 * 
 * File này chứa các helper functions toàn cục để sử dụng trong ứng dụng.
 */

use App\Core\Container;
use App\Core\AppHelper;

if (!function_exists('route')) {
    /**
     * Tạo route handler với auto-wiring từ Container
     * 
     * Helper này giúp tạo closure cho routes một cách gọn gàng.
     * Container sẽ tự động resolve và inject dependencies vào controller.
     * 
     * @param Container $container DI Container
     * @param string $controller Controller class name
     * @param string $method Method name
     * @return Closure Route handler
     * 
     * @example
     * $router->get('/users', route($container, UserController::class, 'index'));
     */
    function route(Container $container, string $controller, string $method): Closure
    {
        return function($request, array $args = []) use ($container, $controller, $method) {
            $instance = $container->make($controller);
            return $instance->$method($request, $args);
        };
    }
}

if (!function_exists('config')) {
    /**
     * Lấy giá trị config
     * 
     * @param string $key Config key (sử dụng dot notation)
     * @param mixed $default Default value nếu không tìm thấy
     * @return mixed
     * 
     * @example
     * config('database.host', 'localhost');
     */
    function config(string $key, $default = null)
    {
        // TODO: Implement config helper khi cần
        return $default;
    }
}

if (!function_exists('env')) {
    /**
     * Lấy giá trị environment variable
     * 
     * @param string $key Environment variable name
     * @param mixed $default Default value
     * @return mixed
     * 
     * @example
     * env('DB_HOST', 'localhost');
     */
    function env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        return $value;
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die - Debug helper
     * 
     * @param mixed ...$vars Variables to dump
     * @return void
     */
    function dd(...$vars): void
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
        die(1);
    }
}

if (!function_exists('dump')) {
    /**
     * Dump variable - Debug helper
     * 
     * @param mixed ...$vars Variables to dump
     * @return void
     */
    function dump(...$vars): void
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
    }
}

if (!function_exists('user_datetime')) {
    /**
     * Convert datetime sang timezone của user
     * 
     * @param string|null $datetime
     * @param string|null $userTimezone
     * @param string $format
     * @return string|null
     */
    function user_datetime(?string $datetime, ?string $userTimezone = null, string $format = 'd/m/Y H:i'): ?string
    {
        return \App\Core\DateTimeHelper::format($datetime, $userTimezone, $format);
    }
}

if (!function_exists('relative_time')) {
    /**
     * Format relative time
     * 
     * @param string|null $datetime
     * @param string|null $userTimezone
     * @return string|null
     */
    function relative_time(?string $datetime, ?string $userTimezone = null): ?string
    {
        return \App\Core\DateTimeHelper::relative($datetime, $userTimezone);
    }
}

if (!function_exists('__')) {
    /**
     * Translate shorthand
     * 
     * @example
     * __('user.username')                    // "Tên đăng nhập"
     * __('common.welcome', ['name' => 'An']) // "Chào An"
     */
    function __(string $key, array $replace = []): string
    {
        try {
            $container = AppHelper::container();
            if ($container->has(\App\Core\Translator::class)) {
                return $container->get(\App\Core\Translator::class)->get($key, $replace);
            }
        } catch (\RuntimeException $e) {
            // Container chưa sẵn sàng, trả về key
        }
        
        return $key;
    }
}

if (!function_exists('trans')) {
    /**
     * Alias for __()
     */
    function trans(string $key, array $replace = []): string
    {
        return __($key, $replace);
    }
}

if (!function_exists('trans_module')) {
    /**
     * Get entire module translations (for JS)
     * 
     * @example
     * trans_module('user')  // ['username' => 'Tên đăng nhập', ...]
     */
    function trans_module(string $module): array
    {
        try {
            $container = AppHelper::container();
            if ($container->has(\App\Core\Translator::class)) {
                return $container->get(\App\Core\Translator::class)->getModule($module);
            }
        } catch (\RuntimeException $e) {
            // Container chưa sẵn sàng
        }
        
        return [];
    }
}

if (!function_exists('auth')) {
    /**
     * Get Auth instance or authenticated user
     * 
     * @return \App\Core\Auth\Auth
     */
    function auth(): \App\Core\Auth\Auth
    {
        return AppHelper::container()->get(\App\Core\Auth\Auth::class);
    }
}

if (!function_exists('user')) {
    /**
     * Get current authenticated user
     * 
     * @return array|null
     */
    function user(): ?array
    {
        return auth()->user();
    }
}

if (!function_exists('user_id')) {
    /**
     * Get current authenticated user ID
     * 
     * @return int|null
     */
    function user_id(): ?int
    {
        return auth()->id();
    }
}

if (!function_exists('flash')) {
    function flash(string $key, ?string $value = null): mixed
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if ($value === null) {
            $val = $_SESSION['_flash'][$key] ?? null;
            unset($_SESSION['_flash'][$key]);
            return $val;
        }

        $_SESSION['_flash'][$key] = $value;
        return null;
    }
}