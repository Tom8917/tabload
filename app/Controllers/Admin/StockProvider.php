<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\StockProviderModel;

class StockProvider extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new StockProviderModel();
    }

    public function getIndex()
    {
        $providers = $this->model->findAll();

        return $this->view('admin/stock_providers/index', [
            'providers' => $providers
        ], true);
    }

    public function getNew()
    {
        return $this->view('admin/stock_providers/form', [
            'provider' => null
        ], true);
    }

    public function getEdit($id)
    {
        $provider = $this->model->find($id);

        if (! $provider) {
            return redirect()->to('/admin/stockprovider')->with('error', 'Fournisseur introuvable.');
        }

        return $this->view('admin/stock_providers/form', [
            'provider' => $provider
        ], true);
    }

    public function postCreate()
    {
        $data = $this->request->getPost(['name']);

        if (! $this->model->insert($data)) {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la création du fournisseur.');
        }

        $id = $this->model->getInsertID();

        $image = $this->request->getFile('image');
        if ($image && $image->isValid() && ! $image->hasMoved()) {
            $newName = $image->getRandomName();
            $image->move(FCPATH . 'uploads/stock_providers', $newName);
            $this->model->update($id, ['image' => $newName]);
        }

        return redirect()->to('/admin/stockprovider')->with('success', 'Fournisseur créé.');
    }

    public function postUpdate()
    {
        $data = $this->request->getPost(['id', 'name']);

        if (! $this->model->save($data)) {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la mise à jour du fournisseur.');
        }

        $image = $this->request->getFile('image');
        if ($image && $image->isValid() && ! $image->hasMoved()) {
            $newName = $image->getRandomName();
            $image->move(FCPATH . 'uploads/stock_providers', $newName);
            $this->model->update($data['id'], ['image' => $newName]);
        }

        return redirect()->to('/admin/stockprovider')->with('success', 'Fournisseur modifié.');
    }

    public function getDelete($id)
    {
        if (! $this->model->delete($id)) {
            return redirect()->to('/admin/stockprovider')->with('error', 'Erreur lors de la suppression.');
        }

        return redirect()->to('/admin/stockprovider')->with('success', 'Fournisseur supprimé.');
    }
}
