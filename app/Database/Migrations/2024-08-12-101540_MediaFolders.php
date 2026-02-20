<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MediaFolders extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => false],
            'parent_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'sort_order' => ['type' => 'INT', 'null' => false, 'default' => 0],
            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
            'updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['parent_id']);
        $this->forge->addKey(['user_id']);
        $this->forge->addForeignKey('parent_id', 'media_folders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'user', 'id', 'RESTRICT', 'NO ACTION');

        $this->forge->createTable('media_folders');
    }

    public function down()
    {
        $this->forge->dropTable('media_folders');
    }
}
