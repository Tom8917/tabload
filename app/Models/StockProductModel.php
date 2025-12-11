<?php

namespace App\Models;
use CodeIgniter\Model;

class StockProductModel extends Model
{
    protected $table = 'stock_products';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'id_stock_type',
        'id_stock_provider',
        'unit_volume_ml',
        'unit_price',
        'image',
        'is_active',
    ];
}
