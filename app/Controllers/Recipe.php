<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\RecipeModel;
use App\Models\StockItemModel;
use App\Services\StockService;

class Recipe extends BaseController
{
    protected $recipeModel;
    protected $stockItemModel;
    protected $stockService;

    public function __construct()
    {
        $this->recipeModel = new RecipeModel();
        $this->stockItemModel = new StockItemModel();
        $this->stockService = new StockService();
    }

    public function getIndex()
    {
        $recipes = $this->recipeModel->findAll();
        return $this->view('front/recipes/index', ['recipes' => $recipes], true);
    }

    public function getShow($id = null)
    {
        if ($id === null || ! is_numeric($id)) {
            return redirect()->to('/recipe')->with('error', 'Recette invalide.');
        }

        $recipe = $this->recipeModel->find($id);
        if (! $recipe) {
            return redirect()->to('/recipe')->with('error', 'Recette introuvable.');
        }

        $rolesJson   = json_decode($recipe['roles'], true) ?: [];
        $ingredients = [];
        foreach ($rolesJson as $roleName => $stockItemId) {
            $item = $this->stockItemModel->find($stockItemId);
            if ($item) {
                $ingredients[] = [
                    'role' => $roleName,
                    'item' => $item
                ];
            }
        }

        return $this->view('front/recipes/show', [
            'recipe'      => $recipe,
            'ingredients' => $ingredients,
        ], true);
    }

    public function postPay($id)
    {
        $recipe = $this->recipeModel->find($id);
        if (! $recipe) {
            return redirect()->to('/recipe')->with('error','Recette introuvable');
        }

        $roles   = json_decode($recipe['roles'], true);
        $dosages = json_decode($recipe['dosages'], true);

        foreach ($roles as $role => $stockId) {
            if ($role === 'fiole') {
                $this->stockService->decrementStockByUnit($stockId, 1);
            } else {
                $mlToDeduct = $dosages[$role] ?? 0;
                $this->stockService->decrementStockByMl($stockId, $mlToDeduct);
            }
        }

        return redirect()->to('/recipe/show/'.$id)->with('success', 'Stock décrémenté et commande validée !');
    }
}
