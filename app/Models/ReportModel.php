<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportModel extends Model
{
    protected $table      = 'reports';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'title',
        'application_name',
        'version',
        'author_name',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
}
