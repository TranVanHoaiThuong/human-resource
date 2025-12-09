#!/usr/bin/env php
<?php

/**
 * Migration CLI Tool
 * 
 * Usage:
 *   php migrate.php migrate    - Chạy pending migrations
 *   php migrate.php rollback   - Rollback migration cuối
 *   php migrate.php status     - Xem status migrations
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Application;
use App\Core\MigrationRunner;
use Doctrine\DBAL\Connection;

$app = new Application(__DIR__);
$app->bootstrap();
$container = $app->getContainer();

/** @var Connection $db */
$db = $container->get(Connection::class);

$runner = new MigrationRunner($db, __DIR__ . '/migrations');

$command = $argv[1] ?? 'status';

try {
    switch ($command) {
        case 'migrate':
            $result = $runner->migrate();
            echo "✅ {$result['message']}\n";
            if (!empty($result['ran'])) {
                echo "Ran migrations:\n";
                foreach ($result['ran'] as $migration) {
                    echo "  - {$migration}\n";
                }
            }
            break;

        case 'rollback':
            $steps = isset($argv[2]) ? (int) $argv[2] : 1;
            $result = $runner->rollback($steps);
            echo "✅ {$result['message']}\n";
            if (!empty($result['rolled'])) {
                echo "Rolled back:\n";
                foreach ($result['rolled'] as $migration) {
                    echo "  - {$migration}\n";
                }
            }
            break;

        case 'status':
            $status = $runner->status();
            echo "Migration Status:\n\n";
            foreach ($status as $item) {
                $icon = $item['status'] === 'Ran' ? '✅' : '⏳';
                echo "{$icon} {$item['migration']} - {$item['status']}\n";
            }
            break;

        default:
            echo "Usage: php migrate.php [migrate|rollback|status]\n";
            exit(1);
    }
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}

