<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CollectionPointSeeder extends Seeder
{
    public function run()
{
    $data = [
        [
            'name' => 'Ponto Paulista',
            'latitude' => '-23.563639',
            'longitude' => '-46.652739',
            'materials' => json_encode(['plastico', 'vidro']),
            'partner_id' => 1
        ],
        [
            'name' => 'Ponto Centro RJ',
            'latitude' => '-22.906847',
            'longitude' => '-43.172897',
            'materials' => json_encode(['eletronico', 'metal']),
            'partner_id' => 2
        ]
    ];

    $this->db->table('collection_points')->insertBatch($data);
}
}
