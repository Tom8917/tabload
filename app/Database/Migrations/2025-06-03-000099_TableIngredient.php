<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableIngredient extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50
            ],
            'stock' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00
            ],
            'unit' => [
                'type'       => 'VARCHAR',
                'constraint' => 10
            ],
            'price_per_unit' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('ingredient');
    }

    public function down()
    {
        $this->forge->dropTable('ingredient');
    }
}
