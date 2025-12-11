<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableStockItems extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'image'                => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'id_stock_type'        => ['type' => 'INT', 'unsigned' => true, 'null'     => true,],
            'id_stock_product' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'name'                 => ['type' => 'VARCHAR', 'constraint' => 100],
            'quantity'             => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'unit_volume_ml'       => ['type' => 'DECIMAL', 'constraint' => '10,2', 'unsigned' => true, 'default' => '0.00', 'comment' => 'Contenance par unité en ml'],
            'created_at'           => ['type' => 'DATETIME', 'null' => true],
            'updated_at'           => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'           => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_stock_type', 'stock_types', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_stock_product', 'stock_products', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('stock_items');
    }

    public function down()
    {
        $this->forge->dropTable('stock_items');
    }
}
