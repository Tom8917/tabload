<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Job extends BaseController
{
    protected $require_auth = true;
    protected $requiredPermissions = ['administrateur'];
    public function getindex($id = null) {
        $jm = new \App\Models\JobModel(); // Instancier le modèle

        if ($id == null) {
            return $this->view('/admin/user/index-job', [], true);
        } else {
            $jm = Model("/JobModel");
            if ($id == "new") {
                return $this->view('/admin/user/user-job', [], true);
            }
            $job = $jm->getJobById($id);
            return $this->view('/admin/user/user-Job', ["job" => $job], true);
        }

        // Transmettre toujours $jobs à la vue
        return $this->view('/admin/user/index-job', ['jobs' => $jobs ?? []], true);
    }

    public function postupdate() {
        $data = $this->request->getPost();
        $jm = Model("/JobModel");
        if ($jm->updateJob($data['id'], $data)) {
            $this->success("Métier a bien été modifié");
        } else {
            $this->error("Une erreur est survenue");
        }
        $this->redirect("/admin/job");
    }

    public function postcreate() {
        $data = $this->request->getPost();
        $jm = Model("JobModel");
        if ($jm->createJob($data)) {
            $this->success("Le Métier à bien été ajouté.");
            $this->redirect("/admin/job");
        } else {
            $errors = $jm->errors();
            foreach ($errors as $error) {
                $this->error($error);
            }
            $this->redirect("/admin/job/new");
        }
    }

    public function getdelete($id){
        $jm = Model('JobModel');
        if ($jm->deleteJob($id)) {
            $this->success("Métier supprimé");
        } else {
            $this->error("Métier non supprimé");
        }
        $this->redirect('/admin/job');
    }

    public function postSearchJob()
    {
        $UserModel = model('App\Models\JobModel');

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
        $data = $UserModel->getPaginatedJob($start, $length, $searchValue, $orderColumnName, $orderDirection);

        // Obtenez le nombre total de lignes sans filtre
        $totalRecords = $UserModel->getTotalJob();

        // Obtenez le nombre total de lignes filtrées pour la recherche
        $filteredRecords = $UserModel->getFilteredJob($searchValue);

        $result = [
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $data,
        ];
        return $this->response->setJSON($result);
    }
}