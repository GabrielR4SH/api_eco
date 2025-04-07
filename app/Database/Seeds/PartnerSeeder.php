<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PartnerSeeder extends Seeder
{
    // app/Database/Seeds/PartnerSeeder.php
public function run()
{
    $data = [
        [
            'company_name' => 'Recicla São Paulo',
            'cnpj' => '12.345.678/0001-01',
            'email' => 'sp@recicla.com',
            'address' => 'Av. Paulista, 1000'
        ],
        [
            'company_name' => 'Eco Rio',
            'cnpj' => '98.765.432/0001-21',
            'email' => 'contato@ecorio.com.br',
            'address' => 'Rua do Ouvidor, 50'
        ]
    ];

    $this->db->table('partners')->insertBatch($data);
}
}
