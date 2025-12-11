<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Center extends Seeder
{
    public function run()
    {
        $data = [
            ['ville' => 'Aytré', 'diminutif' => 'ATR', 'cp' => '17440', 'adresse' => '11 Boulevard du Commandant Charcot'],
            ['ville' => 'Dolus', 'diminutif' => 'DLS', 'cp' => '17550', 'adresse' => '165 Route de St Pierre'],
            ['ville' => 'La Flotte', 'diminutif' => 'LFT', 'cp' => '17630', 'adresse' => '1 Rue Des Culquoiles'],
            ['ville' => 'La Pallice', 'diminutif' => 'LPL', 'cp' => '17000', 'adresse' => '3 Rue Alphonse de Saintonge'],
            ['ville' => 'La Rochelle', 'diminutif' => 'LRL', 'cp' => '17000', 'adresse' => '56 Boulevard Cognehors'],
            ['ville' => 'Marennes', 'diminutif' => 'MRN', 'cp' => '17320', 'adresse' => '3bis Rue du Docteur Roux'],
            ['ville' => 'Montendre', 'diminutif' => 'MTD', 'cp' => '17130', 'adresse' => '2 Rue du Général De Gaulle'],
            ['ville' => 'Périgny', 'diminutif' => 'PRG', 'cp' => '17180', 'adresse' => '6 Rue Augustin Fresnel'],
            ['ville' => 'Rochefort', 'diminutif' => 'RFT', 'cp' => '17300', 'adresse' => '60 Rue Cochon Duvivier'],
            ['ville' => 'Royan', 'diminutif' => 'RYN', 'cp' => '17200', 'adresse' => '2 Rue du Port Royal'],
            ['ville' => 'Saint Jean d/Angély', 'diminutif' => 'STY', 'cp' => '17400', 'adresse' => 'ZA La Garousserie'],
            ['ville' => 'Saint Pierre', 'diminutif' => 'STP', 'cp' => '17310', 'adresse' => 'Centre Médico Social, Route Des Allées'],
            ['ville' => 'Saintes', 'diminutif' => 'STS', 'cp' => '17100', 'adresse' => '2 Rue des rochers'],
            ['ville' => 'Surgères', 'diminutif' => 'SRG', 'cp' => '17700', 'adresse' => 'Hôtel d\'entreprises "Les pieds sur terre", Rue Tournat, Accès A, Z.I Ouest,'],
            ['ville' => 'Tonnay-Charente', 'diminutif' => 'TCH', 'cp' => '17430', 'adresse' => 'ZA Croix Biron, 16 Rue Alfred Nobel'],
        ];
        $this->db->table('center')->insertBatch($data);
    }
}