<?php

namespace App\Models;

use CodeIgniter\Model;

class PriorityModel extends Model
{
    protected $table = 'priority';
    protected $primaryKey = 'id';

    // Chiffres autorisés pour l'insertion, ce qui garantit une recherche sur l'ID uniquement
    protected $allowedFields = ['id', 'type'];

    // Pas de timestamps ou de soft deletes nécessaires
    protected $useTimestamps = false;
    protected $useSoftDeletes = false;

    // Recherche un enregistrement par l'ID
    public function findPriority($id)
    {
        return $this->find($id);
    }
}
