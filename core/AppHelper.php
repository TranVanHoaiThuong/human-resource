<?php

namespace App\Core;

/** Helper class để access Application và Container */
class AppHelper
{
    protected static ?Application $app = null;
    protected static ?Container $container = null;

    public static function setApp(Application $app): void
    {
        self::$app = $app;
        self::$container = $app->getContainer();
    }

    public static function app(): Application
    {
        if (self::$app === null) {
            throw new \RuntimeException('Application chưa được khởi tạo.');
        }

        return self::$app;
    }

    public static function container(): Container
    {
        if (self::$container === null) {
            throw new \RuntimeException('Container chưa được khởi tạo.');
        }

        return self::$container;
    }
}
