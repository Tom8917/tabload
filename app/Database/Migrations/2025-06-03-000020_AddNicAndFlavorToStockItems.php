<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNicAndFlavorToStockItems extends Migration
{
    public function up()
    {
        $fields = [
            'nic_concentration' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Concentration en mg/ml pour les items nicotine',
            ],
            'flavor_percentage' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
                'comment'    => 'Pourcentage recommandé d’arôme (ex. 0.10 pour 10%)',
            ],
        ];
        $this->forge->addColumn('stock_items', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('stock_items', 'nic_concentration');
        $this->forge->dropColumn('stock_items', 'flavor_percentage');
    }
}
