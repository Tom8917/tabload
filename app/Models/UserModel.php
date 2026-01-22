<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'id';

    // Champs permis pour les opérations d'insertion et de mise à jour
    protected $allowedFields = ['firstname', 'lastname', 'email', 'password', 'id_permission', 'counter_user', 'id_api_tokens', 'created_at', 'updated_at', 'deleted_at'];

    // Activer le soft delete
    protected $useSoftDeletes = true;

    // Champs de gestion des dates
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
//        'username' => 'required|is_unique[user.username,id,{id}]|min_length[3]|max_length[100]',
        'email'    => 'required|valid_email|is_unique[user.email,id,{id}]',
        'password' => 'required|min_length[8]',
        'id_permission' => 'required|is_natural_no_zero',
    ];

    protected $validationMessages = [
//        'username' => [
//            'required'   => 'Le nom d\'utilisateur est requis.',
//            'min_length' => 'Le nom d\'utilisateur doit comporter au moins 3 caractères.',
//            'max_length' => 'Le nom d\'utilisateur ne doit pas dépasser 100 caractères.',
//            'is_unique'   => 'Ce nom d\'utilisateur est déja utilisé.',
//        ],
        'email' => [
            'required'   => 'L\'email est requis.',
            'valid_email' => 'L\'email doit être valide.',
            'is_unique'   => 'Cet email est déjà utilisé.',
        ],
        'password' => [
            'required'   => 'Le mot de passe est requis.',
            'min_length' => 'Le mot de passe doit comporter au moins 8 caractères.',
        ],
        'id_permission' => [
            'required'          => 'La permission est requise.',
            'is_natural_no_zero' => 'La permission doit être un entier positif.',
        ],
    ];

    // Callbacks pour le hachage du mot de passe
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];


    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }


    // Relations avec les permissions
    public function getPermissions()
    {
        return $this->join('user_permission', 'user.id_permission = user_permission.id')
            ->select('user.*, user_permission.name as permission_name')
            ->findAll();
    }

    public function getUserById($id)
    {
        $this->select('user.*, media.file_path as avatar_url, user_blacklist.id_user as blacklistid_user');
        $this->join('media', 'user.id = media.entity_id AND media.entity_type = "user"', 'left');
        $this->join('user_blacklist', 'user.id = user_blacklist.id_user', 'left');

        return $this->find($id);
    }
    public function getAllUsers()
    {
        return $this->findAll();
    }

    public function createUser($data)
    {
        return $this->insert($data);
    }
    public function updateUser($id, $data)
    {
        $builder = $this->builder();

        if (isset($data['password'])) {
            if ($data['password'] == '') {
                unset($data['password']);
            } else {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
        }
        $builder->where('id', $id);
        return $builder->update($data);
    }

    public function countUserByPermission() {
        $builder = $this->db->table('user U');
        $builder->select('UP.name, count(U.id) as count');
        $builder->join('user_permission UP', 'U.id_permission = UP.id');
        $builder->groupBy('U.id_permission');
        return $builder->get()->getResultArray();
    }

//actif
    public function activateUser($id) {
        $builder = $this->builder();
        $builder->set('deleted_at', NULL); // Efface la valeur de soft delete
        $builder->where('id', $id);
        return $builder->update();
    }

    public function deactivateUser($id) {
        $builder = $this->builder();
        $builder->set('deleted_at', date('Y-m-d H:i:s')); // Met à jour le champ `deleted_at` avec la date/heure actuelle
        $builder->where('id', $id);
        return $builder->update();
    }


    public function deleteUser($id) {
        return $this->builder()
            ->where('id', $id)
            ->delete(); // Suppression définitive
    }


    public function verifyLogin($email, $password)
    {
        // Rechercher l'utilisateur par email
        $user = $this->withDeleted()->where('email', $email)->first();

        // Si l'utilisateur existe, vérifier le mot de passe
        if ($user && password_verify($password, $user['password'])) {
            // Le mot de passe est correct, retourner les informations de l'utilisateur
            return $user;
        }

        // Si l'utilisateur n'existe pas ou si le mot de passe est incorrect, retourner false
        return false;
    }

    public function getPaginatedUser($start, $length, $searchValue, $orderColumnName, $orderDirection)
    {
        $builder = $this->builder();
        $builder->join('user_permission', 'user.id_permission = user_permission.id', 'left');
        $builder->join('media', 'user.id = media.entity_id AND media.entity_type = "user"', 'left');
        $builder->select('user.*, user_permission.name as permission_name, media.file_path as avatar_url');

        // Recherche
        if ($searchValue != null) {
            $builder->like('firstname', $searchValue);
            $builder->orLike('email', $searchValue);
            $builder->orLike('user_permission.name', $searchValue);
        }

        // Tri
        if ($orderColumnName && $orderDirection) {
            $builder->orderBy($orderColumnName, $orderDirection);
        }
        $builder->limit($length, $start);
        return $builder->get()->getResultArray();
    }

    public function getTotalUser()
    {
        $builder = $this->builder();
        return $builder->countAllResults();
    }


    public function getFilteredUser($searchValue)
    {
        $builder = $this->builder();

        // Ajout des jointures pour récupérer les données des autres tables
        $builder->join('user_permission', 'user.id_permission = user_permission.id', 'left');
        $builder->join('media', 'user.id = media.entity_id AND media.entity_type = "user"', 'left');
        $builder->join('user_blacklist', 'user.id = user_blacklist.id_user', 'left');
        $builder->select('user.*, user_permission.name as permission_name, media.file_path as avatar_url');

        // @phpstan-ignore-next-line
        if (! empty($searchValue)) {
            // Recherche sur plusieurs colonnes
            $builder->like('firstname', $searchValue);
            $builder->orLike('lastname', $searchValue);
            $builder->orLike('email', $searchValue);
            $builder->orLike('user_permission.name', $searchValue);
        }

        // Retourne le nombre d'enregistrements filtrés
        return $builder->countAllResults();
    }

    public function getAllEmails()
    {
        return $this->select('email')->findAll();
    }




    public function decrementCounterUser($userId)
    {
        $this->where('id', $userId)->set('counter_user', 'counter_user - 1', false)->update();
    }

    public function resetCounter($email)
    {
        $this->set('counter_user', 3)->where('email', $email)->update();
    }
}
