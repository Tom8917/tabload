<?php

namespace App\Models;

use CodeIgniter\Model;

class StatusModel extends Model
{
    protected $table = 'status';
    protected $primaryKey = 'id';

    protected $allowedFields = ['id', 'type'];

    protected $useTimestamps = false;
    protected $useSoftDeletes = false;

    public function findStatus($id)
    {
        return $this->find($id);
    }
}
