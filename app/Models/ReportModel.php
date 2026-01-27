<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportModel extends Model
{
    protected $table          = 'reports';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'user_id',
        'title',
        'application_name',
        'application_version',
        'version',
        'author_name',
        'status',
        'doc_status',
        'doc_version',
        'modification_kind',
        'file_media_id',
        'version_date',
        'author_updated_at',
        'corrected_by',
        'corrected_at',
        'validated_by',
        'validated_at',
        'comments',
    ];

    public function findWithUsers(int $reportId): ?array
    {
        return $this->select('
            reports.*,
            CONCAT(vu.firstname, " ", vu.lastname) AS validated_by_name,
            CONCAT(cu.firstname, " ", cu.lastname) AS corrected_by_name
        ')
            ->join('user vu', 'vu.id = reports.validated_by', 'left')
            ->join('user cu', 'cu.id = reports.corrected_by', 'left')
            ->where('reports.id', $reportId)
            ->first();
    }

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

    public function docVersionFromStatus(string $docStatus): string
    {
        return match ($docStatus) {
            'validated' => 'v1.0',
            default     => 'v0.1',
        };
    }
}
