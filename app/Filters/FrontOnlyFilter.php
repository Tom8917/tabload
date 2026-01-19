<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class FrontOnlyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = session()->get('user');

        // Si ton filter "auth" est déjà là, ce cas arrivera rarement,
        // mais on sécurise quand même.
        if (!$user) {
            return redirect()->to('/login');
        }

        // Admin => interdit en front
        if ((int)($user->id_permission ?? 0) === 1) {
            return redirect()->to('/admin')->with('error', "Section réservée aux utilisateurs (non-admin).");
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
