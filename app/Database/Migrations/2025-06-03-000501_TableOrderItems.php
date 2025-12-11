<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableOrderItems extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'order_id'        => ['type' => 'INT', 'unsigned' => true],
            'stock_item_id'   => ['type' => 'INT', 'unsigned' => true],
            'quantity_ml'     => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'unit_price'      => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('order_id', 'orders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('stock_item_id', 'stock_items', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('order_items');
    }

    public function down()
    {
        $this->forge->dropTable('order_items');
    }
}
