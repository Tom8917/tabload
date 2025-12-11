<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class StockProviders extends Seeder
{
    public function run()
    {
        $data = [
            ['name' => 'Le Vapoteur Discount',],
            ['name' => 'Amazon',],
            ['name' => 'AliExpress',],
        ];

        $this->db->table('stock_providers')->insertBatch($data);
    }
}
