<?php

namespace App\Services;

use App\Models\StockItemModel;

class StockService
{
    protected $stockModel;

    public function __construct()
    {
        $this->stockModel = new StockItemModel();
    }

    public function incrementStock(int $idStockType, float $unitsToAdd, float $totalCost = 0.0): bool
    {
        return $this->stockModel
            ->where('id_stock_type', $idStockType)
            ->where('deleted_at IS NULL', null, false)
            ->set('quantity', 'quantity + ' . $unitsToAdd, false)
            ->update();
    }

    public function decrementStockByMl(int $idStockItem, float $ml): bool
    {
        $item = $this->stockModel->find($idStockItem);
        if (!$item || $item['unit_volume_ml'] <= 0) return false;

        $unitVolume = $item['unit_volume_ml'];
        $totalAvailable = $item['quantity'] * $unitVolume;
        $newTotal = max(0, $totalAvailable - $ml);
        $newUnits = $newTotal / $unitVolume;

        return $this->stockModel->update($idStockItem, ['quantity' => round($newUnits, 2)]);
    }

    public function decrementStockByUnit(int $idStockItem, float $units): bool
    {
        $item = $this->stockModel->find($idStockItem);
        if (!$item) return false;

        $newUnits = max(0, $item['quantity'] - $units);
        return $this->stockModel->update($idStockItem, ['quantity' => round($newUnits, 2)]);
    }

    public function calculateCost(array $roleData, array $dosages, float $volumeRecette): float
    {
        $totalCost = 0.0;

        foreach ($dosages as $roleName => $pourcentage) {
            if (isset($roleData[$roleName])) {
                $item = $this->stockModel->find($roleData[$roleName]);

                if ($item && $item['unit_volume_ml'] > 0 && isset($item['purchase_price_unit'])) {
                    $unitMl = (float) $item['unit_volume_ml'];
                    $unitPrice = (float) $item['purchase_price_unit'];

                    $mlUsed = ($pourcentage / 100) * $volumeRecette;
                    $cost = ($mlUsed / $unitMl) * $unitPrice;
                    $totalCost += $cost;
                }
            }
        }

        return round($totalCost, 2);
    }
}
