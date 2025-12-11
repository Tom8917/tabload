<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableStockProvider extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'image'                => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'name'           => ['type' => 'VARCHAR', 'constraint' => 100],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('stock_providers');
    }

    public function down()
    {
        $this->forge->dropTable('stock_providers');
    }
}
