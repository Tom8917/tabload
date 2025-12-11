<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableTask extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('task')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'title' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                ],
                'description' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 2000,
                ],
                'limit_time' => [
                    'type'       => 'DATETIME',
                    'null'       => true,
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint'     => 10,
                    'null'       => true,
                ],
                'created_at' => [
                    'type'       => 'DATETIME',
                    'null'       => true,
                ],
                'updated_at' => [
                    'type'       => 'DATETIME',
                    'null'       => true,
                ],
                'deleted_at' => [
                    'type'       => 'DATETIME',
                    'null'       => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->createTable('task');
        }
    }

    public function down()
    {
        $this->forge->dropTable('task');
    }
}