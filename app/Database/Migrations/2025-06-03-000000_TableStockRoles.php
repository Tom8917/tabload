<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableStockRoles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'   => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'unique'     => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('stock_roles');
    }

    public function down()
    {
        $this->forge->dropTable('stock_roles');
    }
}
