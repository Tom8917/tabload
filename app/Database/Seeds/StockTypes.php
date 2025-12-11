<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class StockTypes extends Seeder
{
    public function run()
    {
        $data = [
            ['name' => 'Base 50PG/50VG 1L',           'unit_volume_ml' => 1000.00],
            ['name' => 'Base 80PG/20VG 1L',           'unit_volume_ml' => 1000.00],
            ['name' => 'Base 30PG/70VG 1L',           'unit_volume_ml' => 1000.00],
            ['name' => 'Arôme Fruits rouges 30ml',    'unit_volume_ml' => 30.00],
            ['name' => 'Arôme Fruit du dragon 30ml',  'unit_volume_ml' => 30.00],
            ['name' => 'Arôme Fruits exotiques 30ml', 'unit_volume_ml' => 30.00],
            ['name' => 'Fiole 50ml',                  'unit_volume_ml' => 50.00],
            ['name' => 'Fiole 100ml',                 'unit_volume_ml' => 100.00],
            ['name' => 'Fiole 200ml',                 'unit_volume_ml' => 200.00],
            ['name' => 'Booster de nicotine 20mg/ml', 'unit_volume_ml' => 10.00],
        ];

        $this->db->table('stock_types')->insertBatch($data);
    }
}
