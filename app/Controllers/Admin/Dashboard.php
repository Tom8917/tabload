<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    protected $title        = 'Dashboard';
    protected $require_auth = true;

    public function getIndex(): string
    {
        $um = model('App\Models\UserModel');
        $rm = model('App\Models\ReportModel');

        $users = $um->findAll();
        $reports = $rm->findAll();

        $stats = [
            'users'  => count($users),
            'reports'  => count($reports),
        ];

        return $this->view('admin/dashboard/index', ['stats' => $stats], true, ['saveData' => true]);
    }

    public function getTest(): void
    {
        $this->error("Oh");
        $this->message("Oh");
        $this->success("Oh");
        $this->warning("Oh");
        $this->error("Oh");
        $this->redirect("/admin");
    }
}
