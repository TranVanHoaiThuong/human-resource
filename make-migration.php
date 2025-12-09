#!/usr/bin/env php
<?php

/**
 * Tạo migration file mới
 * 
 * Usage: php make-migration.php create_users_table
 */

$name = $argv[1] ?? null;

if (!$name) {
    echo "Usage: php make-migration.php migration_name\n";
    echo "Example: php make-migration.php create_users_table\n";
    exit(1);
}

$migrationsDir = __DIR__ . '/migrations';

// Tạo thư mục migrations nếu chưa có
if (!is_dir($migrationsDir)) {
    mkdir($migrationsDir, 0755, true);
}

$timestamp = date('Y_m_d_His');
$filename = "{$timestamp}_{$name}.php";
$filepath = "{$migrationsDir}/{$filename}";

// Tạo class name từ migration name
$className = '';
foreach (explode('_', $name) as $part) {
    $className .= ucfirst($part);
}

$template = <<<PHP
<?php

namespace App\Migrations;

use App\Core\Migration;

class {$className} extends Migration
{
    public function up(): void
    {
        // TODO: Implement migration
    }

    public function down(): void
    {
        // TODO: Implement rollback
    }
}
PHP;

file_put_contents($filepath, $template);
echo "✅ Created migration: {$filename}\n";

