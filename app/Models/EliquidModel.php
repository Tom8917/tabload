<?php

namespace App\Models;

use CodeIgniter\Model;

class EliquidModel extends Model
{
    protected $table = 'eliquid';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'name', 'slug', 'description', 'volume_ml', 'price', 'stock', 'image'
    ];

    protected $validationRules = [
        'name'      => 'required|min_length[2]|max_length[100]',
        'volume_ml' => 'required|is_natural',
        'price'     => 'required|decimal',
        'stock'     => 'required|is_natural',
    ];

    public function getAllForDataTable()
    {
        $results = $this->select('id, name, volume_ml, price, stock')
            ->where('deleted_at IS NULL')
            ->findAll();

        return ['data' => $results];
    }

    public function getWithRecipe($id)
    {
        return $this->where('id', $id)->first();
    }

    public function getPaginatedEliquids($start, $length, $searchValue, $orderColumnName, $orderDirection)
    {
        $builder = $this->builder()->select('id, name, volume_ml, price, stock')->where('deleted_at IS NULL');

        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('name', $searchValue)
                ->orLike('description', $searchValue)
                ->groupEnd();
        }

        if ($orderColumnName && $orderDirection) {
            $builder->orderBy($orderColumnName, $orderDirection);
        }

        return $builder->limit($length, $start)->get()->getResultArray();
    }

    public function getTotalEliquids()
    {
        return $this->where('deleted_at IS NULL')->countAllResults();
    }

    public function getFilteredEliquids($searchValue)
    {
        $builder = $this->builder()->where('deleted_at IS NULL');

        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('name', $searchValue)
                ->orLike('description', $searchValue)
                ->groupEnd();
        }

        return $builder->countAllResults();
    }

}
