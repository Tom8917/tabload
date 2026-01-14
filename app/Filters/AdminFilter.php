<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->has('user')) {
            $session->set('redirect_url', current_url(true)->getPath());
            return redirect()->to(site_url('login'));
        }

        $user = $session->get('user');
        if (!$user || $user->getPermissionSlug() !== 'administrateur') {
            return redirect()->to(site_url('/'));
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}