<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ApiTokenModel;

class TokenController extends BaseController
{
    public function index($token = null)
    {
        if (!$token) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Token is required']);
        }

        $model = new ApiTokenModel();
        $result = $model->getToken($token);

        if (!$result) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Token not found']);
        }

        return $this->response->setJSON($result);
    }
}