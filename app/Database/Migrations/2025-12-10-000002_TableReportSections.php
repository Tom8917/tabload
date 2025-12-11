<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableReportSections extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'report_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'parent_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'position' => [
                'type'    => 'INT',
                'default' => 1,
            ],
            'level' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],
            'code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'content' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'period_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'period_number' => [
                'type' => 'INT',
                'null' => true,
            ],
            'debit_value' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
            ],
            'start_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'end_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'compliance_status' => [
                'type'       => 'ENUM',
                'constraint' => ['conforme', 'non_conforme', 'partiel', 'non_applicable'],
                'default'    => 'non_applicable',
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
        $this->forge->addKey('report_id');
        $this->forge->addKey('parent_id');

        $this->forge->addForeignKey('report_id', 'reports', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('parent_id', 'report_sections', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('report_sections');
    }

    public function down()
    {
        $this->forge->dropTable('report_sections');
    }
}
