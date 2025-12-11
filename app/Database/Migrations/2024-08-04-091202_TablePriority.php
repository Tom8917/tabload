<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TablePriority extends Migration
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
        $this->forge->createTable('priority');

        $data = [
            ['type' => 'Faible'],
            ['type' => 'Moyenne'],
            ['type' => 'Importante'],
        ];
        $this->db->table('priority')->insertBatch($data);
    }

    public function down()
    {
        $this->forge->dropTable('priority');
    }
}
