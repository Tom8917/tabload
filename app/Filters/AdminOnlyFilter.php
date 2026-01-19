<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminOnlyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = session()->get('user');

        // pas connecté -> dehors
        if (!$user) {
            return redirect()->to('/login');
        }

        // pas admin -> dehors
        if ((int)($user->id_permission ?? 0) !== 1) {
            return redirect()->to('/')->with('error', "Accès admin refusé.");
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // nothing
    }
}
