<?php

namespace App\Models;

use CodeIgniter\Model;

class PriorityModel extends Model
{
    protected $table = 'priority';
    protected $primaryKey = 'id';

    protected $allowedFields = ['id', 'type'];

    protected $useTimestamps = false;
    protected $useSoftDeletes = false;

    public function findPriority($id)
    {
        return $this->find($id);
    }
}
