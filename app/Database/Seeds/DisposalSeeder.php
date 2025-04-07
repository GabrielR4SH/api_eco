<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DisposalSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'user_id' => 3, // ID do usuário teste
                'point_id' => 1, // ID do ponto de coleta
                'weight_kg' => 5.5,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'user_id' => 3,
                'point_id' => 2,
                'weight_kg' => 3.2,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
    
        $this->db->table('disposals')->insertBatch($data);
    }
}
