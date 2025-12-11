<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableStockTypes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'image'                => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'name'           => ['type' => 'VARCHAR', 'constraint' => 100],
            'unit_volume_ml' => [
                'type'       => 'DECIMAL',
                'unsigned'   => true,
                'constraint' => '10,2',
                'null'       => false,
                'default'    => '0.00',
                'comment'    => 'Contenance en ml par unité (ex. 1000 pour 1 L, 50 pour 50 ml, etc.)',
            ],
            'roles'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('stock_types');
    }

    public function down()
    {
        $this->forge->dropTable('stock_types');
    }
}
