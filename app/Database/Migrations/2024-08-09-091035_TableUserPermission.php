<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableUserPermission extends Migration
{
    public function up()
    {
        // Créer la table 'user_permission' avec la colonne 'slug'
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('user_permission');

        // Insérer les 3 permissions par défaut
        $data = [
            ['name' => 'Administrateur'],
            ['name' => 'Collaborateur'],
            ['name' => 'Utilisateur'],
        ];
        $this->db->table('user_permission')->insertBatch($data);

        // Vérifier si la colonne 'id_permission' existe dans la table 'user'
        $fields = $this->db->getFieldData('user');
        $columnExists = false;

        foreach ($fields as $field) {
            if ($field->name === 'id_permission') {
                $columnExists = true;
                break;
            }
        }

        // Si la colonne 'id_permission' n'existe pas, on l'ajoute
        if (!$columnExists) {
            $this->forge->addColumn('user', [
                'id_permission' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true, // Permet de ne pas obliger un id_permission
                ]
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('user', 'id_permission');
        $this->forge->dropTable('user_permission');
    }
}
