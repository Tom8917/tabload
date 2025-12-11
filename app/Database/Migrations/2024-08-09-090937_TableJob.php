<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableJob extends Migration
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
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
            ],
            'diminutif' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
            ],
            'slug' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'unique' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('job');
    }

    public function down()
    {
        $this->forge->dropTable('job');
    }
}
