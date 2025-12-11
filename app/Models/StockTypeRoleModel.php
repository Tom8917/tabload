<?php

namespace App\Models;

use CodeIgniter\Model;

class StockTypeRoleModel extends Model
{
    protected $table = 'stock_type_roles';
    protected $primaryKey = 'id'; // ✅ Nécessaire pour éviter l'erreur
    protected $useAutoIncrement = true;

    protected $allowedFields = ['id_stock_type', 'id_stock_role'];

    public function getRolesForType($stockTypeId)
    {
        return $this->select('stock_roles.*')
            ->join('stock_roles', 'stock_roles.id = stock_type_roles.id_stock_role')
            ->where('id_stock_type', $stockTypeId)
            ->findAll();
    }

    public function getTypesForRole($stockRoleId)
    {
        return $this->select('stock_types.*')
            ->join('stock_types', 'stock_types.id = stock_type_roles.id_stock_type')
            ->where('id_stock_role', $stockRoleId)
            ->findAll();
    }

    public function syncRolesForType($stockTypeId, array $roleIds)
    {
        $this->where('id_stock_type', $stockTypeId)->delete();

        $batch = [];
        foreach ($roleIds as $roleId) {
            $batch[] = [
                'id_stock_type' => $stockTypeId,
                'id_stock_role' => $roleId
            ];
        }

        if (!empty($batch)) {
            $this->insertBatch($batch);
        }
    }
}
