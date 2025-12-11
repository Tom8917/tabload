<?php

namespace App\Models;

use CodeIgniter\Model;

class StatusModel extends Model
{
    protected $table = 'status';  // Nom de la table correspondant à "status"
    protected $primaryKey = 'id';

    // Champs autorisés pour les opérations d'insertion et de mise à jour
    protected $allowedFields = ['id', 'type'];

    // Pas de timestamps ou de soft deletes nécessaires
    protected $useTimestamps = false;
    protected $useSoftDeletes = false;

    // Recherche un enregistrement par l'ID
    public function findStatus($id)
    {
        return $this->find($id);
    }
}
