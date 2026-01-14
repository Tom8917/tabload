<?php

namespace App\Controllers;

use App\Entities\User;

class Login extends BaseController
{
    protected $require_auth = false;

    // GET /login
    public function getIndex(): string
    {
        return view('/login/login');
    }

    // POST /login
    public function postLogin()
    {
        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $um   = model('UserModel');
        $user = $um->verifyLogin($email, $password);

        if (!$user) {
            return view('/login/login');
        }

        $user = new User($user);

        if (!$user->isActive()) {
            return view('/login/login');
        }

        $this->session->set('user', $user);
        $this->session->set('special_password', 'admin1234');

        if ($user->getPermissionSlug() === 'administrateur') {
            return $this->redirect('/admin');
        }

        return $this->redirect('/');
    }

    // GET /login/register
    public function getRegister()
    {
        $flashData = session()->getFlashdata('data');

        return view('/login/register', [
            'errors' => $flashData['errors'] ?? null,
        ]);
    }

    // POST /login/register
    public function postRegister()
    {
        $data = [
            'email'         => $this->request->getPost('email'),
            'password'      => $this->request->getPost('password'),
            'id_job'        => $this->request->getPost('id_job'),
            'firstname'     => $this->request->getPost('firstname'),
            'lastname'      => $this->request->getPost('lastname'),
            'id_permission' => 3,
        ];

        $um = model('UserModel');

        if (!$um->createUser($data)) {
            return $this->redirect('/login/register', [
                'errors' => $um->errors(),
            ]);
        }

        return $this->redirect('/login');
    }

    // GET /logout
    public function getLogout()
    {
        $this->session->remove('user');
        return $this->redirect('/login');
    }
}
