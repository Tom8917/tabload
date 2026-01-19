<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TableReports extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'application_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'version' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],

            // Auteur (rédacteur) - ne doit JAMAIS être écrasé par admin
            'author_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            // Statut technique existant
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['brouillon', 'en_relecture', 'final'],
                'default'    => 'brouillon',
            ],

            // Statut "métier" piloté par admin
            'doc_status' => [
                'type'       => 'ENUM',
                'constraint' => ['work', 'approved', 'validated'],
                'default'    => 'work',
            ],

            // Date affichée dans le tableau d'intro (date de version/rédaction)
            'version_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            // Dernière modif par l'auteur (front)
            'author_updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            // Correcteur (admin)
            'corrected_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'corrected_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            // Validateur (admin)
            'validated_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'validated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('doc_status');
        $this->forge->addKey('corrected_by');
        $this->forge->addKey('validated_by');

        // ✅ Foreign keys vers ta table `user`
        // user_id : si un user est supprimé, tu peux choisir CASCADE ou RESTRICT.
        // Ici je mets CASCADE (supprimer l'auteur supprime ses reports)
        $this->forge->addForeignKey('user_id', 'user', 'id', 'CASCADE', 'CASCADE');

        // corrected_by / validated_by : si l'admin est supprimé, on garde le report mais on null les champs
        $this->forge->addForeignKey('corrected_by', 'user', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('validated_by', 'user', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('reports', true);
    }

    public function down()
    {
        $this->forge->dropTable('reports', true);
    }
}
