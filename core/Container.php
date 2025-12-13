<?php

namespace App\Core;

use Closure;
use Exception;
use ReflectionClass;
use ReflectionException;
use Psr\Container\ContainerInterface;

/**
 * Dependency Injection Container (Improved)
 *
 * Container cải thiện với các tính năng:
 * - Singleton: Tạo một instance duy nhất và tái sử dụng
 * - Factory: Tạo instance mới mỗi lần resolve
 * - Auto-wiring: Tự động inject dependencies vào constructor
 * - Circular dependency detection: Phát hiện và báo lỗi circular dependency
 * - Interface binding: Bind interface với concrete class
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
 * // Interface binding
 * $container->alias(LoggerInterface::class, FileLogger::class);
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
     * Interface bindings: interface => concrete class
     * @var array<string, string|Closure>
     */
    protected array $aliases = [];

    /**
     * Đang resolve (để phát hiện circular dependency)
     * @var array<string, bool>
     */
    protected array $resolving = [];

    /**
     * Đăng ký một binding vào container (factory pattern)
     * Mỗi lần get() sẽ tạo instance mới
     * 
     * @param string $abstract Tên/key của service
     * @param Closure|string|null $concrete Factory function hoặc class name
     * @return void
     */
    public function bind(string $abstract, Closure|string|null $concrete = null): void
    {
        // Nếu không có concrete, dùng abstract làm concrete (auto-bind)
        if ($concrete === null) {
            $concrete = $abstract;
        }

        // Nếu concrete là string (class name), tạo closure
        if (is_string($concrete) && !$concrete instanceof Closure) {
            $concreteClass = $concrete;
            $concrete = function ($c) use ($concreteClass) {
                return $c->make($concreteClass);
            };
        }

        $this->bindings[$abstract] = $concrete;
        $this->singletons[$abstract] = false;
    }

    /**
     * Đăng ký một singleton vào container
     * Instance chỉ được tạo một lần và tái sử dụng
     * 
     * @param string $abstract Tên/key của service
     * @param Closure|string|null $concrete Factory function hoặc class name
     * @return void
     */
    public function singleton(string $abstract, Closure|string|null $concrete = null): void
    {
        // Nếu không có concrete, dùng abstract làm concrete (auto-bind)
        if ($concrete === null) {
            $concrete = $abstract;
        }

        // Nếu concrete là string (class name), tạo closure
        if (is_string($concrete) && !$concrete instanceof Closure) {
            $concreteClass = $concrete;
            $concrete = function ($c) use ($concreteClass) {
                return $c->make($concreteClass);
            };
        }

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
     * Bind interface hoặc abstract class với concrete class
     * 
     * @param string $abstract Interface hoặc abstract class
     * @param string|Closure $concrete Concrete class hoặc factory
     * @return void
     */
    public function alias(string $abstract, string|Closure $concrete): void
    {
        $this->aliases[$abstract] = $concrete;
    }

    /**
     * Lấy instance từ container
     * 
     * @param string $abstract Tên/key của service
     * @return mixed
     * @throws Exception Nếu service không tồn tại hoặc circular dependency
     */
    public function get(string $abstract): mixed
    {
        // Kiểm tra circular dependency
        if (isset($this->resolving[$abstract])) {
            throw new Exception(
                "Circular dependency detected: " . 
                implode(' -> ', array_keys($this->resolving)) . " -> {$abstract}"
            );
        }

        // Nếu đã có instance (singleton), trả về luôn
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Kiểm tra alias (interface binding)
        if (isset($this->aliases[$abstract])) {
            $concrete = $this->aliases[$abstract];
            if (is_string($concrete)) {
                return $this->get($concrete);
            }
            if ($concrete instanceof Closure) {
                $abstractAlias = $abstract . '@alias';
                $this->bindings[$abstractAlias] = $concrete;
                $abstract = $abstractAlias;
            }
        }

        // Đánh dấu đang resolve
        $this->resolving[$abstract] = true;

        try {
            // Nếu không có binding, thử auto-resolve class
            if (!isset($this->bindings[$abstract])) {
                $instance = $this->make($abstract);
            } else {
                // Gọi factory function để tạo instance
                $concrete = $this->bindings[$abstract];
                $instance = $concrete($this);

                // Nếu là singleton, lưu lại instance
                if ($this->singletons[$abstract] ?? false) {
                    $this->instances[$abstract] = $instance;
                }
            }

            return $instance;
        } finally {
            // Xóa đánh dấu resolving
            unset($this->resolving[$abstract]);
        }
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
        // Kiểm tra circular dependency
        if (isset($this->resolving[$class])) {
            throw new Exception(
                "Circular dependency detected: " . 
                implode(' -> ', array_keys($this->resolving)) . " -> {$class}"
            );
        }

        // Kiểm tra alias
        if (isset($this->aliases[$class])) {
            $concrete = $this->aliases[$class];
            if (is_string($concrete)) {
                return $this->make($concrete);
            }
        }

        try {
            $reflector = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new Exception("Class {$class} không tồn tại: " . $e->getMessage());
        }

        // Kiểm tra class có thể khởi tạo không
        if (!$reflector->isInstantiable()) {
            throw new Exception("Class {$class} không thể khởi tạo (có thể là abstract class hoặc interface).");
        }

        $constructor = $reflector->getConstructor();

        // Nếu không có constructor, tạo instance trực tiếp
        if (is_null($constructor)) {
            return new $class;
        }

        // Đánh dấu đang resolve
        $this->resolving[$class] = true;

        try {
            // Lấy danh sách parameters của constructor
            $parameters = $constructor->getParameters();
            $dependencies = [];

            foreach ($parameters as $parameter) {
                $type = $parameter->getType();

                // Nếu parameter không có type hint, không thể auto-resolve
                if (is_null($type)) {
                    // Kiểm tra có default value không
                    if ($parameter->isDefaultValueAvailable()) {
                        $dependencies[] = $parameter->getDefaultValue();
                    } else {
                        throw new Exception(
                            "Không thể resolve parameter \${$parameter->getName()} của class {$class}. " .
                            "Parameter cần có type hint hoặc default value."
                        );
                    }
                } elseif ($type instanceof \ReflectionNamedType) {
                    // Kiểm tra nếu là built-in type
                    if ($type->isBuiltin()) {
                        // Kiểm tra có default value không
                        if ($parameter->isDefaultValueAvailable()) {
                            $dependencies[] = $parameter->getDefaultValue();
                        } else {
                            throw new Exception(
                                "Không thể resolve parameter \${$parameter->getName()} của class {$class}. " .
                                "Parameter cần có type hint hoặc default value."
                            );
                        }
                    } else {
                        // Resolve dependency từ container
                        $typeName = $type->getName();
                        $dependencies[] = $this->get($typeName);
                    }
                } else {
                    // Union types hoặc intersection types - không hỗ trợ auto-resolve
                    if ($parameter->isDefaultValueAvailable()) {
                        $dependencies[] = $parameter->getDefaultValue();
                    } else {
                        throw new Exception(
                            "Không thể resolve parameter \${$parameter->getName()} của class {$class}. " .
                            "Union/Intersection types không được hỗ trợ auto-resolve."
                        );
                    }
                }
            }

            return $reflector->newInstanceArgs($dependencies);
        } finally {
            // Xóa đánh dấu resolving
            unset($this->resolving[$class]);
        }
    }

    /**
     * Kiểm tra service đã được đăng ký chưa
     * 
     * @param string $abstract Tên/key của service
     * @return bool
     */
    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) 
            || isset($this->instances[$abstract])
            || isset($this->aliases[$abstract])
            || class_exists($abstract);
    }

    /**
     * Xóa binding và instance (dùng cho testing)
     * 
     * @param string $abstract
     * @return void
     */
    public function forget(string $abstract): void
    {
        unset($this->bindings[$abstract]);
        unset($this->instances[$abstract]);
        unset($this->singletons[$abstract]);
        unset($this->aliases[$abstract]);
    }

    /**
     * Xóa tất cả bindings (dùng cho testing)
     * 
     * @return void
     */
    public function flush(): void
    {
        $this->bindings = [];
        $this->instances = [];
        $this->singletons = [];
        $this->aliases = [];
        $this->resolving = [];
    }
}

