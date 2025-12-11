<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\StockItemModel;

class StockItem extends BaseController
{
    protected $stockItemModel;

    public function __construct()
    {
        $this->stockItemModel = new StockItemModel();
    }

    public function getIndex()
    {
        $items = $this->stockItemModel->getAllWithRelations();

        $formattedItems = [];
        foreach ($items as $item) {
            $qty     = (float)($item['quantity'] ?? 0);
            $unitMl  = (float)($item['unit_volume_ml'] ?? 0);
            $totalMl = $qty * $unitMl;

            $qtyClass = 'stock-box-primary';
            if ($qty <= 5) {
                $qtyClass = 'stock-box-danger';
            } elseif ($qty <= 10) {
                $qtyClass = 'stock-box-warning';
            } elseif ($qty <= 20) {
                $qtyClass = 'stock-box-success';
            }

            $formattedItems[] = array_merge($item, [
                'total_volume_ml' => $totalMl,
                'qty_class'       => $qtyClass,
            ]);
        }

        return $this->view('admin/stock_items/index', [
            'items' => $formattedItems,
        ], true);
    }

    public function getEdit($id = null)
    {
        $item = $this->stockItemModel->getOneWithType((int)$id);
        if (! $item) {
            return redirect()->to('/admin/stockitem')->with('error', 'Produit introuvable.');
        }

        return $this->view('admin/stock_items/form', [
            'item' => $item,
        ], true);
    }

    public function postUpdate()
    {
        $post = $this->request->getPost([
            'id',
            'name',
            'quantity',
        ]);

        $item = $this->stockItemModel->find((int)$post['id']);
        if (! $item) {
            return redirect()->to('/admin/stockitem')->with('error', 'Produit introuvable.');
        }

        $image = $this->request->getFile('image');
        if ($image && $image->isValid() && ! $image->hasMoved()) {
            $newName = $image->getRandomName();
            $image->move(FCPATH . 'uploads/stock_items', $newName);
            $post['image'] = $newName;
        }

        if (! $this->stockItemModel->update($post['id'], $post)) {
            return redirect()->back()->withInput()->with('error', 'Échec lors de la mise à jour.');
        }

        return redirect()->to('/admin/stockitem')->with('success', 'Produit mis à jour.');
    }

    public function getSetAll($value = null)
    {
        if (! is_numeric($value) || (float)$value < 0) {
            return redirect()->to('/admin/stockitem')->with('error', 'Valeur invalide.');
        }

        $items = $this->stockItemModel->where('deleted_at IS NULL', null, false)->findAll();

        foreach ($items as $item) {
            $this->stockItemModel->update($item['id'], [
                'quantity' => (float)$value,
            ]);
        }

        return redirect()->to('/admin/stockitem')->with('success', "Tous les stocks mis à {$value} unités.");
    }

    public function getDelete($id)
    {
        $this->stockItemModel->delete((int)$id);
        return redirect()->to('/admin/stockitem')->with('success', 'Produit supprimé du stock.');
    }
}
