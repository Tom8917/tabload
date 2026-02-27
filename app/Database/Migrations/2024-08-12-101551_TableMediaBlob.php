<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableMediaBlob extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('media_blob')) {
            return;
        }

        $this->forge->addField([
            'media_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'data' => [
                'type' => 'MEDIUMBLOB',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('media_id', true);

        $this->forge->addForeignKey(
            'media_id',
            'media',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('media_blob', true);
    }

    public function down()
    {
        if ($this->db->tableExists('media_blob')) {
            $this->forge->dropTable('media_blob', true);
        }
    }
}