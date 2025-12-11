<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableStatus extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('status');

        $data = [
            ['type' => 'Non traité'],
            ['type' => 'En cours'],
            ['type' => 'Clôturé'],
        ];
        $this->db->table('status')->insertBatch($data);
    }

    public function down()
    {
        $this->forge->dropTable('status');
    }
}
