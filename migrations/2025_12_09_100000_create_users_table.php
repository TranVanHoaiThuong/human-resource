<?php

namespace App\Migrations;

use App\Core\Migration;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        $this->createTable('users', [
            'id BIGSERIAL PRIMARY KEY',
            'username VARCHAR(255) UNIQUE NOT NULL',
            'fullname VARCHAR(512) NOT NULL',
            'firstname VARCHAR(255) NOT NULL',
            'lastname VARCHAR(255) NOT NULL',
            'email VARCHAR(255) UNIQUE NOT NULL',
            'password VARCHAR(255) NOT NULL',
            'status VARCHAR(50) DEFAULT \'active\'',
            'orgstructure_id BIGINT',
            'timezone VARCHAR(50) DEFAULT \'Asia/Ho_Chi_Minh\'',
            'is_superadmin BOOLEAN DEFAULT FALSE',
            'user_created BIGINT',
            'user_updated BIGINT',
            'created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP',
            'updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP'
        ]);

        // Tạo index
        $this->execute('CREATE INDEX idx_users_email ON users(email)');
        $this->execute('CREATE INDEX idx_users_status ON users(status)');
        // Tạo superadmin user
        $this->createSuperAdmin();
    }

    public function down(): void
    {
        $this->dropTable('users');
    }

    /**
     * Tạo superadmin user từ .env
     */
    private function createSuperAdmin(): void
    {
        $username = $_ENV['ADMIN_USERNAME'] ?? 'superadmin';
        $email = $_ENV['ADMIN_EMAIL'] ?? 'admin@example.com';
        $password = $_ENV['ADMIN_PASSWORD'] ?? 'Admin@123';
        $fullname = $_ENV['ADMIN_FULLNAME'] ?? 'Super Admin';
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Tách firstname/lastname từ fullname
        $nameParts = explode(' ', $fullname, 2);
        $firstname = $nameParts[0];
        $lastname = $nameParts[1] ?? '';
        
        $now = date('Y-m-d H:i:s');
        
        $this->execute("
            INSERT INTO users (
                username, 
                email, 
                password, 
                fullname, 
                firstname, 
                lastname, 
                status,
                is_superadmin,
                created_at, 
                updated_at
            ) VALUES (
                '{$username}',
                '{$email}',
                '{$hashedPassword}',
                '{$fullname}',
                '{$firstname}',
                '{$lastname}',
                'active',
                TRUE,
                '{$now}',
                '{$now}'
            )
        ");
    }
}

