<?php

namespace App\Controllers;

class Home extends BaseController
{
    protected $require_auth = true;
    protected $requiredPermissions = ['utilisateur'];

    public function getindex(): string
    {
        $um = Model("App\Models\UserModel");
        $infosUser = $um->countUserByPermission();
        return $this->view('/front/dashboard/index.php', ['infosUser' => $infosUser], true);
    }

    public function getforbidden() : string
    {
        return view('/templates/forbidden');
    }
}