<?php

namespace App\Core;

use App\Core\View\Extensions\AssetsPath;
use App\Core\View\Extensions\PublicPath;
use Dotenv\Dotenv;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;
use League\Plates\Engine;
use stdClass;
use App\Core\View\ViewFactory;
use App\Core\View\ViewRenderer;
use App\Core\Http\ResponseFactory;

/**
 * Application Class
 * 
 * Class chính để khởi tạo và quản lý ứng dụng.
 * Chịu trách nhiệm:
 * - Load environment variables
 * - Khởi tạo Container
 * - Đăng ký các services cơ bản (DB, View Engine, Config)
 * - Bootstrap ứng dụng
 * 
 * @example
 * $app = new Application(__DIR__);
 * $app->bootstrap();
 * $container = $app->getContainer();
 */
class Application
{
    /**
     * @var Container DI Container instance
     */
    protected Container $container;

    /**
     * @var string Đường dẫn gốc của ứng dụng
     */
    protected string $basePath;

    /**
     * @var bool Đánh dấu app đã được bootstrap chưa
     */
    protected bool $bootstrapped = false;

    /**
     * Khởi tạo Application
     * 
     * @param string $basePath Đường dẫn gốc của ứng dụng
     */
    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
        $this->container = new Container();
        
        // Đăng ký chính Application vào container
        $this->container->instance(Application::class, $this);
        $this->container->instance(Container::class, $this->container);
    }

    /**
     * Bootstrap ứng dụng
     * Load config, đăng ký services
     * 
     * @return void
     */
    public function bootstrap(): void
    {
        if ($this->bootstrapped) {
            return;
        }

        $this->loadEnvironment();
        $this->registerConfig();
        $this->registerDatabase();
        $this->registerViewEngine();
        $this->registerViewServices();
        $this->registerHttpServices();
        $this->registerTranslator();

        $this->bootstrapped = true;
    }

    /**
     * Load environment variables từ file .env
     * 
     * @return void
     */
    protected function loadEnvironment(): void
    {
        $dotenv = Dotenv::createImmutable($this->basePath);
        $dotenv->load();
    }

    /**
     * Đăng ký Config vào container
     * 
     * @return void
     */
    protected function registerConfig(): void
    {
        $this->container->singleton('config', function ($c) {
            $config = new stdClass();
            
            $config->dirroot = $this->basePath;
            $config->dbdriver = 'pgsql';
            $config->dbname = $_ENV['DB_NAME'];
            $config->dbuser = $_ENV['DB_USER'];
            $config->dbpass = $_ENV['DB_PASSWORD'];
            $config->dbhost = $_ENV['DB_HOST'];
            $config->dbport = $_ENV['DB_PORT'] ?? 5432;
            $config->wwwroot = $_ENV['WWWROOT'];
            
            return $config;
        });
    }

    /**
     * Đăng ký Database connection vào container
     * 
     * @return void
     */
    protected function registerDatabase(): void
    {
        $this->container->singleton('db', function ($c) {
            $config = $c->get('config');
            
            try {
                $connection = DriverManager::getConnection([
                    'dbname' => $config->dbname,
                    'user' => $config->dbuser,
                    'password' => $config->dbpass,
                    'host' => $config->dbhost,
                    'driver' => $config->dbdriver,
                    'port' => $config->dbport,
                ]);
                
                return $connection;
            } catch (\Throwable $th) {
                die('Database connection failed: ' . $th->getMessage());
            }
        });

        // Alias cho Connection class
        $this->container->singleton(Connection::class, fn($c) => $c->get('db'));

        // Đăng ký shutdown function để đóng connection
        register_shutdown_function(function () {
            if ($this->container->has('db')) {
                $db = $this->container->get('db');
                if ($db instanceof Connection && $db->isConnected()) {
                    $db->close();
                }
            }
        });
    }

    /**
     * Đăng ký Translator vào container
     * 
     * @return void
     */
    protected function registerTranslator(): void
    {
        $this->container->singleton(Translator::class, function ($c) {
            $locale = $_ENV['APP_LOCALE'] ?? 'vi';
            return new Translator($this->basePath, $locale);
        });
        
        // Alias
        $this->container->singleton('translator', fn($c) => $c->get(Translator::class));
    }

    /**
     * Đăng ký View Engine (Plates) vào container
     * 
     * @return void
     */
    protected function registerViewEngine(): void
    {
        $this->container->singleton('view.engine', function ($c) {
            $engine = new Engine($this->basePath . '/views');
            $engine->loadExtension(new PublicPath());
            $engine->loadExtension(new AssetsPath());
            return $engine;
        });

        // Alias cho Engine class
        $this->container->singleton(Engine::class, fn($c) => $c->get('view.engine'));
    }

    /**
     * Đăng ký View Services (ViewFactory, ViewRenderer) vào container
     *
     * @return void
     */
    protected function registerViewServices(): void
    {
        // Đăng ký ViewFactory như singleton
        $this->container->singleton(ViewFactory::class, function ($c) {
            return new ViewFactory(
                $c->get('view.engine'),
                $c->get('config')
            );
        });

        // Đăng ký ViewRenderer như factory (mỗi lần get sẽ tạo instance mới)
        $this->container->bind(ViewRenderer::class, function ($c) {
            return $c->get(ViewFactory::class)->make();
        });

        // Alias ngắn gọn
        $this->container->singleton('view', fn($c) => $c->get(ViewFactory::class));
    }

    /**
     * Đăng ký HTTP Services (ResponseFactory) vào container
     *
     * @return void
     */
    protected function registerHttpServices(): void
    {
        // Đăng ký ResponseFactory như singleton
        $this->container->singleton(ResponseFactory::class, function ($c) {
            return new ResponseFactory($c->get(ViewFactory::class));
        });

        // Alias ngắn gọn
        $this->container->singleton('response', fn($c) => $c->get(ResponseFactory::class));
    }

    /**
     * Lấy Container instance
     * 
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Lấy base path của ứng dụng
     * 
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Kiểm tra app đã được bootstrap chưa
     * 
     * @return bool
     */
    public function isBootstrapped(): bool
    {
        return $this->bootstrapped;
    }
}

