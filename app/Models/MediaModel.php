<?php

namespace App\Models;

use CodeIgniter\Model;

class MediaModel extends Model
{
    protected $table      = 'media';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $useTimestamps = true;
    protected $allowedFields = [
        'folder_id',
        'name',
        'mime_type',
        'file_size',
        'type',
        'entity_type',
        'entity_id',
        'deleted_at',
    ];

    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';

    public function getById(int $id): ?array
    {
        return $this->where('id', $id)->first();
    }

    public function deleteMedia(int $id): bool
    {
        return (bool)$this->delete($id);
    }
}