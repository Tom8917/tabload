<?php

namespace App\Models;

use CodeIgniter\Model;

class MediaFolderModel extends Model
{
    protected $table      = 'media_folders';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'name',
        'parent_id',
        'user_id',
        'sort_order',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getById(int $id): ?array
    {
        return $this->find($id);
    }

    public function getChildren(?int $parentId): array
    {
        if ($parentId === null) {
            return $this->where('parent_id', null)->orderBy('sort_order', 'ASC')->orderBy('name', 'ASC')->findAll();
        }
        return $this->where('parent_id', $parentId)->orderBy('sort_order', 'ASC')->orderBy('name', 'ASC')->findAll();
    }
}
