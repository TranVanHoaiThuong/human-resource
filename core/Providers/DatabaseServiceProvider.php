<?php

namespace App\Core\Providers;

use App\Core\Container;
use App\Core\ServiceProvider;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

/**
 * Database Service Provider
 * 
 * Đăng ký database connection vào container
 */
class DatabaseServiceProvider implements ServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton('db', function ($c) {
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
                $logger = $c->has('logger') ? $c->get('logger') : null;
                if ($logger) {
                    $logger->error('Database connection failed', [
                        'error' => $th->getMessage(),
                        'host' => $config->dbhost,
                        'database' => $config->dbname,
                    ]);
                }
                die('Database connection failed: ' . $th->getMessage());
            }
        });

        // Alias cho Connection class
        $container->singleton(Connection::class, fn($c) => $c->get('db'));
    }

    public function boot(Container $container): void
    {
        // Đăng ký shutdown function để đóng connection
        register_shutdown_function(function () use ($container) {
            if ($container->has('db')) {
                $db = $container->get('db');
                if ($db instanceof Connection && $db->isConnected()) {
                    $db->close();
                }
            }
        });
    }
}

