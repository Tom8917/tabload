<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableUser extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_permission' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'firstname' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'lastname' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'unique' => true,
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'counter_user' => [
                'type' => 'INT',
                'default' => 3,
                'null' => false,
            ],
            'id_api_tokens' => [
                'type' => 'INT',
                'constraint' => 255,
                'unsigned' => true,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('user');

        $data = [
            'firstname' => 'admin',
            'lastname' => 'admin',
            'email' => 'admin@admin.fr',
            'password' => password_hash('admin', PASSWORD_DEFAULT),
            'id_permission' => 1,
            'counter_user' => 3,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $data2 = [
            'firstname' => 'user',
            'lastname' => 'user',
            'email' => 'user@user.fr',
            'password' => password_hash('user', PASSWORD_DEFAULT),
            'id_permission' => 3,
            'counter_user' => 3,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->table('user')->insert($data);
        $this->db->table('user')->insert($data2);
    }

    public function down()
    {
        $this->db->table('user')
            ->where('firstname', 'admin')
            ->delete();
        $this->forge->dropTable('user');
    }
}
