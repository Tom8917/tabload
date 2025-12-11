<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableEliquidRecipe extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'id_eliquid' => ['type' => 'INT', 'unsigned' => true],
            'id_ingredient' => ['type' => 'INT', 'unsigned' => true],
            'quantity' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'unit' => ['type' => 'VARCHAR', 'constraint' => 10],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('eliquid_recipe');
    }

    public function down()
    {
        $this->forge->dropTable('eliquid_recipe');
    }
}
