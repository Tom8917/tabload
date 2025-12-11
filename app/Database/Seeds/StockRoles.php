<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class StockRoles extends Seeder
{
    public function run()
    {
        $data = [
            ['name' => 'base'],
            ['name' => 'concentrate'],
            ['name' => 'nicotine'],
            ['name' => 'fiole'],
        ];

        $this->db->table('stock_roles')->insertBatch($data);
    }
}
