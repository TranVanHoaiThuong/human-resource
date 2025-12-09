<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected string $table = 'users';
    
    protected array $fillable = [
        'username',
        'fullname',
        'firstname',
        'lastname',
        'email',
        'password',
        'status',
        'orgstructure_id',
        'timezone',
        'user_created',
        'user_updated'
    ];

    /**
     * Tìm user theo email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->findBy(['email' => $email]);
    }

    /**
     * Tìm user theo username
     */
    public function findByUsername(string $username): ?array
    {
        return $this->findBy(['username' => $username]);
    }

    /**
     * Lấy users đang active
     */
    public function active(): array
    {
        return $this->where(['status' => 'active']);
    }

    /**
     * Lấy users theo phòng ban
     */
    public function byOrgStructure(int $orgStructureId): array
    {
        return $this->where(['orgstructure_id' => $orgStructureId]);
    }

    /**
     * Tạo user mới với password hash
     */
    public function createWithPassword(array $data): int|string
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        return $this->create($data);
    }

    /**
     * Verify password
     */
    public function verifyPassword(array $user, string $password): bool
    {
        return password_verify($password, $user['password']);
    }

    /**
     * Search users (custom SQL example)
     */
    public function search(string $keyword): array
    {
        return $this->raw(
            "SELECT * FROM users 
             WHERE username ILIKE :keyword 
                OR fullname ILIKE :keyword 
                OR email ILIKE :keyword
             ORDER BY fullname",
            ['keyword' => "%{$keyword}%"]
        );
    }
}