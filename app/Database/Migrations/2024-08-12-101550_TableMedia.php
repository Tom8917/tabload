<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableMedia extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'folder_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],

            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],

            'mime_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],

            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'default'    => 'file',
                'comment'    => 'image|document|file',
            ],

            'file_size' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'comment'  => 'Taille en octets',
            ],

            'entity_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'comment'  => 'ID de l’entité reliée',
            ],
            'entity_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Nom du type d’entité reliée (ex: card, user, product)',
            ],

            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
            'updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',

            'deleted_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['folder_id']);
        $this->forge->addKey(['entity_id', 'entity_type']);
        $this->forge->addKey(['type']);

        $this->forge->addForeignKey('folder_id', 'media_folders', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('media', true);
    }

    public function down()
    {
        $this->forge->dropTable('media', true);
    }
}