<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageForbiddenException;
use CodeIgniter\Exceptions\PageNotFoundException;

class Profile extends BaseController
{
    protected $require_auth = true;

    public function getIndex(int $id)
    {
        $current = session()->get('user');
        if (! $current) {
            return redirect()->to(site_url('login'));
        }

        $isAdmin = false;
        if (is_object($current)) {
            $isAdmin = ($current->getPermissionSlug && $current->getPermissionSlug() === 'administrateur')
                || (property_exists($current, 'id_permission') && (int)$current->id_permission === 1);
        } elseif (is_array($current)) {
            $isAdmin = (($current['permission_slug'] ?? null) === 'administrateur')
                || ((int)($current['id_permission'] ?? 0) === 1);
        }

        $currentId = is_object($current) ? (int)($current->id ?? 0) : (int)($current['id'] ?? 0);
        if ($currentId !== $id && ! $isAdmin) {
            throw new PageForbiddenException('Accès non autorisé');
        }

        $um  = new UserModel();
        $raw = $um->find($id);
        if (! $raw) {
            throw new PageNotFoundException("Utilisateur #{$id} introuvable");
        }

        $user = $this->toArraySafe($raw);

        $user['name']          = $user['firstname'] . ' ' . $user['lastname'] ?? ($user['username'] ?? '');
        $user['email']         = $user['email']         ?? '';
        $user['id_permission'] = (int)($user['id_permission'] ?? 0);
        $user['avatar']        = $user['avatar']        ?? 'assets/img/default-avatar.png';

        return $this->view('front/profile/index', [
            'user'    => $user,
            'isAdmin' => $isAdmin,
        ], false);
    }

    protected function toArraySafe($raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }
        if (is_object($raw)) {
            if (method_exists($raw, 'toArray')) {
                return $raw->toArray(true);
            }
            return get_object_vars($raw);
        }
        return [];
    }

    public function getMe()
    {
        $user = session()->get('user');
        if (!$user) return redirect()->to(site_url('login'));

        return redirect()->to(site_url('profile/' . (int)$user->id));
    }
}
