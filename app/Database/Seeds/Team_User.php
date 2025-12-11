<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Team_User extends Seeder
{
    public function run()
    {
        $data = [
            ['id_team' => 1, 'id_user' => 1, 'id_ticketcategory' => 1],
            ['id_team' => 2, 'id_user' => 2, 'id_ticketcategory' => 2],
            ['id_team' => 3, 'id_user' => 1, 'id_ticketcategory' => 3],
            ['id_team' => 3, 'id_user' => 2, 'id_ticketcategory' => 3],
        ];
        $this->db->table('team_user')->insertBatch($data);
    }
}