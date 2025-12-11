<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\{StockReceptionModel, StockProductModel, StockItemModel};

class StockReception extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new StockReceptionModel();
    }

    public function getIndex()
    {
        $receptions = $this->model
            ->select('
        stock_receptions.*,
        stock_products.unit_price as expected_price,
        stock_products.unit_volume_ml,
        stock_types.name as type_name,
        stock_providers.name as provider_name,
        stock_types.image as type_image
    ')
            ->join('stock_products', 'stock_products.id = stock_receptions.id_stock_product')
            ->join('stock_types', 'stock_types.id = stock_products.id_stock_type')
            ->join('stock_providers', 'stock_providers.id = stock_products.id_stock_provider')
            ->orderBy('stock_receptions.created_at', 'DESC')
            ->findAll();

        $chartData = [];
        foreach ($receptions as $recv) {
            $date = date('Y-m-d', strtotime($recv['created_at']));
            $chartData[$date] = ($chartData[$date] ?? 0) + (float) $recv['units'];
        }

        ksort($chartData);
        $chartDataFormatted = array_map(fn($d, $u) => ['date' => $d, 'units' => round($u, 2)], array_keys($chartData), $chartData);

        return $this->view('admin/stock_receptions/index', [
            'receptions' => $receptions,
            'chartData'  => array_slice($chartDataFormatted, -7, 7)
        ], true);
    }

    public function getEdit($id)
    {
        $reception = $this->model->find((int)$id);

        if (! $reception) {
            return redirect()->to('/admin/stockreception')->with('error', 'Réception introuvable.');
        }

        $products = (new StockProductModel())
            ->select('stock_products.*, stock_types.name as type_name, stock_providers.name as provider_name')
            ->join('stock_types', 'stock_types.id = stock_products.id_stock_type')
            ->join('stock_providers', 'stock_providers.id = stock_products.id_stock_provider')
            ->where('stock_products.is_active', 1)
            ->findAll();

        return $this->view('admin/stock_receptions/form', [
            'products'   => $products,
            'reception'  => $reception
        ], true);
    }

    public function getShow($id)
    {
        $reception = $this->model
            ->select('stock_receptions.*, stock_types.name as type_name, stock_providers.name as provider_name, stock_products.unit_volume_ml')
            ->join('stock_products', 'stock_products.id = stock_receptions.id_stock_product')
            ->join('stock_types', 'stock_types.id = stock_products.id_stock_type')
            ->join('stock_providers', 'stock_providers.id = stock_products.id_stock_provider')
            ->find($id);

        if (! $reception) {
            return redirect()->to('/admin/stockreception')->with('error', 'Réception introuvable.');
        }

        return $this->view('admin/stock_receptions/show', [
            'reception' => $reception,
            'product'   => $reception,
        ], true);
    }

    public function getNew()
    {
        $products = (new StockProductModel())
            ->select('stock_products.*, stock_types.name as type_name, stock_providers.name as provider_name')
            ->join('stock_types', 'stock_types.id = stock_products.id_stock_type')
            ->join('stock_providers', 'stock_providers.id = stock_products.id_stock_provider')
            ->where('stock_products.is_active', 1)
            ->findAll();

        return $this->view('admin/stock_receptions/form', [
            'products' => $products,
            'reception' => null
        ], true);
    }

    public function postCreate()
    {
        $data = $this->request->getPost([
            'id_stock_product', 'units', 'price_total', 'note'
        ]);

        $data['units'] = (float) $data['units'];
        $data['price_total'] = $data['price_total'] !== '' ? (float) $data['price_total'] : null;

        $data['unit_price'] = ($data['price_total'] !== null && $data['units'] > 0)
            ? round($data['price_total'] / $data['units'], 4)
            : null;

        if ($data['units'] <= 0 || $data['unit_price'] === null) {
            return redirect()->back()->withInput()->with('error', 'Données invalides.');
        }

        $productModel = new StockProductModel();
        $product = $productModel->find($data['id_stock_product']);

        if (! $product) {
            return redirect()->back()->with('error', 'Produit introuvable.');
        }

        // Insérer la réception
        if (! $this->model->insert($data)) {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la réception.');
        }

        // Mise à jour ou création de stock_item
        $itemModel = new StockItemModel();
        $item = $itemModel->where('id_stock_product', $data['id_stock_product'])->first();

        if ($item) {
            $newQty = (float) $item['quantity'] + $data['units'];
            $itemModel->update($item['id'], ['quantity' => $newQty]);
        } else {
            $itemModel->insert([
                'id_stock_product' => $data['id_stock_product'],
                'quantity'         => $data['units'],
                'unit_volume_ml'   => $product['unit_volume_ml'],
            ]);
        }

        return redirect()->to('/admin/stockreception')->with('success', 'Réception enregistrée.');
    }

    public function postUpdate()
    {
        $id = $this->request->getPost('id');
        $data = $this->request->getPost([
            'id_stock_product', 'units', 'price_total', 'note'
        ]);

        $data['units'] = (float) $data['units'];
        $data['price_total'] = $data['price_total'] !== '' ? (float) $data['price_total'] : null;

        // Calcul automatique du prix unitaire
        $data['unit_price'] = ($data['price_total'] !== null && $data['units'] > 0)
            ? round($data['price_total'] / $data['units'], 4)
            : null;

        if ($data['units'] <= 0 || $data['unit_price'] === null) {
            return redirect()->back()->withInput()->with('error', 'Données invalides.');
        }

        $reception = $this->model->find($id);
        if (! $reception) {
            return redirect()->to('/admin/stockreception')->with('error', 'Réception introuvable.');
        }

        // Mise à jour de la réception
        $this->model->update($id, $data);

        // Mise à jour du stock si la quantité a changé
        $itemModel = new StockItemModel();
        $item = $itemModel->where('id_stock_product', $data['id_stock_product'])->first();

        if ($item) {
            $diff = $data['units'] - $reception['units'];
            $newQty = $item['quantity'] + $diff;

            if ($newQty < 0) {
                return redirect()->back()->with('error', 'Stock insuffisant pour cette mise à jour.');
            }

            $itemModel->update($item['id'], ['quantity' => $newQty]);
        }

        return redirect()->to('/admin/stockreception')->with('success', 'Réception mise à jour.');
    }

    public function getDelete($id)
    {
        $reception = $this->model->find((int)$id);
        if (! $reception) {
            return redirect()->to('/admin/stockreception')->with('error', 'Réception introuvable.');
        }

        $itemModel = new StockItemModel();
        $item = $itemModel->where('id_stock_product', $reception['id_stock_product'])->first();

        // Si le produit existe dans le stock, on décrémente la quantité (même si négatif)
        if ($item) {
            $newQty = (float)$item['quantity'] - (float)$reception['units'];
            $itemModel->update($item['id'], ['quantity' => $newQty]);
        }

        // Puis on supprime la réception
        $this->model->delete((int)$id);

        return redirect()->to('/admin/stockreception')->with('success', 'Réception supprimée. Stock ajusté.');
    }

}
