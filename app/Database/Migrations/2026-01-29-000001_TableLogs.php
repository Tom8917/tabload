<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableLogs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'action' => ['type' => 'VARCHAR', 'constraint' => 30],
            'entity_type' => ['type' => 'VARCHAR', 'constraint' => 50],
            'entity_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'message' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'meta' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => false],
        ]);

        $this->forge->addKey('id', true);

        $this->forge->addKey(['entity_type', 'entity_id']);
        $this->forge->addKey('user_id');
        $this->forge->addKey('created_at');

        $this->forge->addForeignKey('user_id', 'user', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('logs', true);
    }

    public function down()
    {
        $this->forge->dropTable('logs', true);
    }
}
