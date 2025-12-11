<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableStockMovements extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'id_stock_item'   => ['type' => 'INT', 'unsigned' => true],
            'type'            => ['type' => 'ENUM', 'constraint' => ['in', 'out']],
            'quantity'        => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'note'            => ['type' => 'TEXT', 'null' => true],
            'created_at'      => ['type' => 'DATETIME'],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_stock_item', 'stock_items', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('stock_movements');
    }

    public function down()
    {
        $this->forge->dropTable('stock_movements');
    }
}
