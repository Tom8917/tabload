<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportVersionModel extends Model
{
    protected $table      = 'report_versions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    protected $allowedFields = [
        'report_id',
        'version_label',
        'comment',
        'change_type',
        'doc_status',
        'changed_by',
    ];
}
