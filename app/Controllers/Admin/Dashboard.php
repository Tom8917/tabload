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
        $tm = model('App\Models\TaskModel');
        $pm = model('App\Models\PageModel');
        $cm = class_exists('App\Models\CoursModel') ? model('App\Models\CoursModel') : null;
        $em = class_exists('App\Models\EventModel') ? model('App\Models\EventModel') : null;

        $users = $um->findAll();
        $tasks = $tm->findAll();
        $pages = $pm->findAll();

        $stats = [
            'users'  => count($users),
            'tasks'  => count($tasks),
            'pages'  => count($pages),
            'cours'  => $cm ? $cm->countAllResults() : 0,
            'events' => $em ? $em->countAllResults() : 0,
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
