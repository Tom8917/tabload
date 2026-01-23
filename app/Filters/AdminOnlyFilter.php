<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminOnlyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->has('user')) {
            $session->set('redirect_url', current_url(true)->getPath());
            return redirect()->to(site_url('login'));
        }

        $user = $session->get('user');

        if ((int)($user->id_permission ?? 0) !== 1) {
            return redirect()
                ->to(site_url('/'))
                ->with('error', 'Accès réservé aux administrateurs.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
