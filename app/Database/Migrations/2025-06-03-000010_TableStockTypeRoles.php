<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableStockTypeRoles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true
            ],
            'id_stock_type' => [
                'type'     => 'INT',
                'unsigned' => true
            ],
            'id_stock_role' => [
                'type'     => 'INT',
                'unsigned' => true
            ]
        ]);

        $this->forge->addKey('id', true); // ID technique requis par CodeIgniter
        $this->forge->addUniqueKey(['id_stock_type', 'id_stock_role']);

        $this->forge->addForeignKey('id_stock_type', 'stock_types', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_stock_role', 'stock_roles', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('stock_type_roles');
    }

    public function down()
    {
        $this->forge->dropTable('stock_type_roles');
    }
}
