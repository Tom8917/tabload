<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskModel extends Model
{
    protected $table = 'task';
    protected $primaryKey = 'id';

    protected $allowedFields = ['title', 'description', 'limit_time', 'status', 'created_at', 'updated_at', 'deleted_at'];

    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'title'   => 'required|min_length[1]|max_length[100]',
        'description'   => 'required|min_length[1]|max_length[2000]',
    ];

    protected $validationMessages = [
        'title' => [
            'required'   => 'Le titre est requise.',
            'min_length' => 'Le titre doit comporter au moins 1 caractères.',
            'max_length' => 'Le titre ne doit pas dépasser 100 caractères.',
        ],
        'description' => [
            'required'   => 'La description est requise.',
            'min_length' => 'La description doit comporter au moins 1 caractères.',
            'max_length' => 'La description ne doit pas dépasser 2000 caractères.',
        ],
    ];

    // Méthodes CRUD
    public function createTask($id)
    {
        return $this->insert($id);
    }

    public function updateTask($id, $data)
    {
        return $this->update($id, $data);
    }

    public function deleteTask($id)
    {
        return $this->delete($id);
    }

    public function getTaskById($id)
    {
        return $this->find($id);
    }

    public function getTasksByStatus($status)
    {
        return $this->where('status', $status)->findAll();
    }

    public function getAllTasks()
    {
        return $this->findAll();
    }

    public function getPaginatedTasks($start, $length, $searchValue, $orderColumnName, $orderDirection)
    {
        $builder = $this->builder();

        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('title', $searchValue)
                ->orLike('description', $searchValue)
                ->groupEnd();
        }

        if (!empty($orderColumnName) && !empty($orderDirection)) {
            $builder->orderBy($orderColumnName, $orderDirection);
        }

        $builder->limit($length, $start);

        return $builder->get()->getResultArray();
    }

    public function getTotalTasks()
    {
        return $this->countAll();
    }

    public function getFilteredTasks($searchValue)
    {
        $builder = $this->builder();

        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('title', $searchValue)
                ->orLike('description', $searchValue)
                ->groupEnd();
        }

        return $builder->countAllResults();
    }

    public function getTotalTask()
    {
        return $this->countAllResults();
    }

    public function getFilteredTask($searchValue)
    {
        $builder = $this->builder();
        if (!empty($searchValue)) {
            $builder->like('title', $searchValue);
            $builder->orLike('description', $searchValue);
        }

        return $builder->countAllResults();
    }
}
