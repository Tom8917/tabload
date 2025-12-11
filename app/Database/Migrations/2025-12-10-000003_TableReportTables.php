<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableReportTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'section_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['raw', 'debit', 'result'],
                'default'    => 'raw',
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'data_json' => [
                'type' => 'LONGTEXT',
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
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('section_id');

        $this->forge->addForeignKey('section_id', 'report_sections', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('report_tables');
    }

    public function down()
    {
        $this->forge->dropTable('report_tables');
    }
}
