<?php

namespace App\Models;

use CodeIgniter\Model;

class StockMovementModel extends Model
{
    protected $table            = 'stock_movements';
    protected $primaryKey       = 'id';
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'id_stock_item',
        'type',
        'quantity',
        'note',
        'created_at',
    ];

    // Méthode personnalisée (utile si utilisée dans d’autres endroits)
    public function getMovementsByStockItem($id_stock_item, $limit = 50)
    {
        return $this->select('stock_movements.*, stock_items.name as item_name')
            ->join('stock_items', 'stock_items.id = stock_movements.id_stock_item')
            ->where('stock_movements.id_stock_item', $id_stock_item)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
