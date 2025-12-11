<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\{StockProductModel, StockProviderModel, StockTypeModel};

class StockProduct extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new StockProductModel();
    }

    public function getIndex()
    {
        $products = $this->model
            ->select('stock_products.*, stock_types.name as type_name, stock_providers.name as provider_name')
            ->join('stock_types', 'stock_types.id = stock_products.id_stock_type')
            ->join('stock_providers', 'stock_providers.id = stock_products.id_stock_provider')
            ->orderBy('stock_products.id', 'ASC')
            ->findAll();

        return $this->view('admin/stock_products/index', [
            'products' => $products
        ], true);
    }

    public function getNew()
    {
        return $this->view('admin/stock_products/form', [
            'product'      => null,
            'types'        => (new StockTypeModel())->findAll(),
            'providers'    => (new StockProviderModel())->findAll(),
        ], true);
    }

    public function getEdit($id)
    {
        $product = $this->model->find($id);
        if (! $product) {
            return redirect()->to('/admin/stockproduct')->with('error', 'Produit introuvable.');
        }

        return $this->view('admin/stock_products/form', [
            'product'      => $product,
            'types'        => (new StockTypeModel())->findAll(),
            'providers'    => (new StockProviderModel())->findAll(),
        ], true);
    }

    public function postCreate()
    {
        $data = $this->request->getPost([
            'id_stock_type',
            'id_stock_provider',
            'unit_price'
        ]);

        // Récupérer la contenance automatiquement depuis le type
        $typeModel = new \App\Models\StockTypeModel();
        $type = $typeModel->find($data['id_stock_type']);
        if (! $type) {
            return redirect()->back()->withInput()->with('error', 'Type sélectionné invalide.');
        }
        $data['unit_volume_ml'] = $type['unit_volume_ml'];

        // Gérer l'image si envoyée
        $image = $this->request->getFile('image');
        if ($image && $image->isValid() && ! $image->hasMoved()) {
            $newName = $image->getRandomName();
            $image->move(FCPATH . 'uploads/stock_products', $newName);
            $data['image'] = $newName;
        }

        // Insérer le produit
        $model = new \App\Models\StockProductModel();
        if (! $model->insert($data)) {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la création du produit.');
        }

        return redirect()->to('/admin/stockproduct')->with('success', 'Produit enregistré.');
    }

    public function postUpdate()
    {
        $data = $this->request->getPost([
            'id',
            'id_stock_type',
            'id_stock_provider',
            'unit_price'
        ]);

        // Récupérer la contenance depuis le type
        $typeModel = new \App\Models\StockTypeModel();
        $type = $typeModel->find($data['id_stock_type']);
        if (! $type) {
            return redirect()->back()->withInput()->with('error', 'Type sélectionné invalide.');
        }
        $data['unit_volume_ml'] = $type['unit_volume_ml'];

        // Gérer une nouvelle image
        $image = $this->request->getFile('image');
        if ($image && $image->isValid() && ! $image->hasMoved()) {
            $newName = $image->getRandomName();
            $image->move(FCPATH . 'uploads/stock_products', $newName);
            $data['image'] = $newName;
        }

        $model = new \App\Models\StockProductModel();
        if (! $model->update($data['id'], $data)) {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la mise à jour du produit.');
        }

        return redirect()->to('/admin/stockproduct')->with('success', 'Produit mis à jour.');
    }


    public function getDelete($id)
    {
        if (! $this->model->delete((int)$id)) {
            return redirect()->to('/admin/stockproduct')->with('error', 'Erreur lors de la suppression.');
        }

        return redirect()->to('/admin/stockproduct')->with('success', 'Produit supprimé.');
    }
}
