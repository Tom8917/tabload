<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPermissionSlug extends Migration
{
    public function up()
    {
        // Charger le helper si nécessaire
        helper('utils'); // Assurez-vous que 'utils' est le nom correct du fichier sans l'extension

        // TableUserPermission
        $this->forge->addColumn('user_permission', [
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'unique'     => true,
                'after'      => 'name',
            ],
        ]);
        $this->updateSlugs('user_permission');
    }

    public function down()
    {
        $this->forge->dropColumn('user_permission', 'slug');
    }

    private function updateSlugs($table)
    {
        $db = \Config\Database::connect();
        $builder = $db->table($table);
        $results = $builder->get()->getResultArray();
        foreach ($results as $row) {
            $slug = $this->generateSlug($row['name']); // Utilisez la fonction du helper ou définie ici
            $builder->where('id', $row['id'])->update(['slug' => $slug]);
        }
    }
    private function generateSlug($string)
    {
        // Normaliser la chaîne pour enlever les accents
        $string = \Normalizer::normalize($string, \Normalizer::FORM_D);
        $string = preg_replace('/[\p{Mn}]/u', '', $string);

        // Convertir les caractères spéciaux en minuscules et les espaces en tirets
        $string = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));

        return $string;
    }
}
