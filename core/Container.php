<?php

namespace App\Core;

use Closure;
use Exception;
use ReflectionClass;
use ReflectionException;
use Psr\Container\ContainerInterface;

/**
 * Dependency Injection Container
 *
 * Container đơn giản để quản lý dependencies trong ứng dụng.
 * Implement PSR-11 ContainerInterface để tương thích với các thư viện khác.
 *
 * Hỗ trợ:
 * - Singleton: Tạo một instance duy nhất và tái sử dụng
 * - Factory: Tạo instance mới mỗi lần resolve
 * - Auto-wiring: Tự động inject dependencies vào constructor
 *
 * @example
 * // Đăng ký singleton
 * $container->singleton('db', function($c) {
 *     return new Database($c->get('config'));
 * });
 *
 * // Đăng ký factory
 * $container->bind('mailer', function($c) {
 *     return new Mailer($c->get('config'));
 * });
 *
 * // Lấy instance
 * $db = $container->get('db');
 *
 * // Auto-resolve class với dependencies
 * $controller = $container->make(UserController::class);
 */
class Container implements ContainerInterface
{
    /**
     * Lưu trữ các bindings (factory functions)
     * @var array<string, Closure>
     */
    protected array $bindings = [];

    /**
     * Lưu trữ các singleton instances đã được tạo
     * @var array<string, mixed>
     */
    protected array $instances = [];

    /**
     * Đánh dấu các bindings nào là singleton
     * @var array<string, bool>
     */
    protected array $singletons = [];

    /**
     * Đăng ký một binding vào container (factory pattern)
     * Mỗi lần get() sẽ tạo instance mới
     * 
     * @param string $abstract Tên/key của service
     * @param Closure $concrete Factory function để tạo instance
     * @return void
     */
    public function bind(string $abstract, Closure $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
        $this->singletons[$abstract] = false;
    }

    /**
     * Đăng ký một singleton vào container
     * Instance chỉ được tạo một lần và tái sử dụng
     * 
     * @param string $abstract Tên/key của service
     * @param Closure $concrete Factory function để tạo instance
     * @return void
     */
    public function singleton(string $abstract, Closure $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
        $this->singletons[$abstract] = true;
    }

    /**
     * Đăng ký một instance có sẵn vào container
     * 
     * @param string $abstract Tên/key của service
     * @param mixed $instance Instance đã tạo sẵn
     * @return void
     */
    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
        $this->singletons[$abstract] = true;
    }

    /**
     * Lấy instance từ container
     * 
     * @param string $abstract Tên/key của service
     * @return mixed
     * @throws Exception Nếu service không tồn tại
     */
    public function get(string $abstract): mixed
    {
        // Nếu đã có instance (singleton), trả về luôn
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Nếu không có binding, thử auto-resolve class
        if (!isset($this->bindings[$abstract])) {
            return $this->make($abstract);
        }

        // Gọi factory function để tạo instance
        $concrete = $this->bindings[$abstract];
        $object = $concrete($this);

        // Nếu là singleton, lưu lại instance
        if ($this->singletons[$abstract] ?? false) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * Tạo instance của một class với auto-wiring dependencies
     * 
     * @param string $class Class name cần tạo
     * @return mixed
     * @throws Exception Nếu class không tồn tại hoặc không thể resolve dependencies
     */
    public function make(string $class): mixed
    {
        try {
            $reflector = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new Exception("Class {$class} không tồn tại.");
        }

        // Kiểm tra class có thể khởi tạo không
        if (!$reflector->isInstantiable()) {
            throw new Exception("Class {$class} không thể khởi tạo.");
        }

        $constructor = $reflector->getConstructor();

        // Nếu không có constructor, tạo instance trực tiếp
        if (is_null($constructor)) {
            return new $class;
        }

        // Lấy danh sách parameters của constructor
        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            // Nếu parameter không có type hint, không thể auto-resolve
            if (is_null($type) || $type->isBuiltin()) {
                // Kiểm tra có default value không
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new Exception(
                        "Không thể resolve parameter \${$parameter->getName()} của class {$class}"
                    );
                }
            } else {
                // Resolve dependency từ container
                $dependencies[] = $this->get($type->getName());
            }
        }

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Kiểm tra service đã được đăng ký chưa
     * 
     * @param string $abstract Tên/key của service
     * @return bool
     */
    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }
}

