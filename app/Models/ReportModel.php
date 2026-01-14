<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportModel extends Model
{
    protected $table      = 'reports';
    protected $primaryKey = 'id';
    protected $requiredPermissions = ['administrateur'];

    protected $allowedFields = [
        'user_id',
        'title',
        'application_name',
        'version',
        'author_name',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;

    public function findAllForUser(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function findOneForUser(int $reportId, int $userId): ?array
    {
        return $this->where('id', $reportId)
            ->where('user_id', $userId)
            ->first();
    }
}
