<?php

namespace App\Core;

use Closure;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use Psr\Container\ContainerInterface;

/**
 * Dependency Injection Container (Simplified)
 *
 * Container với các tính năng:
 * - Singleton: Tạo một instance duy nhất và tái sử dụng
 * - Factory: Tạo instance mới mỗi lần resolve
 * - Auto-wiring: Tự động inject dependencies vào constructor
 * - Circular dependency detection: Phát hiện và báo lỗi circular dependency
 * - Interface binding: Bind interface với concrete class
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
        $concrete = $this->normalizeConcrete($abstract, $concrete);
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
        $concrete = $this->normalizeConcrete($abstract, $concrete);
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
        $this->checkCircularDependency($abstract);

        // Nếu đã có instance (singleton), trả về luôn
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Xử lý alias (interface binding)
        $abstract = $this->resolveAlias($abstract);

        // Đánh dấu đang resolve
        $this->resolving[$abstract] = true;

        try {
            $instance = $this->resolveBinding($abstract);
            
            // Nếu là singleton, lưu lại instance
            if ($this->singletons[$abstract] ?? false) {
                $this->instances[$abstract] = $instance;
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
        $this->checkCircularDependency($class);

        // Xử lý alias
        $class = $this->resolveAlias($class);

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
            $dependencies = $this->resolveDependencies($constructor, $class);
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

    // ==================== Protected Helper Methods ====================

    /**
     * Normalize concrete value (string hoặc closure)
     * 
     * @param string $abstract
     * @param Closure|string|null $concrete
     * @return Closure
     */
    protected function normalizeConcrete(string $abstract, Closure|string|null $concrete): Closure
    {
        // Nếu không có concrete, dùng abstract làm concrete (auto-bind)
        if ($concrete === null) {
            $concrete = $abstract;
        }

        // Nếu concrete là string (class name), tạo closure
        if (is_string($concrete)) {
            $concreteClass = $concrete;
            return function ($c) use ($concreteClass) {
                return $c->make($concreteClass);
            };
        }

        return $concrete;
    }

    /**
     * Kiểm tra circular dependency
     * 
     * @param string $abstract
     * @return void
     * @throws Exception Nếu phát hiện circular dependency
     */
    protected function checkCircularDependency(string $abstract): void
    {
        if (isset($this->resolving[$abstract])) {
            $chain = implode(' -> ', array_keys($this->resolving)) . " -> {$abstract}";
            throw new Exception("Circular dependency detected: {$chain}");
        }
    }

    /**
     * Resolve alias (interface binding)
     * 
     * @param string $abstract
     * @return string
     */
    protected function resolveAlias(string $abstract): string
    {
        if (!isset($this->aliases[$abstract])) {
            return $abstract;
        }

        $concrete = $this->aliases[$abstract];
        
        if (is_string($concrete)) {
            return $concrete;
        }
        
        if ($concrete instanceof Closure) {
            // Tạo binding tạm thời cho alias
            $aliasKey = $abstract . '@alias';
            $this->bindings[$aliasKey] = $concrete;
            return $aliasKey;
        }

        return $abstract;
    }

    /**
     * Resolve binding từ container
     * 
     * @param string $abstract
     * @return mixed
     */
    protected function resolveBinding(string $abstract): mixed
    {
        // Nếu không có binding, thử auto-resolve class
        if (!isset($this->bindings[$abstract])) {
            return $this->make($abstract);
        }

        // Gọi factory function để tạo instance
        $concrete = $this->bindings[$abstract];
        return $concrete($this);
    }

    /**
     * Resolve dependencies cho constructor
     * 
     * @param \ReflectionMethod $constructor
     * @param string $class
     * @return array
     * @throws Exception
     */
    protected function resolveDependencies(\ReflectionMethod $constructor, string $class): array
    {
        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependencies[] = $this->resolveParameter($parameter, $class);
        }

        return $dependencies;
    }

    /**
     * Resolve một parameter
     * 
     * @param \ReflectionParameter $parameter
     * @param string $class
     * @return mixed
     * @throws Exception
     */
    protected function resolveParameter(\ReflectionParameter $parameter, string $class): mixed
    {
        $type = $parameter->getType();

        // Nếu parameter không có type hint
        if (is_null($type)) {
            return $this->getDefaultValue($parameter, $class);
        }

        // Nếu là ReflectionNamedType
        if ($type instanceof ReflectionNamedType) {
            // Built-in types (string, int, array, etc.)
            if ($type->isBuiltin()) {
                return $this->getDefaultValue($parameter, $class);
            }

            // Class type - resolve từ container
            $typeName = $type->getName();
            return $this->get($typeName);
        }

        // Union types hoặc intersection types - không hỗ trợ auto-resolve
        return $this->getDefaultValue($parameter, $class);
    }

    /**
     * Lấy default value hoặc throw exception
     * 
     * @param \ReflectionParameter $parameter
     * @param string $class
     * @return mixed
     * @throws Exception
     */
    protected function getDefaultValue(\ReflectionParameter $parameter, string $class): mixed
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $paramName = $parameter->getName();
        $typeHint = $parameter->getType()?->getName() ?? 'unknown';
        
        throw new Exception(
            "Không thể resolve parameter \${$paramName} ({$typeHint}) của class {$class}. " .
            "Parameter cần có type hint (class) hoặc default value."
        );
    }
}

