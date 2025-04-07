<?php

namespace api_eco\App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePartners extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'company_name'=> ['type' => 'VARCHAR', 'constraint' => 255],
            'cnpj'        => ['type' => 'VARCHAR', 'constraint' => 18, 'unique' => true],
            'email'       => ['type' => 'VARCHAR', 'constraint' => 255, 'unique' => true],
            'address'     => ['type' => 'TEXT'],
            'created_at'  => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('partners');
    }

    public function down()
    {
        $this->forge->dropTable('partners');
    }
}