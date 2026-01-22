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
                'constraint' => 100,
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

    }

    public function down()
    {
        $this->db->table('user')
            ->where('firstname', 'admin')
            ->delete();
        $this->forge->dropTable('user');
    }
}
