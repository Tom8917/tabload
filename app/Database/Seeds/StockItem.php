<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class StockItem extends Seeder
{
    public function run()
    {
        $productModel = new \App\Models\StockProductModel();
        $products = $productModel->findAll();

        $productMap = [];
        foreach ($products as $product) {
            $typeId = $product['id_stock_type'];
            if (!isset($productMap[$typeId])) {
                $productMap[$typeId] = $product['id'];
            }
        }

        $data = [
            ['id_stock_type' => 1, 'id_stock_product' => $productMap[1] ?? null, 'name' => 'Base 50PG/50VG', 'quantity' => 10, 'unit_volume_ml' => 1000.00],
            ['id_stock_type' => 2, 'id_stock_product' => $productMap[2] ?? null, 'name' => 'Base 80PG/20VG', 'quantity' => 10, 'unit_volume_ml' => 1000.00],
            ['id_stock_type' => 3, 'id_stock_product' => $productMap[3] ?? null, 'name' => 'Base 30PG/70VG', 'quantity' => 10, 'unit_volume_ml' => 1000.00],
            ['id_stock_type' => 4, 'id_stock_product' => $productMap[4] ?? null, 'name' => 'Arôme Fruits rouges', 'quantity' => 10, 'unit_volume_ml' => 30.00],
            ['id_stock_type' => 5, 'id_stock_product' => $productMap[5] ?? null, 'name' => 'Arôme Fruit du dragon', 'quantity' => 10, 'unit_volume_ml' => 30.00],
            ['id_stock_type' => 6, 'id_stock_product' => $productMap[6] ?? null, 'name' => 'Arôme Fruits exotiques', 'quantity' => 10, 'unit_volume_ml' => 30.00],
            ['id_stock_type' => 9, 'id_stock_product' => $productMap[7] ?? null, 'name' => 'Fiole 50ml',  'quantity' => 10, 'unit_volume_ml' => 50.00],
            ['id_stock_type' => 8, 'id_stock_product' => $productMap[8] ?? null, 'name' => 'Fiole 100ml', 'quantity' => 10, 'unit_volume_ml' => 100.00],
            ['id_stock_type' => 7, 'id_stock_product' => $productMap[9] ?? null, 'name' => 'Fiole 200ml', 'quantity' => 10, 'unit_volume_ml' => 200.00],
            ['id_stock_type' => 10, 'id_stock_product' => $productMap[10] ?? null, 'name' => 'Booster nicotine 20mg/ml', 'quantity' => 30, 'unit_volume_ml' => 10.00],
        ];

        foreach ($data as $d) {
            if (!$d['id_stock_product']) {
                echo "⚠️ Aucun produit pour type ID: {$d['id_stock_type']}\n";
            }
        }

        $validData = array_filter($data, fn($item) => $item['id_stock_product'] !== null);
        $this->db->table('stock_items')->insertBatch($validData);
    }
}
