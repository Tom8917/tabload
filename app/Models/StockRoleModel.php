<?php

namespace App\Models;

use CodeIgniter\Model;

class StockRoleModel extends Model
{
    protected $table = 'stock_roles';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['name'];

    protected $validationRules = [
        'name' => 'required|max_length[50]',
    ];

    public function deleteStockRole($id): bool
    {
        return (bool) $this->delete((int)$id);
    }
}
