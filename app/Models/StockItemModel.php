<?php

namespace App\Models;

use CodeIgniter\Model;

class StockItemModel extends Model
{
    protected $table            = 'stock_items';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $allowedFields    = ['id_stock_product', 'quantity', 'unit_volume_ml'];

    public function getAllWithRelations(): array
    {
        return $this
            ->select('
            stock_items.*,
            stock_products.unit_price,
            stock_products.unit_volume_ml,
            stock_types.name as type_name,
            stock_types.image as type_image,
            stock_providers.name as provider_name
        ')
            ->join('stock_products', 'stock_products.id = stock_items.id_stock_product')
            ->join('stock_types', 'stock_types.id = stock_products.id_stock_type')
            ->join('stock_providers', 'stock_providers.id = stock_products.id_stock_provider')
            ->orderBy('stock_types.name', 'ASC')
            ->orderBy('stock_providers.name', 'ASC')
            ->findAll();
    }

    public function getAllWithType(): array
    {
        return $this->select('stock_items.*, stock_types.name AS type_name')
            ->join('stock_types', 'stock_types.id = stock_items.id_stock_type')
            ->where('stock_items.deleted_at IS NULL', null, false)
            ->findAll();
    }

    public function getOneWithType(int $id)
    {
        return $this->select('
                stock_items.*,
                stock_types.name AS type_name,
                stock_types.unit_volume_ml AS type_unit_volume_ml
            ')
            ->join('stock_types', 'stock_types.id = stock_items.id_stock_type')
            ->where('stock_items.id', $id)
            ->where('stock_items.deleted_at IS NULL', null, false)
            ->first();
    }
}
