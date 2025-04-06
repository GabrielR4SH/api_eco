<?php

namespace api_eco\App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateColletions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'point_id' => ['type' => 'INT'],
            'material' => ['type' => 'VARCHAR', 'constraint' => 50],
            'weight_kg' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'destination' => ['type' => 'VARCHAR', 'constraint' => 50],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('collections');
    }

    public function down()
    {
        $this->forge->dropTable('collections');
    }
}
