<?php

use App\Core\Container;
use App\Core\AppHelper;

if (!function_exists('route')) {
    /** Tạo route handler với auto-wiring từ Container */
    function route(Container $container, string $controller, string $method): Closure
    {
        return function($request, array $args = []) use ($container, $controller, $method) {
            $instance = $container->make($controller);
            return $instance->$method($request, $args);
        };
    }
}

if (!function_exists('config')) {
    /** Lấy giá trị config theo dot notation */
    function config(string $key, $default = null)
    {
        return $default;
    }
}

if (!function_exists('env')) {
    /** Lấy giá trị environment variable */
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
    /** Dump and die - Debug helper */
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
    /** Dump variable - Debug helper */
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
    /** Convert datetime sang timezone của user */
    function user_datetime(?string $datetime, ?string $userTimezone = null, string $format = 'd/m/Y H:i'): ?string
    {
        return \App\Core\DateTimeHelper::format($datetime, $userTimezone, $format);
    }
}

if (!function_exists('relative_time')) {
    /** Format relative time (vd: "5 phút trước") */
    function relative_time(?string $datetime, ?string $userTimezone = null): ?string
    {
        return \App\Core\DateTimeHelper::relative($datetime, $userTimezone);
    }
}

if (!function_exists('__')) {
    /** Translate shorthand */
    function __(string $key, array $replace = []): string
    {
        try {
            $container = AppHelper::container();
            if ($container->has(\App\Core\Translator::class)) {
                return $container->get(\App\Core\Translator::class)->get($key, $replace);
            }
        } catch (\RuntimeException $e) {
        }

        return $key;
    }
}

if (!function_exists('trans')) {
    /** Alias for __() */
    function trans(string $key, array $replace = []): string
    {
        return __($key, $replace);
    }
}

if (!function_exists('trans_module')) {
    /** Get entire module translations (for JS) */
    function trans_module(string $module): array
    {
        try {
            $container = AppHelper::container();
            if ($container->has(\App\Core\Translator::class)) {
                return $container->get(\App\Core\Translator::class)->getModule($module);
            }
        } catch (\RuntimeException $e) {
        }

        return [];
    }
}

if (!function_exists('auth')) {
    /** Get Auth instance */
    function auth(): \App\Core\Auth\Auth
    {
        return AppHelper::container()->get(\App\Core\Auth\Auth::class);
    }
}

if (!function_exists('user')) {
    /** Get current authenticated user */
    function user(): ?array
    {
        return auth()->user();
    }
}

if (!function_exists('user_id')) {
    /** Get current authenticated user ID */
    function user_id(): ?int
    {
        return auth()->id();
    }
}

if (!function_exists('flash')) {
    /** Flash message helper - set hoặc get flash message */
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