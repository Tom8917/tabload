<?php

namespace App\Models;

use CodeIgniter\Model;

class EliquidRecipeModel extends Model
{
    protected $table = 'eliquid_recipe';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;

    protected $allowedFields = ['id_eliquid', 'id_ingredient', 'quantity', 'unit'];

    public function getRecipeByEliquid($eliquidId)
    {
        return $this->select('eliquid_recipe.*, ingredient.name AS ingredient_name')
            ->join('ingredient', 'ingredient.id = eliquid_recipe.id_ingredient')
            ->where('id_eliquid', $eliquidId)
            ->findAll();
    }

    public function deleteByEliquid($eliquidId)
    {
        return $this->where('id_eliquid', $eliquidId)->delete();
    }

    public function getFilteredData($search, $orderCol, $orderDir, $limit, $offset)
    {
        return $this->like('name', $search)
            ->orderBy($orderCol, $orderDir)
            ->findAll($limit, $offset);
    }

    public function countFiltered($search)
    {
        return $this->like('name', $search)->countAllResults();
    }

    public function countAllData()
    {
        return $this->countAll();
    }

}
