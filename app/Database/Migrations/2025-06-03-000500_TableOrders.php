<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableOrders extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'total_amount'   => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        // Si vous voulez lier à une table users existante :
        // $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('orders');
    }

    public function down()
    {
        $this->forge->dropTable('orders');
    }
}
