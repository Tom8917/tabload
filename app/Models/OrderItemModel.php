<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderItemModel extends Model
{
    protected $table      = 'order_items';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'order_id', 'stock_item_id', 'quantity_ml', 'unit_price', 'created_at'
    ];
    public $useTimestamps = false;
}
