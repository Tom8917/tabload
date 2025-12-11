<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableRecipeIngredients extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'id_recipe'      => ['type' => 'INT', 'unsigned' => true],
            'id_stock_item'  => ['type' => 'INT', 'unsigned' => true],
            'quantity_ml'    => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_recipe', 'recipes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_stock_item', 'stock_items', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('recipe_ingredients');
    }

    public function down()
    {
        $this->forge->dropTable('recipe_ingredients');
    }
}
