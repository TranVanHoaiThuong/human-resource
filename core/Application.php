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

/**
 * Application Class
 */
class Application
{
    /**
     * @var Container DI Container instance
     */
    protected Container $container;

    /**
     * @var bool App is bootstrapped
     */
    protected bool $bootstrapped = false;

    /**
     * List of service providers
     * @var array<string>
     */
    protected array $providers = [
        ConfigServiceProvider::class,
        LoggingServiceProvider::class,
        DatabaseServiceProvider::class,
        AuthServiceProvider::class,
        ViewServiceProvider::class,
        HttpServiceProvider::class,
        TranslatorServiceProvider::class,
    ];

    /**
     * Constructor - Init Application
     * 
     * @param string $basePath Path of app. Example: __DIR__
     */
    public function __construct(protected string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
        $this->container = new Container();
        
        // Register Application instance into container
        $this->container->instance(Application::class, $this);
        $this->container->instance(Container::class, $this->container);
    }

    /**
     * Bootstrap Application
     * Load config, register service providers, boot services
     * 
     * @return void
     */
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

    /**
     * Load environment variables from .env
     * 
     * @return void
     */
    protected function loadEnvironment(): void
    {
        $dotenv = Dotenv::createImmutable($this->basePath);
        $dotenv->load();
    }

    /**
     * Register all service providers
     * 
     * @return void
     */
    protected function registerServiceProviders(): void
    {
        foreach ($this->providers as $providerClass) {
            $provider = new $providerClass($this->basePath);
            $provider->register($this->container);
        }
    }

    /**
     * Boot all service providers
     * 
     * @return void
     */
    protected function bootServiceProviders(): void
    {
        foreach ($this->providers as $providerClass) {
            $provider = new $providerClass($this->basePath);
            $provider->boot($this->container);
        }
    }

    /**
     * Register a new service provider
     * 
     * @param string $providerClass
     * @return void
     */
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

    /**
     * Get DI Container
     * 
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Get app base path
     * 
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Check if app is bootstrapped
     * 
     * @return bool
     */
    public function isBootstrapped(): bool
    {
        return $this->bootstrapped;
    }
}

