<?php

namespace api_eco\App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCo2SavedToCollections extends Migration
{
    public function up()
    {
        $this->forge->addColumn('collections', [
            'co2_saved' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'after' => 'destination'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('collections', 'co2_saved');
    }
}
