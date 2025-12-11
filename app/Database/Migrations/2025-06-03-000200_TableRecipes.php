<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableRecipes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'image'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 100],
            'description' => ['type' => 'TEXT', 'null' => true],
            'nicotine' => ['type' => 'FLOAT', 'null' => true],
            'roles'       => ['type' => 'TEXT', 'null' => true],   // { "base": 5, "arome": 7, ... }
            'dosages'     => ['type' => 'TEXT', 'null' => true],   // { "base": 75, "arome": 20, "nicotine": 5 } en ml
            'volume_ml'   => ['type' => 'INT', 'default' => 100],  // volume final de la recette
            'cost'        => ['type' => 'DECIMAL', 'constraint'=>'10,2', 'null' => true],
            'price'       => ['type' => 'DECIMAL', 'constraint'=>'10,2', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('recipes');
    }

    public function down()
    {
        $this->forge->dropTable('recipes');
    }
}
