<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Tabload extends BaseController
{
    public function getIndex()
    {
        return $this->view('admin/tabload/index', [], true);
    }
}
