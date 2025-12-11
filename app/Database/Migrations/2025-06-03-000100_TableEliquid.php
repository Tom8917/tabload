<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableEliquid extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'auto_increment' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 100],
            'slug'          => ['type' => 'VARCHAR', 'constraint' => 100],
            'description'   => ['type' => 'TEXT', 'null' => true],
            'volume_ml'     => ['type' => 'INT'],
            'price'         => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'stock'         => ['type' => 'INT'],
            'image'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'    => ['type' => 'DATETIME'],
            'updated_at'    => ['type' => 'DATETIME'],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('eliquid');
    }

    public function down()
    {
        $this->forge->dropTable('eliquid');
    }
}
