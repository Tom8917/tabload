<?php

namespace App\Models;

use CodeIgniter\Model;

class UserPermissionModel extends Model
{
    protected $table = 'user_permission';
    protected $primaryKey = 'id';

    protected $allowedFields = ['name', 'slug'];

    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[100]',
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Le nom de la permission est requis.',
            'min_length' => 'Le nom de la permission doit comporter au moins 3 caractères.',
            'max_length' => 'Le nom de la permission ne doit pas dépasser 100 caractères.',
        ],
    ];

    public function hasPermission($userId, $permission)
    {
        return $this->db->table('user_permission')
            ->where('id', $userId)
            ->where('name', $permission)
            ->countAllResults() > 0;
    }


    public function createPermission($data)
    {
        if (isset($data['name'])) {
            $data['slug'] = $this->generateUniqueSlug($data['name']);
        }

        return $this->insert($data);
    }

    public function updatePermission($id, $data)
    {
        if (isset($data['name'])) {
            $data['slug'] = $this->generateUniqueSlug($data['name']);
        }

        return $this->update($id, $data);
    }

    private function generateUniqueSlug($name)
    {
        $slug = generateSlug($name);
        $builder = $this->builder();

        $count = $builder->where('slug', $slug)->countAllResults();

        if ($count === 0) {
            return $slug;
        }

        $i = 1;
        while ($count > 0) {
            $newSlug = $slug . '-' . $i;
            $count = $builder->where('slug', $newSlug)->countAllResults();
            $i++;
        }

        return $newSlug;
    }

    public function getUsersByPermission($permissionId)
    {
        return $this->join('TableUser', 'TableUserPermission.id = TableUser.id_permission')
            ->where('TableUserPermission.id', $permissionId)
            ->select('TableUser.*, TableUserPermission.name as permission_name')
            ->findAll();
    }

    public function getAllPermissions()
    {
        return $this->findAll();
    }

    public function getUserPermissionById($id)
    {
        return $this->find($id);
    }

    public function deletePermission($id)
    {
        return $this->delete($id);
    }

    public function getPaginatedPermission($start, $length, $searchValue, $orderColumnName, $orderDirection)
    {
        $builder = $this->builder();
        if ($searchValue != null) {
            $builder->like('name', $searchValue);
        }

        if ($orderColumnName && $orderDirection) {
            $builder->orderBy($orderColumnName, $orderDirection);
        }

        $builder->limit($length, $start);

        return $builder->get()->getResultArray();
    }

    public function getTotalPermission()
    {
        $builder = $this->builder();
        return $builder->countAllResults();
    }

    public function getFilteredPermission($searchValue)
    {
        $builder = $this->builder();
        if (!empty($searchValue)) {
            $builder->like('name', $searchValue);
        }

        return $builder->countAllResults();
    }
}