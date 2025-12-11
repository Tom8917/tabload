<?php

namespace App\Models;

use CodeIgniter\Model;

class BlacklistModel extends Model
{
    protected $table = 'user_blacklist';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_user', 'created_at'];
    protected $useTimestamps = false;
//
//    public function addToBlacklist($userId)
//    {
//        $data = [
//            'id_user' => $userId,
//            'created_at' => date('Y-m-d H:i:s')
//        ];
//        return $this->insert($data); // Insérer dans la table user_blacklist
//    }
//
//    public function removeFromBlacklist($userId)
//    {
//        return $this->delete(['id_user' => $userId]); // Supprimer de la table user_blacklist
//    }


    public function addToBlacklist($id)
    {
        $builder = $this->builder();
        return $builder->insert([
            'id_user' => $id,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function removeFromBlacklist($id)
    {
        $builder = $this->builder();
        $builder->where('id_user', $id);
        return $builder->delete();

    }

//    public function isBlacklisted($id)
//    {
//        $builder = $this->builder();
//        $builder->where('id_user', $id);
//        return $builder->countAllResults() > 0;
//    }
//
//    public function addToBlacklistuser($userId)
//    {
//        return $this->insert([
//            'id_user' => $userId,
//            'created_at' => date('Y-m-d H:i:s')
//        ]);
//    }
}

