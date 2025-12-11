<?php
namespace App\Models;

use CodeIgniter\Model;

class EventModel extends Model
{
    protected $table         = 'events';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'title','course_id','type','starts_at','ends_at','all_day','location','notes','color'
    ];

    public function ranged(string $from, string $to): array
    {
        return $this->where('starts_at >=', $from)
            ->where('starts_at <=', $to)
            ->orderBy('starts_at','ASC')
            ->findAll();
    }
}
