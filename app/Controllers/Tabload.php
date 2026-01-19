<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Tabload extends BaseController
{
    public function getIndex()
    {
        return $this->view('front/tabload/index', [], false);
    }
}
