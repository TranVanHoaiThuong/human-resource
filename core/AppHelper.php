<?php

namespace App\Core;

/**
 * App Helper
 * 
 * Helper class để access Application và Container mà không cần global variables.
 * Sử dụng static property để lưu reference, được set trong index.php.
 */
class AppHelper
{
    /**
     * @var Application|null Application instance
     */
    protected static ?Application $app = null;

    /**
     * @var Container|null Container instance
     */
    protected static ?Container $container = null;

    /**
     * Set application instance
     * 
     * @param Application $app
     * @return void
     */
    public static function setApp(Application $app): void
    {
        self::$app = $app;
        self::$container = $app->getContainer();
    }

    /**
     * Get application instance
     * 
     * @return Application
     * @throws \RuntimeException Nếu app chưa được set
     */
    public static function app(): Application
    {
        if (self::$app === null) {
            throw new \RuntimeException('Application chưa được khởi tạo. Gọi AppHelper::setApp() trong index.php.');
        }
        
        return self::$app;
    }

    /**
     * Get container instance
     * 
     * @return Container
     * @throws \RuntimeException Nếu container chưa được set
     */
    public static function container(): Container
    {
        if (self::$container === null) {
            throw new \RuntimeException('Container chưa được khởi tạo. Gọi AppHelper::setApp() trong index.php.');
        }
        
        return self::$container;
    }
}
