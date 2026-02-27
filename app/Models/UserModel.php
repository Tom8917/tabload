<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'user';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'firstname',
        'lastname',
        'email',
        'password',
        'id_permission',
        'counter_user',
        'id_api_tokens',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $useSoftDeletes = true;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'email'         => 'required|valid_email|is_unique[user.email,id,{id}]',
        'id_permission' => 'required|is_natural_no_zero',
    ];

    protected $validationMessages = [
        'email' => [
            'required'    => 'L\'email est requis.',
            'valid_email' => 'L\'email doit être valide.',
            'is_unique'   => 'Cet email est déjà utilisé.',
        ],
        'id_permission' => [
            'required'           => 'La permission est requise.',
            'is_natural_no_zero' => 'La permission doit être un entier positif.',
        ],
    ];

    protected $beforeInsert = ['hashPasswordIfProvided'];
    protected $beforeUpdate = ['hashPasswordIfProvided'];

    protected function hashPasswordIfProvided(array $data): array
    {
        if (! isset($data['data']) || ! is_array($data['data'])) {
            return $data;
        }

        if (array_key_exists('password', $data['data'])) {
            $pwd = (string) $data['data']['password'];

            if (trim($pwd) === '') {
                unset($data['data']['password']);
                return $data;
            }

            $data['data']['password'] = password_hash($pwd, PASSWORD_DEFAULT);
        }

        return $data;
    }

    public function getPermissions(): array
    {
        return $this->join('user_permission', 'user.id_permission = user_permission.id')
            ->select('user.*, user_permission.name as permission_name')
            ->findAll();
    }

    public function getUserById(int $id): ?array
    {
        $this->select('user.*, user_blacklist.id_user as blacklistid_user');
        $this->join('user_blacklist', 'user.id = user_blacklist.id_user', 'left');

        return $this->find($id) ?: null;
    }

    public function getAllUsers(): array
    {
        return $this->findAll();
    }

    public function createUser(array $data)
    {
        return $this->insert($data);
    }

    public function updateUser(int $id, array $data): bool
    {
        return (bool) $this->update($id, $data);
    }

    public function countUserByPermission(): array
    {
        $builder = $this->db->table('user U');
        $builder->select('UP.name, COUNT(U.id) as count');
        $builder->join('user_permission UP', 'U.id_permission = UP.id');
        $builder->groupBy('U.id_permission');

        return $builder->get()->getResultArray();
    }

    public function activateUser(int $id): bool
    {
        return (bool) $this->builder()
            ->set('deleted_at', null)
            ->where('id', $id)
            ->update();
    }

    public function deactivateUser(int $id): bool
    {
        return (bool) $this->builder()
            ->set('deleted_at', date('Y-m-d H:i:s'))
            ->where('id', $id)
            ->update();
    }

    public function deleteUser(int $id): bool
    {
        return (bool) $this->builder()
            ->where('id', $id)
            ->delete();
    }

    public function verifyLogin(string $email, string $password)
    {
        $user = $this->withDeleted()->where('email', $email)->first();

        if ($user && password_verify($password, (string) $user['password'])) {
            return $user;
        }

        return false;
    }

    public function getPaginatedUser(int $start, int $length, ?string $searchValue, ?string $orderColumnName, ?string $orderDirection): array
    {
        $builder = $this->builder();

        $builder->join('user_permission', 'user.id_permission = user_permission.id', 'left');
        $builder->select('user.*, user_permission.name as permission_name');

        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('user.firstname', $searchValue)
                ->orLike('user.lastname', $searchValue)
                ->orLike('user.email', $searchValue)
                ->orLike('user_permission.name', $searchValue)
                ->groupEnd();
        }

        // whitelist orderBy (évite avatar_url / colonnes inexistantes)
        $allowed = [
            'id'              => 'user.id',
            'firstname'       => 'user.firstname',
            'lastname'        => 'user.lastname',
            'email'           => 'user.email',
            'permission_name' => 'user_permission.name',
            'deleted_at'      => 'user.deleted_at',
        ];

        $col = $allowed[$orderColumnName ?? 'id'] ?? 'user.id';
        $dir = strtolower((string)$orderDirection) === 'desc' ? 'DESC' : 'ASC';

        $builder->orderBy($col, $dir);

        $builder->limit($length, $start);
        return $builder->get()->getResultArray();
    }

    public function getTotalUser(): int
    {
        return $this->builder()->countAllResults();
    }

    public function getFilteredUser(?string $searchValue): int
    {
        $builder = $this->builder();

        $builder->join('user_permission', 'user.id_permission = user_permission.id', 'left');

        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('user.firstname', $searchValue)
                ->orLike('user.lastname', $searchValue)
                ->orLike('user.email', $searchValue)
                ->orLike('user_permission.name', $searchValue)
                ->groupEnd();
        }

        return (int) $builder->countAllResults();
    }

    public function getAllEmails(): array
    {
        return $this->select('email')->findAll();
    }

    public function decrementCounterUser(int $userId): void
    {
        $this->where('id', $userId)
            ->set('counter_user', 'counter_user - 1', false)
            ->update();
    }

    public function resetCounter(string $email): void
    {
        $this->set('counter_user', 3)
            ->where('email', $email)
            ->update();
    }
}
