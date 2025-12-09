<?php

/**
 * Helper Functions
 * 
 * File này chứa các helper functions toàn cục để sử dụng trong ứng dụng.
 */

use App\Core\Container;

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

