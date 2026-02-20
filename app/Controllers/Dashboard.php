<?php

namespace App\Controllers;

use App\Models\EventModel;
use App\Models\CoursModel;

class Dashboard extends BaseController
{
    protected $title = 'Dashboard';

    public function getIndex(): string
    {
        return $this->view('front/dashboard/index', [

        ], false);
    }
}
