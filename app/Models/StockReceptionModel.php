<?php

namespace App\Models;

use CodeIgniter\Model;

class StockReceptionModel extends Model
{
    protected $table            = 'stock_receptions';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'id_stock_product',
        'units',
        'unit_price',
        'price_total',
        'note',
        'created_at',
        'updated_at'
    ];
}
