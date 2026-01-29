<?php

namespace App\Models;

use CodeIgniter\Model;

class LogModel extends Model
{
    protected $table            = 'logs';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'message',
        'meta',
        'created_at',
    ];

    public function latestWithUser(int $limit = 30): array
    {
        return $this->select('logs.*, CONCAT(u.firstname, " ", u.lastname) AS user_fullname, u.email AS user_email')
            ->join('user u', 'u.id = logs.user_id', 'left')
            ->orderBy('logs.id', 'DESC')
            ->findAll($limit);
    }
}
