<?php

namespace App\Models;

use CodeIgniter\Model;

class StockTypeModel extends Model
{
    protected $table         = 'stock_types';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'name',
        'image',
        'unit_volume_ml',
        'roles',
    ];

    protected $validationRules = [
        'name'           => 'required|max_length[100]',
        'unit_volume_ml' => 'required|decimal|greater_than[0]',
    ];

    /**
     * Fonction utilitaire pour récupérer tous les rôles disponibles
     * (on suppose que la table `stock_roles` existe et que le champ `roles`
     * dans `stock_types` contient une liste d’IDs séparés par virgule).
     */
    public function getAllRoles(): array
    {
        $allTypes = $this->select('roles')->where('roles IS NOT NULL', null, false)->findAll();
        $roles    = [];

        foreach ($allTypes as $t) {
            foreach (explode(',', $t['roles']) as $r) {
                $r = trim($r);
                if ($r !== '') {
                    $roles[] = $r;
                }
            }
        }

        return array_unique($roles);
    }

    public function deleteStockType($id): bool
    {
        return (bool) $this->delete((int)$id);
    }
}
