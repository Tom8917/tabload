<?php

namespace App\Models;

use CodeIgniter\Model;

class WarrantyModel extends Model
{
    protected $table = 'warranty';
    protected $primaryKey = 'id';

    // Champs permis pour les opérations d'insertion et de mise à jour
    protected $allowedFields = ['time_warranty'];

    // Validation
//    protected $validationRules = [
//        'type' => 'required|min_length[3]|max_length[100]',
//    ];

//    protected $validationMessages = [
//        'type' => [
//            'required'   => 'Le nom du métier est requis.',
//            'min_length' => 'Le nom du métier doit comporter au moins 3 caractères.',
//            'max_length' => 'Le nom du métier ne doit pas dépasser 100 caractères.',
//        ],
//    ];


    public function createWarranty($data)
    {
        return $this->insert($data);
    }

    public function updateWarranty($id, $data)
    {
        return $this->update($id, $data);
    }

    public function getAllWarrantys()
    {
        return $this->findAll(); // Récupère tous les jobs de la table 'job'
    }

    public function getWarrantyById($id)
    {
        return $this->find($id);
    }

    public function deleteWarranty($id)
    {
        return $this->delete($id);
    }

    public function getPaginatedWarranty($start, $length, $searchValue, $orderColumnName, $orderDirection)
    {
        $builder = $this->builder();
        // Recherche
        if ($searchValue != null) {
            $builder->like('time_warranty', $searchValue);
        }

        // Tri
        if ($orderColumnName && $orderDirection) {
            $builder->orderBy($orderColumnName, $orderDirection);
        }

        $builder->limit($length, $start);

        return $builder->get()->getResultArray();
    }

    public function getTotalWarranty()
    {
        $builder = $this->builder();
        return $builder->countAllResults();
    }

    public function getFilteredWarranty($searchValue)
    {
        $builder = $this->builder();
        if (!empty($searchValue)) {
            $builder->like('time_warranty', $searchValue);
        }

        return $builder->countAllResults();
    }
}