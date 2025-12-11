<?php

namespace App\Models;

use CodeIgniter\Model;

class RecipeModel extends Model
{
    protected $table            = 'recipes';
    protected $primaryKey       = 'id';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'name','description','roles','image','volume_ml','cost','price','dosages'
    ];

    public function findDecoded($id)
    {
        $recipe = $this->find($id);
        if (!$recipe) return null;
        $recipe['roles'] = json_decode($recipe['roles'], true);
        $recipe['dosages'] = json_decode($recipe['dosages'], true);
        return $recipe;
    }
}
