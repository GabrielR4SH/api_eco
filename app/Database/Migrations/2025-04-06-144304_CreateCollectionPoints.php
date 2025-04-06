<?php

namespace api_eco\App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCollectionPoints extends Migration
{
    public function up() {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'latitude' => ['type' => 'DECIMAL', 'constraint' => '10,8'],
            'longitude' => ['type' => 'DECIMAL', 'constraint' => '11,8'],
            'materials' => ['type' => 'JSON'],
            'partner_id' => ['type' => 'INT'],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at' => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('collection_points');
    }

    public function down()
    {
        $this->forge->dropTable('collection_points');
    }
}