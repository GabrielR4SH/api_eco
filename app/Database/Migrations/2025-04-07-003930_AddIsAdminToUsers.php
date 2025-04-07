<?php

namespace api_eco\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsAdminToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'is_admin' => [
                'type' => 'BOOLEAN',
                'default' => false,
                'after' => 'password'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'is_admin');
    }
}