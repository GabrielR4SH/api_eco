<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name' => 'Administrador EcoAssist',
                'email' => 'admin@ecoassist.com.br',
                'password' => password_hash('SenhaAdmin123@', PASSWORD_BCRYPT),
                'is_admin' => true,
                'created_at' => Time::now(),
                'updated_at' => Time::now()
            ],
            [
                'name' => 'Parceiro Recicla Barueri',
                'email' => 'contato@reciclabarueri.com.br',
                'password' => password_hash('Recicla@2024', PASSWORD_BCRYPT),
                'is_admin' => false,
                'created_at' => Time::now(),
                'updated_at' => Time::now()
            ],
            [
                'name' => 'Usuário Teste',
                'email' => 'teste@ecoassist.com.br',
                'password' => password_hash('Teste1234@', PASSWORD_BCRYPT),
                'is_admin' => false,
                'created_at' => Time::now(),
                'updated_at' => Time::now()
            ]
        ];

        // Simple Queries
        // $this->db->query('INSERT INTO users (name, email, password, is_admin, created_at, updated_at) VALUES(:name:, :email:, :password:, :is_admin:, :created_at:, :updated_at:)', $data);

        // Using Query Builder
        $this->db->table('users')->insertBatch($data);
        
        echo "Usuários iniciais criados com sucesso!\n";
        echo "Admin: admin@ecoassist.com.br / SenhaAdmin123@\n";
        echo "Parceiro: contato@reciclabarueri.com.br / Recicla@2024\n";
        echo "Usuário: teste@ecoassist.com.br / Teste1234@\n";
    }
}