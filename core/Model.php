<?php

namespace App\Core;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Base Model
 * 
 * Model cơ sở cho tất cả models trong ứng dụng.
 * Cung cấp các phương thức CRUD cơ bản và query builder.
 */
abstract class Model
{
    protected Connection $db;
    
    /**
     * Tên bảng trong database
     */
    protected string $table = '';
    
    /**
     * Primary key column
     */
    protected string $primaryKey = 'id';
    
    /**
     * Các fields được phép mass-assign (insert/update)
     */
    protected array $fillable = [];
    
    /**
     * Tự động cập nhật timestamps
     */
    protected bool $timestamps = true;
    
    /**
     * Tên cột timestamps
     */
    protected string $createdAt = 'created_at';
    protected string $updatedAt = 'updated_at';

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    // ==================== CRUD Methods ====================

    /**
     * Lấy tất cả records
     */
    public function all(array $columns = ['*']): array
    {
        $cols = implode(', ', $columns);
        return $this->db->fetchAllAssociative(
            "SELECT {$cols} FROM {$this->table} ORDER BY {$this->primaryKey} DESC"
        );
    }

    /**
     * Tìm record theo ID
     */
    public function find(int|string $id): ?array
    {
        $result = $this->db->fetchAssociative(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id]
        );
        return $result ?: null;
    }

    /**
     * Tìm record theo ID, throw exception nếu không tìm thấy
     */
    public function findOrFail(int|string $id): array
    {
        $result = $this->find($id);
        if (!$result) {
            throw new \RuntimeException("Record not found in {$this->table} with ID: {$id}");
        }
        return $result;
    }

    /**
     * Tìm record theo điều kiện
     */
    public function findBy(array $conditions): ?array
    {
        $qb = $this->query()->select('*');
        
        foreach ($conditions as $column => $value) {
            $qb->andWhere("{$column} = :{$column}")
               ->setParameter($column, $value);
        }
        
        $result = $qb->executeQuery()->fetchAssociative();
        return $result ?: null;
    }

    /**
     * Lấy nhiều records theo điều kiện
     */
    public function where(array $conditions): array
    {
        $qb = $this->query()->select('*');
        
        foreach ($conditions as $column => $value) {
            $qb->andWhere("{$column} = :{$column}")
               ->setParameter($column, $value);
        }
        
        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * Tạo record mới
     */
    public function create(array $data): int|string
    {
        $filtered = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');
            $filtered[$this->createdAt] = $now;
            $filtered[$this->updatedAt] = $now;
        }
        
        $this->db->insert($this->table, $filtered);
        return $this->db->lastInsertId();
    }

    /**
     * Cập nhật record
     */
    public function update(int|string $id, array $data): int
    {
        $filtered = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $filtered[$this->updatedAt] = date('Y-m-d H:i:s');
        }
        
        return $this->db->update(
            $this->table,
            $filtered,
            [$this->primaryKey => $id]
        );
    }

    /**
     * Xóa record
     */
    public function delete(int|string $id): int
    {
        return $this->db->delete($this->table, [$this->primaryKey => $id]);
    }

    // ==================== Query Methods ====================

    /**
     * Tạo Query Builder mới
     */
    public function query(): QueryBuilder
    {
        return $this->db->createQueryBuilder()->from($this->table);
    }

    /**
     * Chạy raw SQL query
     */
    public function raw(string $sql, array $params = []): array
    {
        return $this->db->fetchAllAssociative($sql, $params);
    }

    /**
     * Chạy raw SQL và lấy 1 record
     */
    public function rawOne(string $sql, array $params = []): ?array
    {
        $result = $this->db->fetchAssociative($sql, $params);
        return $result ?: null;
    }

    /**
     * Đếm số records
     */
    public function count(array $conditions = []): int
    {
        $qb = $this->query()->select('COUNT(*) as total');
        
        foreach ($conditions as $column => $value) {
            $qb->andWhere("{$column} = :{$column}")
               ->setParameter($column, $value);
        }
        
        return (int) $qb->executeQuery()->fetchOne();
    }

    /**
     * Kiểm tra record tồn tại
     */
    public function exists(int|string $id): bool
    {
        return $this->count([$this->primaryKey => $id]) > 0;
    }

    // ==================== Helper Methods ====================

    /**
     * Lọc chỉ lấy các fields trong fillable
     */
    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }
        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Lấy tên bảng
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Lấy database connection
     */
    public function getConnection(): Connection
    {
        return $this->db;
    }
}