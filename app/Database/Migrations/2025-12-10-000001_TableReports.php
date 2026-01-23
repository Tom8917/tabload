<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableReports extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'application_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'version' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'author_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['brouillon', 'en_relecture', 'final'],
                'default'    => 'brouillon',
            ],
            'doc_status' => [
                'type'       => 'ENUM',
                'constraint' => ['work', 'approved', 'validated'],
                'default'    => 'work',
            ],
            'modification_kind' => [
                'type'       => 'ENUM',
                'constraint' => ['creation', 'replace'],
                'default'    => 'creation',
                'null'       => false,
            ],
            'file_media_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'version_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'author_updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'corrected_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'corrected_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'validated_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'validated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'comments' => [
                'type' => 'TEXT',
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
        $this->forge->addKey('user_id');
        $this->forge->addKey('doc_status');
        $this->forge->addKey('corrected_by');
        $this->forge->addKey('validated_by');

        $this->forge->addForeignKey('user_id', 'user', 'id', 'CASCADE', 'CASCADE');

        $this->forge->addForeignKey('corrected_by', 'user', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('validated_by', 'user', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('reports', true);
    }

    public function down()
    {
        $this->forge->dropTable('reports', true);
    }
}
