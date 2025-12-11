<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class StockProducts extends Seeder
{
    public function run()
    {
        $data = [
            ['id_stock_type' => 1, 'id_stock_provider' => 1, 'unit_price' => 2.50, 'unit_volume_ml' => 1000.00, 'is_active' => 1],
            ['id_stock_type' => 2, 'id_stock_provider' => 1, 'unit_price' => 2.40, 'unit_volume_ml' => 1000.00, 'is_active' => 1],
            ['id_stock_type' => 3, 'id_stock_provider' => 1, 'unit_price' => 2.60, 'unit_volume_ml' => 1000.00, 'is_active' => 1],
            ['id_stock_type' => 4, 'id_stock_provider' => 2, 'unit_price' => 1.75, 'unit_volume_ml' => 30.00, 'is_active' => 1],
            ['id_stock_type' => 5, 'id_stock_provider' => 2, 'unit_price' => 1.80, 'unit_volume_ml' => 30.00, 'is_active' => 1],
            ['id_stock_type' => 6, 'id_stock_provider' => 2, 'unit_price' => 1.85, 'unit_volume_ml' => 30.00, 'is_active' => 1],
            ['id_stock_type' => 7, 'id_stock_provider' => 1, 'unit_price' => 0.30, 'unit_volume_ml' => 50.00, 'is_active' => 1], // Fiole 50ml
            ['id_stock_type' => 8, 'id_stock_provider' => 1, 'unit_price' => 0.25, 'unit_volume_ml' => 100.00, 'is_active' => 1], // Fiole 100ml
            ['id_stock_type' => 9, 'id_stock_provider' => 1, 'unit_price' => 0.20, 'unit_volume_ml' => 200.00,  'is_active' => 1], // Fiole 200ml
            ['id_stock_type' => 10, 'id_stock_provider' => 1, 'unit_price' => 0.30, 'unit_volume_ml' => 10.00, 'is_active' => 1],
        ];

        $this->db->table('stock_products')->insertBatch($data);
    }
}
