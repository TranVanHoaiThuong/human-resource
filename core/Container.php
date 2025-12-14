<?php

namespace App\Core;

use Closure;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use Psr\Container\ContainerInterface;

/** DI Container với auto-wiring, singleton, factory và circular dependency detection */
class Container implements ContainerInterface
{
    protected array $bindings = [];
    protected array $instances = [];
    protected array $singletons = [];
    protected array $aliases = [];
    protected array $resolving = [];

    /** Đăng ký binding (factory pattern - tạo instance mới mỗi lần get) */
    public function bind(string $abstract, Closure|string|null $concrete = null): void
    {
        $concrete = $this->normalizeConcrete($abstract, $concrete);
        $this->bindings[$abstract] = $concrete;
        $this->singletons[$abstract] = false;
    }

    /** Đăng ký singleton (tạo 1 lần, tái sử dụng) */
    public function singleton(string $abstract, Closure|string|null $concrete = null): void
    {
        $concrete = $this->normalizeConcrete($abstract, $concrete);
        $this->bindings[$abstract] = $concrete;
        $this->singletons[$abstract] = true;
    }

    /** Đăng ký instance có sẵn vào container */
    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
        $this->singletons[$abstract] = true;
    }

    /** Bind interface với concrete class */
    public function alias(string $abstract, string|Closure $concrete): void
    {
        $this->aliases[$abstract] = $concrete;
    }

    /** Lấy instance từ container */
    public function get(string $abstract): mixed
    {
        $this->checkCircularDependency($abstract);

        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $abstract = $this->resolveAlias($abstract);
        $this->resolving[$abstract] = true;

        try {
            $instance = $this->resolveBinding($abstract);

            if ($this->singletons[$abstract] ?? false) {
                $this->instances[$abstract] = $instance;
            }

            return $instance;
        } finally {
            unset($this->resolving[$abstract]);
        }
    }

    /** Tạo instance với auto-wiring dependencies */
    public function make(string $class): mixed
    {
        $this->checkCircularDependency($class);
        $class = $this->resolveAlias($class);

        try {
            $reflector = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new Exception("Class {$class} không tồn tại: " . $e->getMessage());
        }

        if (!$reflector->isInstantiable()) {
            throw new Exception("Class {$class} không thể khởi tạo.");
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $class;
        }

        $this->resolving[$class] = true;

        try {
            $dependencies = $this->resolveDependencies($constructor, $class);
            return $reflector->newInstanceArgs($dependencies);
        } finally {
            unset($this->resolving[$class]);
        }
    }

    /** Kiểm tra service đã được đăng ký chưa */
    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract])
            || isset($this->instances[$abstract])
            || isset($this->aliases[$abstract])
            || class_exists($abstract);
    }

    /** Xóa binding và instance */
    public function forget(string $abstract): void
    {
        unset($this->bindings[$abstract]);
        unset($this->instances[$abstract]);
        unset($this->singletons[$abstract]);
        unset($this->aliases[$abstract]);
    }

    /** Xóa tất cả bindings */
    public function flush(): void
    {
        $this->bindings = [];
        $this->instances = [];
        $this->singletons = [];
        $this->aliases = [];
        $this->resolving = [];
    }

    protected function normalizeConcrete(string $abstract, Closure|string|null $concrete): Closure
    {
        if ($concrete === null) {
            $concrete = $abstract;
        }

        if (is_string($concrete)) {
            $concreteClass = $concrete;
            return function ($c) use ($concreteClass) {
                return $c->make($concreteClass);
            };
        }

        return $concrete;
    }

    protected function checkCircularDependency(string $abstract): void
    {
        if (isset($this->resolving[$abstract])) {
            $chain = implode(' -> ', array_keys($this->resolving)) . " -> {$abstract}";
            throw new Exception("Circular dependency detected: {$chain}");
        }
    }

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
            $aliasKey = $abstract . '@alias';
            $this->bindings[$aliasKey] = $concrete;
            return $aliasKey;
        }

        return $abstract;
    }

    protected function resolveBinding(string $abstract): mixed
    {
        if (!isset($this->bindings[$abstract])) {
            return $this->make($abstract);
        }

        $concrete = $this->bindings[$abstract];
        return $concrete($this);
    }

    protected function resolveDependencies(\ReflectionMethod $constructor, string $class): array
    {
        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependencies[] = $this->resolveParameter($parameter, $class);
        }

        return $dependencies;
    }

    protected function resolveParameter(\ReflectionParameter $parameter, string $class): mixed
    {
        $type = $parameter->getType();

        if (is_null($type)) {
            return $this->getDefaultValue($parameter, $class);
        }

        if ($type instanceof ReflectionNamedType) {
            if ($type->isBuiltin()) {
                return $this->getDefaultValue($parameter, $class);
            }

            $typeName = $type->getName();
            return $this->get($typeName);
        }

        return $this->getDefaultValue($parameter, $class);
    }

    protected function getDefaultValue(\ReflectionParameter $parameter, string $class): mixed
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $paramName = $parameter->getName();
        $typeHint = $parameter->getType()?->getName() ?? 'unknown';

        throw new Exception(
            "Không thể resolve parameter \${$paramName} ({$typeHint}) của class {$class}."
        );
    }
}

