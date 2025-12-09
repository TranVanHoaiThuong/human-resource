<?php

namespace App\Migrations;

use App\Core\Migration;

class CreateOrgstructureTable extends Migration
{
    public function up(): void
    {
        $this->createTable('orgstructures', [
            'id BIGSERIAL PRIMARY KEY',
            'name VARCHAR(512) NOT NULL',
            'code VARCHAR(100) UNIQUE NOT NULL',
            'description TEXT',
            'parent_id BIGINT',
            'path VARCHAR(512)',
            'user_created BIGINT',
            'user_updated BIGINT',
            'created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP',
            'updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP'
        ]);
        $this->execute('CREATE INDEX idx_orgstructures_code ON orgstructures(code)');
        $this->execute('CREATE INDEX idx_orgstructures_path ON orgstructures(path)');
    }

    public function down(): void
    {
        $this->dropTable('orgstructures');
    }
}

