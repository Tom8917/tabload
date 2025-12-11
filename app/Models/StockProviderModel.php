<?php

namespace App\Models;

use CodeIgniter\Model;

class StockProviderModel extends Model
{
    protected $table = 'stock_providers';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'image'];
    protected $useTimestamps = false;
}
