<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class User extends Entity
{
    protected $attributes = [
        'id' => null,
        'email' => null,
        'password' => null,
        'sessionPassword' => null,
        'uegarPassword' => null,
        'id_permission' => null,
        'id_material' => null,
        'id_team' => null,
        'created_at' => null,
        'updated_at' => null,
        'deleted_at' => null,
    ];

    protected $casts = [
        'id' => 'integer',
        'id_permission' => 'integer',
        'id_team' => 'integer',
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
            ->where('entity_id', (int) $this->attributes['id'])
            ->where('entity_type', 'user')
            ->orderBy('created_at', 'DESC')
            ->first();

        return $media ? $media['file_path'] . '?v=' . time() : '/assets/img/avatars/unknow.png';
    }

    static public function permission_levels(): array
    {
        return [
            '' => 'Utilisateur',
            'administrateur' => 'Administrateur',        // Utilisez des slugs au lieu de noms
            'super-admininistrateur' => 'Super Administrateur'
        ];
    }
}
