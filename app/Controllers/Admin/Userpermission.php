<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Userpermission extends BaseController
{
    protected $require_auth = true;
    protected $requiredPermissions = ['administrateur'];
    public function getIndex()
    {
        $upm = model('UserPermissionModel');
        $permissions = $upm->findAll();

        return $this->view('/admin/user/index-permission', [
            'permissions' => $permissions
        ], true);
    }

    public function postupdate() {
        $data = $this->request->getPost();
        $upm = Model("/UserPermissionModel");
        if ($upm->updatePermission($data['id'], $data)) {
            $this->success("Permission à bien été modifié");
        } else {
            $this->error("Une erreur est survenue");
        }
        $this->redirect("/admin/userpermission");
    }

    public function postcreate() {
        $data = $this->request->getPost();
        $upm = Model("UserPermissionModel");
        if ($upm->createPermission($data)) {
            $this->success("Le rôle à bien été ajouté.");
            $this->redirect("/admin/userpermission");
        } else {
            $errors = $upm->errors();
            foreach ($errors as $error) {
                $this->error($error);
            }
            $this->redirect("/admin/userpermission/new");
        }
    }

    public function getdelete($id){
        $upm = Model('UserPermissionModel');
        if ($upm->deletePermission($id)) {
            $this->success("Rôle supprimé");
        } else {
            $this->error("Rôle non supprimé");
        }
        $this->redirect('/admin/userpermission');
    }

    public function postSearchPermission()
    {
        $UserModel = model('App\Models\UserPermissionModel');

        // Paramètres de pagination et de recherche envoyés par DataTables
        $draw        = $this->request->getPost('draw');
        $start       = $this->request->getPost('start');
        $length      = $this->request->getPost('length');
        $searchValue = $this->request->getPost('search')['value'];

        // Obtenez les informations sur le tri envoyées par DataTables
        $orderColumnIndex = $this->request->getPost('order')[0]['column'];
        $orderDirection = $this->request->getPost('order')[0]['dir'];
        $orderColumnName = $this->request->getPost('columns')[$orderColumnIndex]['data'];

        // Obtenez les données triées et filtrées
        $data = $UserModel->getPaginatedPermission($start, $length, $searchValue, $orderColumnName, $orderDirection);

        // Obtenez le nombre total de lignes sans filtre
        $totalRecords = $UserModel->getTotalPermission();

        // Obtenez le nombre total de lignes filtrées pour la recherche
        $filteredRecords = $UserModel->getFilteredPermission($searchValue);

        $result = [
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $data,
        ];

        $result['csrfHash'] = csrf_hash();

        return $this->response->setJSON($result);
    }
}