<?php

namespace api_eco\App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDisposals extends Migration
{
    public function up() {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'user_id' => ['type' => 'INT'],
            'point_id' => ['type' => 'INT'],
            'material' => ['type' => 'VARCHAR', 'constraint' => 100],
            'weight_kg' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'points_earned' => ['type' => 'INT', 'default' => 0],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('disposals');
    }

    public function down()
    {
        $this->forge->dropTable('disposals');
    }
}