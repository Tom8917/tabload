<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\StockMovementModel;
use App\Models\StockItemModel;

class StockMovement extends BaseController
{
    public function getIndex($id_stock_item = null)
    {
        $itemModel = new StockItemModel();

        if (!$id_stock_item || !($item = $itemModel->find($id_stock_item))) {
            return redirect()->to('/admin/stockitem')->with('error', 'Produit non trouvé.');
        }

        return $this->view('admin/stock_movements/index', [
            'stockItemId' => $id_stock_item,
            'item'        => $item
        ], true);
    }

    public function postSearch($id_stock_item)
    {
        $request = $this->request;
        $draw = $request->getPost('draw');
        $start = $request->getPost('start');
        $length = $request->getPost('length');
        $searchValue = $request->getPost('search')['value'] ?? '';

        $model = new StockMovementModel();

        $builder = $model
            ->select('stock_movements.*, stock_items.name as item_name')
            ->join('stock_items', 'stock_items.id = stock_movements.id_stock_item', 'left')
            ->where('stock_movements.id_stock_item', $id_stock_item);

        if (!empty($searchValue)) {
            $builder->like('stock_movements.note', $searchValue);
        }

        $total = $builder->countAllResults(false);
        $data = $builder->orderBy('created_at', 'DESC')
            ->findAll($length, $start);

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => $data,
        ]);
    }

    public function postManualAdd()
    {
        $data = $this->request->getPost([
            'id_stock_item', 'type', 'quantity', 'note'
        ]);

        $data['created_at'] = date('Y-m-d H:i:s');

        (new StockMovementModel())->insert($data);

        return redirect()->to('/admin/stock_movement/' . $data['id_stock_item'])->with('success', 'Mouvement ajouté.');
    }
}
