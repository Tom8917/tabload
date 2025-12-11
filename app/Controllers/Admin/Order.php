<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\StockItemModel;

class Order extends BaseController
{
    protected $orderModel;
    protected $orderItemModel;
    protected $stockItemModel;

    public function __construct()
    {
        $this->orderModel     = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->stockItemModel = new StockItemModel();
    }

    /**
     * GET /admin/order
     * Liste toutes les commandes en back-office
     */
    public function getIndex()
    {
        // 1) Récupérer toutes les commandes (ordonnées par date décroissante)
        $orders = $this->orderModel
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // 2) Pour chaque commande, récupérer ses order_items et le nom de chaque stock_item
        $ordersData = [];
        foreach ($orders as $order) {
            $itemsRaw = $this->orderItemModel
                ->where('order_id', $order['id'])
                ->findAll();

            $details = [];
            foreach ($itemsRaw as $item) {
                $stock = $this->stockItemModel->find($item['stock_item_id']);
                $details[] = [
                    'ingredient_name' => $stock ? $stock['name'] : '—',
                    'quantity_ml'     => $item['quantity_ml'],
                ];
            }

            $ordersData[] = [
                'id'         => $order['id'],
                'created_at' => $order['created_at'],
                'items'      => $details,
            ];
        }

        return $this->view('admin/orders/index', [
            'orders' => $ordersData
        ], true);
    }
}
