<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\StockRoleModel;
use App\Models\StockTypeModel;

class StockRole extends BaseController
{
    public function getIndex()
    {
        $roles = (new StockRoleModel())->findAll();
        return $this->view('admin/stock_roles/index', ['roles' => $roles], true);
    }

    public function getNew()
    {
        return $this->view('admin/stock_roles/form', [], true);
    }

    public function postCreate()
    {
        $model = new StockRoleModel();
        $data = $this->request->getPost(['name']);

        if (!$model->insert($data)) {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de l’enregistrement.');
        }

        return redirect()->to('/admin/stockrole')->with('success', 'Rôle ajouté.');
    }

    public function getEdit($id)
    {
        $model = new StockRoleModel();
        $role = $model->find($id);

        if (!$role) {
            return redirect()->to('/admin/stockrole')->with('error', 'Rôle introuvable.');
        }

        return $this->view('admin/stock_roles/form', ['role' => $role], true);
    }

    public function postUpdate()
    {
        $model = new StockRoleModel();
        $data = $this->request->getPost(['id', 'name']);

        if (!$model->save($data)) {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la mise à jour.');
        }

        return redirect()->to('/admin/stockrole')->with('success', 'Rôle modifié.');
    }

    public function getDelete($id)
    {
        $model = new StockRoleModel();

        if ($model->deleteStockRole((int)$id)) {
            return redirect()->to('/admin/stockrole')->with('success', 'Type supprimé.');
        }

        return redirect()->to('/admin/stockrole')->with('error', 'Erreur lors de la suppression.');
    }

}
