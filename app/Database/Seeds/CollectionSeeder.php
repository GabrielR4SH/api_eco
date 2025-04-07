<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CollectionSeeder extends Seeder
{
    public function run()
{
    $data = [
        [
            'point_id' => 1,
            'material' => 'plastico',
            'weight_kg' => 150.5,
            'destination' => 'reciclagem',
            'co2_saved' => 376.25
        ],
        [
            'point_id' => 2,
            'material' => 'eletronico',
            'weight_kg' => 75.0,
            'destination' => 'reuso',
            'co2_saved' => 375.0
        ]
    ];

    $this->db->table('collections')->insertBatch($data);
}
}
