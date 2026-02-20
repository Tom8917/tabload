<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Home extends BaseController
{
    protected $title      = 'Tableau de Bord';
    protected $require_auth = true;
    protected $requiredPermissions = ['administrateur'];

    public function getIndex(): string
    {
        $um = model('App\Models\UserModel');

        $users = $um->findAll();

        $stats = [
            'users'  => count($users),
        ];

        return $this->view('admin/dashboard/index', ['stats' => $stats], true, ['saveData' => true]);
    }

    public function getforbidden() : string
    {
        return view('/templates/forbidden');
    }
}