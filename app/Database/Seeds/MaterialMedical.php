<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MaterialMedical extends Seeder
{
    public function run()
    {

        $data = [
            [
                'id_center' => 1,
                'id_materialmedicaltype' => 1,
                'id_materialmedicalbrand' => 1,
                'id_material_vulnerability' => 3,
                'reference' => 'test',
                'nserie' => 'test',
                'id_user' => 1,
                'start_warranty' => 2025-02-01,
                'end_warranty' => 2028-02-01,
                'id_warranty' => 3
            ],
            [
                'id_center' => 2,
                'id_materialmedicaltype' => 2,
                'id_materialmedicalbrand' => 2,
                'id_material_vulnerability' => 3,
                'reference' => 'test2',
                'nserie' => 'test2',
                'id_user' => 2,
                'start_warranty' => 2025-02-01,
                'end_warranty' => 2028-02-01,
                'id_warranty' => 3
            ],
        ];
        $this->db->table('materialmedical')->insertBatch($data);
    }
}