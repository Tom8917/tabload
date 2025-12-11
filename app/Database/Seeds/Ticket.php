<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Ticket extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id_user' => 2,
                'id_team' => 1,
                'id_ticketcategory' => 1,
                'id_status' => 1,
                'id_priority' => 1,
                'title' => 'Ticket n°1',
                'description' => 'Ticket créé par user@user.fr',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'deleted_at' => null,
            ],
            [
                'id_user' => 3,
                'id_team' => 2,
                'id_ticketcategory' => 2,
                'id_status' => 1,
                'id_priority' => 2,
                'title' => 'Ticket n°2',
                'description' => 'Ticket créé par test@test.fr',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'deleted_at' => null,
            ],
        ];
        $this->db->table('ticket')->insertBatch($data);
    }
}