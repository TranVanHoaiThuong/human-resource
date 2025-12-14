<?php

namespace App\Core;

use Dotenv\Dotenv;
use App\Core\Providers\ConfigServiceProvider;
use App\Core\Providers\LoggingServiceProvider;
use App\Core\Providers\DatabaseServiceProvider;
use App\Core\Providers\AuthServiceProvider;
use App\Core\Providers\ViewServiceProvider;
use App\Core\Providers\HttpServiceProvider;
use App\Core\Providers\TranslatorServiceProvider;

/** Application bootstrap class */
class Application
{
    protected Container $container;
    protected bool $bootstrapped = false;

    protected array $providers = [
        ConfigServiceProvider::class,
        LoggingServiceProvider::class,
        DatabaseServiceProvider::class,
        AuthServiceProvider::class,
        ViewServiceProvider::class,
        HttpServiceProvider::class,
        TranslatorServiceProvider::class,
    ];

    public function __construct(protected string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
        $this->container = new Container();

        $this->container->instance(Application::class, $this);
        $this->container->instance(Container::class, $this->container);
    }

    /** Bootstrap application - load env, register & boot providers */
    public function bootstrap(): void
    {
        if ($this->bootstrapped) {
            return;
        }

        $this->loadEnvironment();
        $this->registerServiceProviders();
        $this->bootServiceProviders();

        $this->bootstrapped = true;
    }

    protected function loadEnvironment(): void
    {
        $dotenv = Dotenv::createImmutable($this->basePath);
        $dotenv->load();
    }

    protected function registerServiceProviders(): void
    {
        foreach ($this->providers as $providerClass) {
            $provider = new $providerClass($this->basePath);
            $provider->register($this->container);
        }
    }

    protected function bootServiceProviders(): void
    {
        foreach ($this->providers as $providerClass) {
            $provider = new $providerClass($this->basePath);
            $provider->boot($this->container);
        }
    }

    /** Register a new service provider */
    public function registerProvider(string $providerClass): void
    {
        if (!in_array($providerClass, $this->providers)) {
            $this->providers[] = $providerClass;

            if ($this->bootstrapped) {
                $provider = new $providerClass($this->basePath);
                $provider->register($this->container);
                $provider->boot($this->container);
            }
        }
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function isBootstrapped(): bool
    {
        return $this->bootstrapped;
    }
}

