<?php

namespace App\Models;

use CodeIgniter\Model;

class RecipeIngredientModel extends Model
{
    protected $table            = 'recipe_ingredients';
    protected $primaryKey       = 'id';
    protected $useTimestamps    = true;
    protected $allowedFields    = ['id_recipe', 'id_stock_item', 'quantity_ml'];

    protected $validationRules  = [
        'id_recipe'     => 'required|is_natural_no_zero',
        'id_stock_item' => 'required|is_natural_no_zero',
        'quantity_ml'   => 'required|decimal',
    ];
}
