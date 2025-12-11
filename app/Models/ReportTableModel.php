<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportTableModel extends Model
{
    protected $table      = 'report_tables';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'section_id',
        'type',
        'title',
        'description',
        'data_json',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
}
