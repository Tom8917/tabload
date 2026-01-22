<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
class User extends BaseController
{
    protected $require_auth = true;
    protected $requiredPermissions = ['administrateur', 'collaborateur'];
//    protected $breadcrumb =  [['text' => 'Tableau de Bord','url' => '/admin/dashboard'],['text'=> 'Gestion des utilisateurs', 'url' => '/admin/user']];

    public function getindex($id = null)
    {
        $um = Model("UserModel");
        $bm = Model("BlacklistModel");
        $atm = Model("ApiTokenModel");

        if ($id == null) {
            $users = $um->getPermissions();
            return $this->view("/admin/user/index.php", ['users' => $users], true);
        }

        if ($id == 'blacklist') {
            $userId = $this->request->getPost('id_user');
            if ($userId) {
                $result = $bm->addToBlacklist($userId);
                $result ? $this->success("L'utilisateur a été ajouté à la blacklist") : $this->error("Erreur lors de l'ajout à la blacklist");
            } else {
                $this->error("ID utilisateur non spécifié");
            }
            return $this->redirect("/admin/user");
        }

        $permissions = Model("UserPermissionModel")->getAllPermissions();

        if ($id == "new") {
            $this->addBreadcrumb('Création d\'un utilisateur', '');
            return $this->view("/admin/user/user", ["permissions" => $permissions], true);
        }

        $utilisateur = $um->getUserById($id);
        if ($utilisateur) {
            // Récupérer le token utilisateur
            $tokenData = $atm->where('id_user', $id)->first();
            $utilisateur['id_api_tokens'] = $tokenData['token'] ?? '';

            $this->addBreadcrumb('Modification de ' . $utilisateur['firstname'] . " " . $utilisateur['lastname'], '');

            return $this->view("/admin/user/user", [
                "utilisateur" => $utilisateur,
                "permissions" => $permissions,
            ], true);
        } else {
            $this->error("L'ID de l'utilisateur n'existe pas");
            return $this->redirect("/admin/user");
        }
    }

    public function postupdate()
    {
        $data = $this->request->getPost();
        $um = Model("UserModel");

        $file = $this->request->getFile('profile_image');
        if ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) {
            $mm = Model('MediaModel');
            $old_media = $mm->getMediaByEntityIdAndType($data['id'], 'user');

            $mediaData = [
                'entity_type' => 'user',
                'entity_id'   => $data['id'],
            ];

            $uploadResult = upload_file($file, 'avatar', $data['id'], $mediaData, true, ['image/jpeg', 'image/png', 'image/jpg']);
            if (is_array($uploadResult) && $uploadResult['status'] === 'error') {
                $this->error("Erreur lors de l'upload de l'image : " . $uploadResult['message']);
                return $this->redirect("/admin/user");
            }

            if ($old_media) {
                $mm->deleteMedia($old_media[0]['id']);
            }
        }

        if ($um->updateUser($data['id'], $data)) {
            $this->success("L'utilisateur a bien été modifié.");
        } else {
            $errors = $um->errors();
            foreach ($errors as $error) {
                $this->error($error);
            }
        }

        return $this->redirect("/admin/user");
    }

    public function getblacklist($id)
    {
        $bm = Model("BlacklistModel");

        // Ajouter l'utilisateur à la blacklist
        $result = $bm->addToBlacklist($id);

        if ($result) {
            $this->success("Utilisateur ajouté à la blacklist");
        } else {
            $this->error("Une erreur est survenue lors de l'ajout à la blacklist");
        }

        return $this->redirect("/admin/user");
    }

    public function getremoveblacklist($id)
    {
        $bm = Model("BlacklistModel");

        // Retirer l'utilisateur de la blacklist
        $result = $bm->removeFromBlacklist($id);

        if ($result) {
            $this->success("Utilisateur retiré de la blacklist");
        } else {
            $this->error("Une erreur est survenue lors du retrait de la blacklist");
        }

        return $this->redirect("/admin/user");
    }


    public function postcreate()
    {
        $data = $this->request->getPost();
        $um = model("UserModel");

        // Créer l'utilisateur
        $newUserId = $um->createUser($data);

        if ($newUserId) {
            // Gérer l'image si elle existe
            $file = $this->request->getFile('profile_image');
            if ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) {
                $mediaData = [
                    'entity_type' => 'user',
                    'entity_id'   => $newUserId,
                ];

                $uploadResult = upload_file($file, 'avatar', $newUserId, $mediaData, true, ['image/jpeg', 'image/png', 'image/jpg']);

                if (is_array($uploadResult) && $uploadResult['status'] === 'error') {
                    $this->error("Une erreur est survenue lors de l'upload de l'image : " . $uploadResult['message']);
                    return $this->redirect("/admin/user/new");
                }
            }

            $this->success("L'utilisateur a bien été ajouté.");
            return $this->redirect("/admin/user");
        } else {
            $errors = $um->errors();
            foreach ($errors as $error) {
                $this->error($error);
            }
            return $this->redirect("/admin/user/new");
        }
    }


    public function getdelete($id){
        $um = Model('UserModel');
        if ($id == 1) {
            $this->error("L'utilisateur admin ne peut pas être supprimé.");
            return $this->redirect('/admin/user');
        }
        if ($um->deleteUser($id)) {
            $this->success("Utilisateur supprimé");
        } else {
            $this->error("Utilisateur non supprimé");
        }
        $this->redirect('/admin/user');
    }

    // Activer / désactiver utilisateur
    public function getdeactivate($id){
        $um = Model('UserModel');
        if ($id == 1) {
            $this->error("L'utilisateur admin ne peut pas être supprimé.");
            return $this->redirect('/admin/user');
        }
        if ($um->deactivateUser($id)) {
            $this->success("Utilisateur désactivé");
        } else {
            $this->error("Utilisateur non désactivé");
        }
        $this->redirect('/admin/user');
    }

    public function getactivate($id){
        $um = Model('UserModel');
        if ($um->activateUser($id)) {
            $this->success("Utilisateur activé");
        } else {
            $this->error("Utilisateur non activé");
        }
        $this->redirect('/admin/user');
    }

    public function getdeactivate2($id){
        $um = Model('UserModel');
        if ($id == 1) {
            $this->error("L'utilisateur admin ne peut pas être supprimé.");
            return $this->redirect('/admin/user');
        }
        if ($um->deactivateUser($id)) {
            $this->success("Utilisateur banni");
        } else {
            $this->error("Utilisateur débanni");
        }
        $this->redirect('/admin/user');
    }

    public function getactivate2($id){
        $um = Model('UserModel');
        if ($um->activateUser($id)) {
            $this->success("Utilisateur banni");
        } else {
            $this->error("Utilisateur débanni");
        }
        $this->redirect('/admin/user');
    }

    /**
     * Renvoie pour la requete Ajax les stocks fournisseurs rechercher par SKU ( LIKE )
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function postSearchUser()
    {
        $UserModel = model('App\Models\UserModel');

        // Paramètres de pagination et de recherche envoyés par DataTables
        $draw        = $this->request->getPost('draw');
        $start       = $this->request->getPost('start');
        $length      = $this->request->getPost('length');
        $searchValue = $this->request->getPost('search')['value'];

        // Obtenez les informations sur le tri envoyées par DataTables
        $orderColumnIndex = $this->request->getPost('order')[0]['column'] ?? 0;
        $orderDirection = $this->request->getPost('order')[0]['dir'] ?? 'asc';
        $orderColumnName = $this->request->getPost('columns')[$orderColumnIndex]['data'] ?? 'id';

        // Obtenez les données triées et filtrées
        $data = $UserModel->getPaginatedUser($start, $length, $searchValue, $orderColumnName, $orderDirection);

        // Obtenez le nombre total de lignes sans filtre
        $totalRecords = $UserModel->getTotalUser();

        // Obtenez le nombre total de lignes filtrées pour la recherche
        $filteredRecords = $UserModel->getFilteredUser($searchValue);

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
