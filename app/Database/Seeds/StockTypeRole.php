<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class StockTypeRole extends Seeder
{
    public function run()
    {
        $data = [
            ['id_stock_type' => 1, 'id_stock_role' => 1],
            ['id_stock_type' => 2, 'id_stock_role' => 1],
            ['id_stock_type' => 3, 'id_stock_role' => 1],
            ['id_stock_type' => 4, 'id_stock_role' => 2],
            ['id_stock_type' => 5, 'id_stock_role' => 2],
            ['id_stock_type' => 6, 'id_stock_role' => 2],
            ['id_stock_type' => 7, 'id_stock_role' => 4],
            ['id_stock_type' => 8, 'id_stock_role' => 4],
            ['id_stock_type' => 9, 'id_stock_role' => 4],
            ['id_stock_type' => 10, 'id_stock_role' => 3],

        ];

        $this->db->table('stock_type_roles')->insertBatch($data);
    }
}
