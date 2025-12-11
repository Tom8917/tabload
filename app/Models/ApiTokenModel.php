<?php

namespace App\Models;

use CodeIgniter\Model;

class ApiTokenModel extends Model
{
    protected $table = 'api_tokens';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_user', 'token', 'counter', 'created_at', 'expires_at'];

    public function decrementCounter($userId)
    {
        $this->where('id_user', $userId)->set('counter', 'counter - 1', false)->update();
    }

    public function updateAllCounters($newCounter)
    {
        if ($newCounter === null) {
            return $this->set(['counter' => null])->where('id >', 0)->update();
        }
        return $this->set(['counter' => $newCounter])->where('id >', 0)->update();
    }

    public function getToken($token)
    {
        return $this->where('token', $token)
            ->where('expires_at >=', date('Y-m-d H:i:s')) // Token encore valide
            ->first();
    }

    public function getAllTokens()
    {
        return $this->findAll();
    }

    public function deleteToken($id)
    {
        return $this->where('id', $id)->delete();
    }

    public function updateLimit($id, $limit)
    {
        return $this->set('counter', $limit)->where('id', $id)->update();
    }

    public function updateAllTokensLimit($limit)
    {
        return $this->db->table($this->table)->update(['counter' => $limit]);
    }

    public function getPaginatedToken($start, $length, $searchValue, $orderColumnName, $orderDirection)
    {
        $builder = $this->builder();
        $builder->join('user', 'api_tokens.id_user = user.id', 'left');
        $builder->select('api_tokens.id, api_tokens.id_user, api_tokens.token, api_tokens.counter');

        // Recherche
        if (!empty($searchValue)) {
            $builder->like('api_tokens.token', $searchValue);
            $builder->orLike('api_tokens.id_user', $searchValue);
        }

        // Tri
        if ($orderColumnName && $orderDirection) {
            $builder->orderBy($orderColumnName, $orderDirection);
        }

        $builder->limit($length, $start);
        return $builder->get()->getResultArray();
    }


    public function getTotalToken()
    {
        return $this->countAllResults();
    }

    public function getFilteredToken($searchValue)
    {
        $builder = $this->builder();
        $builder->join('user', 'api_tokens.id_user = user.id', 'left');
        $builder->select('api_tokens.id');

        if (!empty($searchValue)) {
            $builder->like('api_tokens.id_user', $searchValue);
            $builder->orLike('api_tokens.token', $searchValue);
        }

        return $builder->countAllResults();
    }

    public function resetCounter($tokenId, $value = 100)
    {
        $this->set('counter', $value)->where('id', $tokenId)->update();
    }
}