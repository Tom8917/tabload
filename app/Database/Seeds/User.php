<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class User extends Seeder
{
    public function run()
    {
        $data = [
            [
                'firstname' => 'user',
                'lastname' => 'user',
                'sessionId' => 'u.user',
                'sessionPassword' => 'user',
                'email' => 'user@user.fr',
                'password' => 'user',
                'uegarId' => 'u.user',
                'uegarPassword' => 'user',
                'id_permission' => 3,
                'id_job' => 3,
                'counter_user' => 3,
            ],
            [
                'firstname' => 'test',
                'lastname' => 'test',
                'sessionId' => 't.test',
                'sessionPassword' => 'test',
                'email' => 'test@test.fr',
                'password' => 'test',
                'uegarId' => 't.test',
                'uegarPassword' => 'test',
                'id_permission' => 3,
                'id_job' => 2,
                'counter_user' => 3,
            ],
        ];
        $this->db->table('user')->insertBatch($data);
    }
}