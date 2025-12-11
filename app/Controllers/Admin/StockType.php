<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\StockItemModel;
use App\Models\StockTypeModel;
use App\Models\StockRoleModel;
use App\Models\StockTypeRoleModel;

class StockType extends BaseController
{
    public function getIndex()
    {
        $types = (new StockTypeModel())->findAll();
        return $this->view('admin/stock_types/index', ['types' => $types], true);
    }

    public function getNew()
    {
        $roles = (new StockRoleModel())->findAll();
        return $this->view('admin/stock_types/form', [
            'stockRoles'     => $roles,
            'existingRoleIds'=> [],
            'type'           => null
        ], true);
    }

    public function getEdit($id)
    {
        $model = new StockTypeModel();
        $type  = $model->find($id);
        if (! $type) {
            return redirect()->to('/admin/stocktype')->with('error', 'Type introuvable.');
        }

        $roles           = (new StockRoleModel())->findAll();
        $existingRoleIds = array_column(
            (new StockTypeRoleModel())->where('id_stock_type', $id)->findAll(),
            'id_stock_role'
        );

        return $this->view('admin/stock_types/form', [
            'type'             => $type,
            'stockRoles'       => $roles,
            'existingRoleIds'  => $existingRoleIds
        ], true);
    }

    public function postCreate()
    {
        $model     = new StockTypeModel();
        $roleModel = new StockTypeRoleModel();

        $data = $this->request->getPost([
            'name',
            'unit_volume_ml',
        ]);
        $roleIds = $this->request->getPost('roles') ?? [];

        if (! $model->insert($data)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de l’enregistrement du type.');
        }

        $typeId = $model->getInsertID();
        $roleModel->syncRolesForType($typeId, $roleIds);

        $image = $this->request->getFile('image');
        if ($image && $image->isValid() && ! $image->hasMoved()) {
            $newName = $image->getRandomName();
            $image->move(FCPATH . 'uploads/stock_types', $newName);
            $model->update($typeId, ['image' => $newName]);
        }

        return redirect()->to('/admin/stocktype')->with('success', 'Type enregistré.');
    }

    public function postUpdate()
    {
        $model     = new StockTypeModel();
        $roleModel = new StockTypeRoleModel();

        $data = $this->request->getPost([
            'id',
            'name',
            'unit_volume_ml'
        ]);
        $roleIds = $this->request->getPost('roles') ?? [];

        if (! $model->save($data)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour.');
        }

        $roleModel->syncRolesForType((int)$data['id'], $roleIds);

        $image = $this->request->getFile('image');
        if ($image && $image->isValid() && ! $image->hasMoved()) {
            $newName = $image->getRandomName();
            $image->move(FCPATH . 'uploads/stock_types', $newName);
            $model->update($data['id'], ['image' => $newName]);
        }

        return redirect()->to('/admin/stocktype')->with('success', 'Type modifié.');
    }

    public function getDelete($id)
    {
        $model = new StockTypeModel();

        if ($model->deleteStockType((int)$id)) {
            return redirect()->to('/admin/stocktype')->with('success', 'Type supprimé.');
        }

        return redirect()->to('/admin/stocktype')->with('error', 'Erreur lors de la suppression.');
    }
}
