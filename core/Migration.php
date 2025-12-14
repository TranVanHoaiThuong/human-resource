<?php

namespace App\Core;

use Doctrine\DBAL\Connection;

/** Base Migration Class */
abstract class Migration
{
    protected Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /** Chạy migration */
    abstract public function up(): void;

    /** Rollback migration */
    abstract public function down(): void;

    protected function execute(string $sql): void
    {
        $this->db->executeStatement($sql);
    }

    protected function createTable(string $table, array $columns): void
    {
        $sql = "CREATE TABLE {$table} (\n";
        $sql .= implode(",\n", $columns);
        $sql .= "\n)";

        $this->execute($sql);
    }

    protected function dropTable(string $table): void
    {
        $this->execute("DROP TABLE IF EXISTS {$table}");
    }
}

