<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageForbiddenException;
use CodeIgniter\Exceptions\PageNotFoundException;

class Profile extends BaseController
{
    protected $require_auth = true;

    // GET /profile/{id}
    public function getIndex(int $id)
    {
        // 1) Session requise
        $current = session()->get('user');
        if (! $current) {
            return redirect()->to(site_url('login'));
        }

        // 2) Autorisation : lui-même OU admin
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

        // 3) Charger l’utilisateur
        $um  = new UserModel();
        $raw = $um->find($id);
        if (! $raw) {
            throw new PageNotFoundException("Utilisateur #{$id} introuvable");
        }

        // 4) Normaliser en array pour la vue (peu importe returnType)
        $user = $this->toArraySafe($raw);

        // 5) Hydrater des valeurs par défaut utiles
        $user['name']          = $user['name']          ?? ($user['username'] ?? '');
        $user['email']         = $user['email']         ?? '';
        $user['id_permission'] = (int)($user['id_permission'] ?? 0);
        $user['avatar']        = $user['avatar']        ?? 'assets/img/default-avatar.png';

        return $this->view('front/profile/index', [
            'user'    => $user,     // toujours un array ici
            'isAdmin' => $isAdmin,
        ], true);
    }

    /**
     * Convertit Entity|object|array en array “safe”
     */
    protected function toArraySafe($raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }
        if (is_object($raw)) {
            // Entity ci4 ?
            if (method_exists($raw, 'toArray')) {
                return $raw->toArray(true);
            }
            // stdClass / objet simple
            return get_object_vars($raw);
        }
        return [];
    }
}
