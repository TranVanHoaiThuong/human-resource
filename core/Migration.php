<?php

namespace App\Core;

use Doctrine\DBAL\Connection;

/**
 * Base Migration Class
 * 
 * Tất cả migration files đều extend từ class này.
 * Chỉ cần implement up() và down() methods.
 */
abstract class Migration
{
    protected Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Chạy migration (tạo table, thêm column, etc.)
     */
    abstract public function up(): void;

    /**
     * Rollback migration (xóa table, xóa column, etc.)
     */
    abstract public function down(): void;

    /**
     * Helper: Execute raw SQL
     */
    protected function execute(string $sql): void
    {
        $this->db->executeStatement($sql);
    }

    /**
     * Helper: Create table với schema đơn giản
     */
    protected function createTable(string $table, array $columns): void
    {
        $sql = "CREATE TABLE {$table} (\n";
        $sql .= implode(",\n", $columns);
        $sql .= "\n)";
        
        $this->execute($sql);
    }

    /**
     * Helper: Drop table
     */
    protected function dropTable(string $table): void
    {
        $this->execute("DROP TABLE IF EXISTS {$table}");
    }
}

