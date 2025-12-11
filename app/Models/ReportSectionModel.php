<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportSectionModel extends Model
{
    protected $table      = 'report_sections';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'report_id',
        'parent_id',
        'position',
        'level',
        'code',
        'title',
        'content',
        'period_label',
        'period_number',
        'debit_value',
        'start_date',
        'end_date',
        'compliance_status',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
}
