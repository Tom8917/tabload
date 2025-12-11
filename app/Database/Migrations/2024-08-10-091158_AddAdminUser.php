<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAdminUser extends Migration
{
    public function up()
    {
        $data = [
                'firstname' => 'admin',
                'lastname' => 'admin',
                'sessionId' => 'admin',
                'sessionPassword' => 'admin',
                'email' => 'admin@admin.fr',
                'password' => password_hash('spiderADMIN$', PASSWORD_DEFAULT),
                'uegarId' => 'admin',
                'uegarPassword' => 'admin',
                'id_permission' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
        ];

        $data3 = [
                'firstname' => 'user',
                'lastname' => 'user',
                'sessionId' => 'user',
                'sessionPassword' => 'user',
                'email' => 'user@user.fr',
                'password' => password_hash('user', PASSWORD_DEFAULT),
                'uegarId' => 'user',
                'uegarPassword' => 'user',
                'id_permission' => 3,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
        ];

        $data4 = [
            'firstname' => 'test',
            'lastname' => 'test',
            'sessionId' => 't.test',
            'sessionPassword' => 'test',
            'email' => 'test@test.fr',
            'password' => password_hash('user', PASSWORD_DEFAULT),
            'uegarId' => 't.test',
            'uegarPassword' => 'test',
            'id_permission' => 3,
            'counter_user' => 3,
        ];

        $this->db->table('user')->insert($data);
        $this->db->table('user')->insert($data3);
        $this->db->table('user')->insert($data4);

    }

    public function down()
    {
        $this->db->table('user')
            ->where('firstname', 'admin')
            ->delete();
    }
}