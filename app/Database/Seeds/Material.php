<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Material extends Seeder
{
    public function run()
    {
        $data = [
            [   'id_center' => 9,
                'id_materialtype' => 1,
                'id_materialbrand' => 1,
                'id_material_operational_system' => 3,
                'id_material_vulnerability' => 3,
                'reference' => 'test',
                'nserie' => 'test',
                'badge' => 'APAS-RFT-MED01',
                'id_user' => 1,
                'id_job' => 1,
                'start_warranty' => '2025-02-01',
                'end_warranty' => '2028-02-01',
                'id_warranty' => 3
            ],
            [
                'id_center' => 5,
                'id_materialtype' => 2,
                'id_materialbrand' => 2,
                'id_material_operational_system' => 3,
                'id_material_vulnerability' => 3,
                'reference' => 'test2',
                'nserie' => 'test2',
                'badge' => 'APAS-LRL-MED01',
                'id_user' => 2,
                'id_job' => 1,
                'start_warranty' => '2025-02-01',
                'end_warranty' => '2028-02-01',
                'id_warranty' => 3
            ],
        ];
        $this->db->table('material')->insertBatch($data);
    }
}