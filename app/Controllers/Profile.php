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
            $isAdmin =
                (method_exists($current, 'getPermissionSlug') && $current->getPermissionSlug() === 'administrateur')
                || (property_exists($current, 'id_permission') && (int)$current->id_permission === 1);
        } else {
            $isAdmin =
                (($current['permission_slug'] ?? null) === 'administrateur')
                || ((int)($current['id_permission'] ?? 0) === 1);
        }

        $currentId = (int)(is_object($current) ? ($current->id ?? 0) : ($current['id'] ?? 0));
        if ($currentId !== $id && ! $isAdmin) {
            throw new PageForbiddenException('Accès non autorisé');
        }

        /** @var UserModel $um */
        $um = model(UserModel::class);

        $raw = $um->find($id);
        if (! $raw) {
            throw PageNotFoundException::forPageNotFound("Utilisateur #{$id} introuvable");
        }

        $userEntity = $raw;
        if (! is_object($userEntity)) {
            $userEntity = new \App\Entities\User($raw);
        }

        $user = $this->toArraySafe($raw);

        $first = trim((string)($user['firstname'] ?? ''));
        $last  = trim((string)($user['lastname'] ?? ''));
        $full  = trim($first . ' ' . $last);

        $user['id']           = (int)($user['id'] ?? $id);
        $user['email']        = (string)($user['email'] ?? '');
        $user['id_permission']= (int)($user['id_permission'] ?? 0);
        $user['name']         = $full !== '' ? $full : (string)($user['username'] ?? ('Utilisateur #'.$user['id']));

        return $this->view('front/profile/index', [
            'title'      => 'Mon profil',
            'user'       => $user,
            'userEntity' => $userEntity,
            'isAdmin'    => $isAdmin,
        ], false);
    }

    protected function toArraySafe($raw): array
    {
        if (is_array($raw)) return $raw;

        if (is_object($raw)) {
            if (method_exists($raw, 'toArray')) return $raw->toArray(true);
            return get_object_vars($raw);
        }

        return [];
    }

    public function getMe()
    {
        $current = session()->get('user');
        if (! $current) {
            return redirect()->to(site_url('login'));
        }

        $currentId = (int)(is_object($current) ? ($current->id ?? 0) : ($current['id'] ?? 0));
        return redirect()->to(site_url('profile/' . $currentId));
    }
}