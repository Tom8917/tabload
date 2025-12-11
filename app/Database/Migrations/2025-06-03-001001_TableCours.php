<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableCours extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'title'       => ['type'=>'VARCHAR','constraint'=>150,'null'=>false],
            'slug'        => ['type'=>'VARCHAR','constraint'=>150,'null'=>false],
            'description' => ['type'=>'TEXT','null'=>true],
            'content'     => ['type'=>'MEDIUMTEXT','null'=>true],
            'image'       => ['type'=>'VARCHAR','constraint'=>255,'null'=>true],
            'created_at'  => ['type'=>'DATETIME','null'=>true],
            'updated_at'  => ['type'=>'DATETIME','null'=>true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');

        $this->forge->createTable('cours');
    }

    public function down()
    {
        $this->forge->dropTable('cours');
    }
}
