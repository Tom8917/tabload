<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableStockProducts extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'id_stock_type'     => ['type' => 'INT', 'unsigned' => true],
            'id_stock_provider' => ['type' => 'INT', 'unsigned' => true],
            'unit_volume_ml'    => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'unit_price'        => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'image'             => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_active'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_stock_type', 'stock_types', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('stock_products');
    }

    public function down()
    {
        $this->forge->dropTable('stock_products', true);
    }
}
