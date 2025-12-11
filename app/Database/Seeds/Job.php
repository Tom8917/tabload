<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
class Job extends Seeder
{
    public function run()
    {
        $data = [
            ['type' => 'Médecin', 'diminutif' => 'MED', 'slug' => 'medecin'],
            ['type' => 'Infirmier', 'diminutif' => 'INF', 'slug' => 'infirmier'],
            ['type' => 'Secrétaire', 'diminutif' => 'SEC', 'slug' => 'secretaire'],
            ['type' => 'IPRP', 'diminutif' => 'IPRP', 'slug' => 'iprp'],
            ['type' => 'Assistante Technicienne Hygiène et Sécurité', 'diminutif' => 'ATHS', 'slug' => 'assistante-technicienne-hygiene-et-securite'],
            ['type' => 'Psychologue', 'diminutif' => 'PSY', 'slug' => 'psychologue'],
            ['type' => 'Toxicologue', 'diminutif' => 'TXC', 'slug' => 'toxicologue'],
            ['type' => 'Ingénieur Qualité', 'diminutif' => 'QLT', 'slug' => 'ingenieur-qualite'],
            ['type' => 'Ergonome', 'diminutif' => 'ERG', 'slug' => 'ergonome'],
            ['type' => 'Technicien Informatique', 'diminutif' => 'SI', 'slug' => 'technicien-informatique'],
        ];
        $this->db->table('job')->insertBatch($data);
    }
}