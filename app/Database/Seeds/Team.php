<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Team extends Seeder
{
    public function run()
    {
        $data = [
            ['name' => 'Équipe 1', 'description' => 'Messagerie'],
            ['name' => 'Équipe 2', 'description' => 'uÉgar'],
            ['name' => 'Équipe 3', 'description' => 'Imprimante'],
            ['name' => 'Équipe 4', 'description' => 'Document'],
            ['name' => 'Équipe 5', 'description' => 'Périphérique'],
        ];
        $this->db->table('team')->insertBatch($data);
    }
}