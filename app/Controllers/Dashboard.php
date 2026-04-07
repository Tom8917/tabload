<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    protected $title = 'Dashboard';

    public function getIndex(): string
    {
        return $this->view('front/dashboard/index', [

        ], false);
    }
}
