<?php

namespace App\Core\Database;

use Doctrine\DBAL\Connection;

/**
 * Database Transaction Helper
 * 
 * Helper class để quản lý database transactions
 */
class Transaction
{
    protected Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Chạy callback trong transaction
     * Tự động rollback nếu có exception
     * 
     * @param callable $callback
     * @return mixed
     * @throws \Throwable
     */
    public function transaction(callable $callback): mixed
    {
        $this->db->beginTransaction();
        
        try {
            $result = $callback($this->db);
            $this->db->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Bắt đầu transaction
     */
    public function begin(): void
    {
        $this->db->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit(): void
    {
        $this->db->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback(): void
    {
        $this->db->rollBack();
    }
}

