<?php

namespace App\Core;

use Doctrine\DBAL\Connection;
use Exception;

/**
 * Migration Runner
 * 
 * Quản lý việc chạy migrations: up, down, status
 */
class MigrationRunner
{
    private Connection $db;
    private string $migrationsPath;
    private string $migrationsTable = 'migrations';

    public function __construct(Connection $db, string $migrationsPath)
    {
        $this->db = $db;
        $this->migrationsPath = rtrim($migrationsPath, '/');
        $this->ensureMigrationsTable();
    }

    /**
     * Tạo migrations table nếu chưa có
     */
    private function ensureMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->migrationsTable} (
            id SERIAL PRIMARY KEY,
            migration VARCHAR(255) UNIQUE NOT NULL,
            batch INTEGER NOT NULL,
            ran_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->db->executeStatement($sql);
    }

    /**
     * Chạy tất cả migrations chưa chạy
     */
    public function migrate(): array
    {
        $migrations = $this->getPendingMigrations();
        
        if (empty($migrations)) {
            return ['message' => 'No pending migrations.'];
        }

        $batch = $this->getNextBatch();
        $ran = [];

        foreach ($migrations as $migration) {
            $this->runMigration($migration, $batch, 'up');
            $ran[] = $migration;
        }

        return [
            'message' => 'Migrations completed.',
            'ran' => $ran
        ];
    }

    /**
     * Rollback migration cuối cùng
     */
    public function rollback(int $steps = 1): array
    {
        $migrations = $this->getLastBatchMigrations($steps);
        
        if (empty($migrations)) {
            return ['message' => 'No migrations to rollback.'];
        }

        $rolled = [];

        foreach ($migrations as $migration) {
            $this->runMigration($migration, null, 'down');
            $this->removeMigrationRecord($migration);
            $rolled[] = $migration;
        }

        return [
            'message' => 'Rollback completed.',
            'rolled' => $rolled
        ];
    }

    /**
     * Xem status của migrations
     */
    public function status(): array
    {
        $files = $this->getMigrationFiles();
        $ran = $this->getRanMigrations();

        $status = [];
        foreach ($files as $file) {
            $status[] = [
                'migration' => $file,
                'status' => in_array($file, $ran) ? 'Ran' : 'Pending'
            ];
        }

        return $status;
    }

    /**
     * Lấy danh sách migration files
     */
    private function getMigrationFiles(): array
    {
        $files = glob($this->migrationsPath . '/*.php');
        $migrations = [];

        foreach ($files as $file) {
            $migrations[] = basename($file, '.php');
        }

        sort($migrations);
        return $migrations;
    }

    /**
     * Lấy migrations chưa chạy
     */
    private function getPendingMigrations(): array
    {
        $files = $this->getMigrationFiles();
        $ran = $this->getRanMigrations();
        
        return array_diff($files, $ran);
    }

    /**
     * Lấy migrations đã chạy
     */
    private function getRanMigrations(): array
    {
        $sql = "SELECT migration FROM {$this->migrationsTable} ORDER BY id";
        $result = $this->db->fetchAllAssociative($sql);
        
        return array_column($result, 'migration');
    }

    /**
     * Lấy migrations của batch cuối
     */
    private function getLastBatchMigrations(int $steps = 1): array
    {
        $sql = "SELECT migration FROM {$this->migrationsTable} 
                WHERE batch IN (
                    SELECT DISTINCT batch FROM {$this->migrationsTable} 
                    ORDER BY batch DESC LIMIT :steps
                )
                ORDER BY id DESC";
        
        $result = $this->db->fetchAllAssociative($sql, ['steps' => $steps]);
        return array_column($result, 'migration');
    }

    /**
     * Lấy batch number tiếp theo
     */
    private function getNextBatch(): int
    {
        $sql = "SELECT COALESCE(MAX(batch), 0) + 1 as next_batch FROM {$this->migrationsTable}";
        $result = $this->db->fetchOne($sql);
        return (int) $result;
    }

    /**
     * Chạy một migration
     */
    private function runMigration(string $migration, ?int $batch, string $direction): void
    {
        require_once $this->migrationsPath . '/' . $migration . '.php';
        
        $className = $this->getMigrationClassName($migration);
        
        if (!class_exists($className)) {
            throw new Exception("Migration class {$className} not found.");
        }

        $instance = new $className($this->db);
        
        if ($direction === 'up') {
            $instance->up();
            $this->recordMigration($migration, $batch);
        } else {
            $instance->down();
        }
    }

    /**
     * Lấy class name từ migration file name
     */
    private function getMigrationClassName(string $migration): string
    {
        // 2024_01_15_100000_create_users_table -> App\Migrations\CreateUsersTable
        $parts = explode('_', $migration);
        $parts = array_slice($parts, 4); // Bỏ phần date/time
        
        $className = 'App\\Migrations\\';
        foreach ($parts as $part) {
            $className .= ucfirst($part);
        }
        
        return $className;
    }

    /**
     * Ghi lại migration đã chạy
     */
    private function recordMigration(string $migration, int $batch): void
    {
        $this->db->insert($this->migrationsTable, [
            'migration' => $migration,
            'batch' => $batch
        ]);
    }

    /**
     * Xóa record migration (khi rollback)
     */
    private function removeMigrationRecord(string $migration): void
    {
        $this->db->delete($this->migrationsTable, ['migration' => $migration]);
    }
}

