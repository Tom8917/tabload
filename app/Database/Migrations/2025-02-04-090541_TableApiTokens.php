<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableApiTokens extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],

            'id_user' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false
            ],

            'token' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false
            ],

            'counter' => [
                'type' => 'INT',
                'default' => 10,
                'null' => false
            ],

            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],

            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
        ]);

        $this->forge->addPrimaryKey('id');

        if ($this->db->tableExists('user')) {
            $this->forge->addForeignKey('id_user', 'user', 'id', 'CASCADE', 'CASCADE');
        }

        $this->forge->createTable('api_tokens');

    }

    public function down()
    {
        $this->forge->dropTable('api_tokens');
    }
}
