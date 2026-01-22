<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportVersionModel extends Model
{
    protected $table          = 'report_versions';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = false;

    protected $allowedFields = [
        'report_id',
        'version_label',
        'comment',
        'change_type',
        'changed_by',
        'created_at',
    ];
}
