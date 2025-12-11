<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableStockReceptions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
                'unsigned'       => true,
            ],
            'id_stock_product' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'units' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'unit_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
            ],
            'price_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
            ],
            'note' => [
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
        $this->forge->addForeignKey('id_stock_product', 'stock_products', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('stock_receptions');
    }

    public function down()
    {
        $this->forge->dropTable('stock_receptions');
    }
}
