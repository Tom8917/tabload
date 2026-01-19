<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportModel extends Model
{
    protected $table            = 'reports';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    // On garde useTimestamps pour created_at/updated_at
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'user_id',
        'title',
        'application_name',
        'version',
        'author_name',
        'status',

        // nouveaux champs
        'doc_status',
        'version_date',
        'author_updated_at',
        'corrected_by',
        'corrected_at',
        'validated_by',
        'validated_at',
    ];

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
