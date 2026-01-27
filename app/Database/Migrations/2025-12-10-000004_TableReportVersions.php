<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableReportVersions extends Migration
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
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'version_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'comment' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'change_type' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'edit', 'correction', 'approval', 'validation'],
                'default'    => 'edit',
            ],
            'doc_status' => [
                'type'       => 'ENUM',
                'constraint' => ['work', 'approved', 'validated'],
                'null'       => true,
            ],
            'changed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('report_id');
        $this->forge->addKey('changed_by');
        $this->forge->addKey('change_type');

        $this->forge->addForeignKey('report_id', 'reports', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('changed_by', 'user', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('report_versions', true);
    }

    public function down()
    {
        $this->forge->dropTable('report_versions', true);
    }
}
