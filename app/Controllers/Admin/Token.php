<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Token extends BaseController
{
    protected $require_auth = true;
    protected $requiredPermissions = ['administrateur', 'collaborateur'];
    protected $breadcrumb = [
        ['text' => 'Tableau de Bord', 'url' => '/admin/dashboard'],
        ['text' => 'Gestion des tokens API', 'url' => '/admin/token']
    ];

    public function getindex($id = null)
    {
        $atm = Model("ApiTokenModel");
        $um  = Model("UserModel");

        // Cas suppression : ?delete=1
        if ($id !== null && $this->request->getGet('delete') == 1) {
            if ($atm->delete($id)) {
                $this->success("Le token ID $id a bien été supprimé.");
            } else {
                $this->error("Erreur lors de la suppression du token ID $id.");
            }
            return $this->redirect('/admin/token');
        }

        // Si pas d'ID → listing
        if ($id === null) {
            $tokens = $atm->findAll();
            return $this->view("/admin/token/index.php", ['tokens' => $tokens], true);
        }

        // Si ID → récupération du token correspondant
        $tokenData = $atm->find($id);
        if (!$tokenData) {
            $this->error("Le token avec l'ID $id n'existe pas.");
            return $this->redirect("/admin/token");
        }

        // Récupération de l'utilisateur lié
        $user = $um->getUserById($tokenData['id_user']);
        $tokenData['user'] = $user;

        $this->addBreadcrumb("Détail du token ID " . $tokenData['id'], '');

        return $this->view("/admin/token/token.php", [
            'token' => $tokenData
        ], true);
    }

    public function postupdate()
    {
        // Récupérer les données du formulaire
        $id = $this->request->getPost('id');
        $id_user = $this->request->getPost('id_user');
        $token = $this->request->getPost('token');
        $counter = $this->request->getPost('counter');

        // Validation des données (tu peux ajouter des règles supplémentaires ici)
        if (!$id || !$id_user || !$token || !$counter) {
            return redirect()->back()->with('error', 'Tous les champs sont obligatoires');
        }

        // Mettre à jour le token dans la base de données
        $atm = Model("ApiTokenModel");
        $tokenData = [
            'id_user' => $id_user,
            'token' => $token,
            'counter' => $counter,
        ];

        $updateSuccess = $atm->update($id, $tokenData);

        if ($updateSuccess) {
            return redirect()->to('/admin/token')->with('success', 'Le token a été mis à jour avec succès');
        } else {
            return redirect()->back()->with('error', 'Une erreur est survenue lors de la mise à jour du token');
        }
    }

    public function getdelete($id){
        $atm = Model('ApiTokenModel');
        if ($atm->deleteToken($id)) {
            $this->success("Token supprimé");
        } else {
            $this->error("Token non supprimé");
        }
        $this->redirect('/admin/token');
    }

    public function postSearchToken()
    {
        $ApiTokenModel = model('App\Models\ApiTokenModel');

        $draw        = $this->request->getPost('draw');
        $start       = $this->request->getPost('start');
        $length      = $this->request->getPost('length');
        $searchValue = $this->request->getPost('search')['value'] ?? '';

        $orderColumnIndex = $this->request->getPost('order')[0]['column'] ?? 0;
        $orderDirection   = $this->request->getPost('order')[0]['dir'] ?? 'asc';
        $columns          = $this->request->getPost('columns');
        $orderColumnName  = $columns[$orderColumnIndex]['data'] ?? 'id';

        $data = $ApiTokenModel->getPaginatedToken($start, $length, $searchValue, $orderColumnName, $orderDirection);
        $totalRecords    = $ApiTokenModel->getTotalToken();
        $filteredRecords = $ApiTokenModel->getFilteredToken($searchValue);

        $result = [
            'draw'            => ($draw),
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $data,
        ];

        return $this->response->setJSON($result);
    }
}
