<?php

namespace App\Migrations;

use App\Core\Migration;

class CreateUserSessionTable extends Migration
{
    public function up(): void
    {
        $this->createTable('user_sessions', [
            'id BIGSERIAL PRIMARY KEY',
            'user_id BIGINT NOT NULL',
            'token VARCHAR(255) UNIQUE NOT NULL',
            'ip_address VARCHAR(50)',
            'user_agent TEXT',
            'device_info TEXT',
            'expires_at TIMESTAMPTZ',
            'last_activity TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP',
            'created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP',
            'updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP'
        ]);
        $this->execute('CREATE INDEX idx_user_sessions_user_id ON user_sessions(user_id)');
    }

    public function down(): void
    {
        $this->dropTable('user_sessions');
    }
}