<?php

namespace App\Controllers;

use App\Entities\User;

class Login extends BaseController
{
    protected $require_auth = false;

    public function getindex(): string
    {
        $um = Model("UserModel");
        $bm = Model("BlacklistModel");
        $jm = Model("JobModel");
        return view('/login/login');
    }

    public function postindex()
    {
        // Traitement de la connexion
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // Logique de vérification des informations d'identification
        $um = Model('UserModel');
        $user = $um->verifyLogin($email, $password);

        if ($user) {
            $user = new User($user);

            // Vérifie si l'utilisateur est actif
            if (!$user->isActive()) {
                return view('/login/login');
            }

            // Enregistre l'utilisateur dans la session
            $this->session->set('user', $user);
            $this->session->set('special_password', 'admin1234');

            // Redirige selon les permissions de l'utilisateur
            if ($user->getPermissionSlug() === 'administrateur') {
                return $this->redirect('/admin');
            } else {
                return $this->redirect('/Dashboard');
            }
        } else {
            // Gérer l'échec de l'authentification
            return view('/login/login');
        }
    }


    public function getregister() {
        $flashData = session()->getFlashdata('data');

        // Récupérer la liste des métiers depuis le modèle JobModel
        $jm = Model('JobModel');
        $jobs = $jm->findAll(); // Récupérer tous les métiers (ou adaptez la méthode selon votre modèle)

        // Préparer les données à passer à la vue
        $data = [
            'errors' => $flashData['errors'] ?? null,
            'jobs' => $jobs, // Passer la variable $jobs à la vue
        ];

        return view('/login/register', $data);
    }


    public function postregister() {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $job = $this->request->getPost('id_job');
        $firstname = $this->request->getPost('firstname');
        $lastname = $this->request->getPost('lastname');
        $data = ['email' => $email, 'password' => $password, 'id_job' => $job, 'firstname' => $firstname, 'lastname' => $lastname, 'id_permission' => 3];
        $um = Model('UserModel');
        if (!$um->createUser($data)) {
            $errors = $um->errors();
            $data = ['errors' => $errors];
            return $this->redirect("/login/register", $data);
        }
        return $this->redirect("/login");
    }

    public function getlogout() {
        $this->logout();
        return $this->redirect("/login");
    }
}
