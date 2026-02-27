<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class User extends Entity
{
    protected $attributes = [
        'id' => null,
        'email' => null,
        'password' => null,
        'id_permission' => null,
        'created_at' => null,
        'updated_at' => null,
        'deleted_at' => null,
    ];

    protected $casts = [
        'id' => 'integer',
        'id_permission' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = ['password'];

    public function setPassword(string $password)
    {
        $this->attributes['password'] = password_hash($password, PASSWORD_DEFAULT);
        return $this;
    }

    public function isAdmin(): bool
    {
        return $this->check('administrateur');
    }

    public function getPermissions(): string
    {
        return $this->getPermissionName();
    }

    public function getTeams(): string
    {
        return $this->getTeamName();
    }

    public function isActive(): bool
    {
        return $this->attributes['deleted_at'] === null;
    }

    public function check(string $slug): bool
    {
        $userPermissionSlug = $this->getPermissionSlug();

        return $userPermissionSlug === $slug;
    }

    public function getPermissionName()
    {
        $upm = Model('UserPermissionModel');
        $permission = $upm->find($this->attributes['id_permission']);
        return $permission ? $permission['name'] : null;
    }

    public function getPermissionSlug(): string
    {
        $upm = Model('UserPermissionModel');
        $permission = $upm->find($this->attributes['id_permission']);

        return $permission ? $permission['slug'] : '';
    }

    public function getProfileImage(): string
    {
        $mediaModel = model('MediaModel');

        $media = $mediaModel
            ->select('id, mime_type') // pas de file_path dans DB-only
            ->where('entity_id', (int) $this->attributes['id'])
            ->where('entity_type', 'user')
            ->orderBy('created_at', 'DESC')
            ->first();

        if (!$media || empty($media['id'])) {
            return base_url('/assets/img/avatars/unknow.png');
        }

        $mime = strtolower((string)($media['mime_type'] ?? ''));
        if ($mime === '' || !str_starts_with($mime, 'image/')) {
            return base_url('/assets/img/avatars/unknow.png');
        }

        return site_url('media/file/' . (int)$media['id']) . '?v=' . time();
    }

    static public function permission_levels(): array
    {
        return [
            '' => 'Utilisateur',
            'administrateur' => 'Administrateur',
            'super-admininistrateur' => 'Super Administrateur'
        ];
    }
}
