<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\{RecipeModel, StockItemModel, StockRoleModel};
use App\Services\StockService;

class Recipe extends BaseController
{
    protected $stockService;

    public function __construct()
    {
        $this->stockService = new StockService();
    }

    public function getIndex()
    {
        $model = new RecipeModel();
        $recipes = $model->findAll();

        return $this->view('admin/recipes/index', ['recipes' => $recipes], true);
    }

    public function getNew()
    {
        $roles = (new StockRoleModel())->findAll();
        $itemsByRole = $this->getAllStockItemsGroupedByRole();

        return $this->view('admin/recipes/form', [
            'roles'       => $roles,
            'itemsByRole' => $itemsByRole,
            'recipe'      => null,
            'dosages'     => [],
        ], true);
    }

    public function getEdit($id)
    {
        $model = new RecipeModel();
        $recipe = $model->findDecoded($id);
        if (!$recipe) return redirect()->to('/admin/recipe')->with('error', 'Recette introuvable.');

        $roles = (new StockRoleModel())->findAll();
        $itemsByRole = $this->getAllStockItemsGroupedByRole();

        return $this->view('admin/recipes/form', [
            'roles'       => $roles,
            'itemsByRole' => $itemsByRole,
            'recipe'      => $recipe,
            'selected'    => $recipe['roles'] ?? [],
            'dosages'     => $recipe['dosages'] ?? [],
        ], true);
    }

    public function postCreate()
    {
        $this->handleSave();
        return redirect()->to('/admin/recipe')->with('success', 'Recette enregistrée');
    }

    public function postUpdate()
    {
        $this->handleSave(true);
        return redirect()->to('/admin/recipe')->with('success', 'Recette modifiée');
    }

    private function handleSave(bool $isUpdate = false): void
    {
        $post = $this->request->getPost();
        $roles = (new StockRoleModel())->findAll();

        // Récupération des rôles
        $roleData = [];
        foreach ($roles as $role) {
            $key = $role['name'];
            if (!empty($post["role_id"]["$key"])) {
                $roleData[$key] = (int)$post["role_id"]["$key"];
            }
        }

        // Volume de la fiole
        $volume = $this->getFioleVolume($roleData['fiole'] ?? null);

        // Calcul initial des dosages
        $dosages = [
            'base'        => round($volume * 0.75, 4),
            'concentrate' => round($volume * 0.25, 4),
            'nicotine'    => 0.0000,
        ];

        // Taux de nicotine (mg/ml) saisi via <select>
        $nicotineTarget = $this->request->getPost('nicotine_target');
        $nicotineTarget = is_numeric($nicotineTarget) ? (float)$nicotineTarget : 0;

        // Si besoin, on calcule le volume de nicotine et on l’applique
        if ($nicotineTarget > 0) {
            $nicotineMl = round(($volume * $nicotineTarget) / 200, 4);
            $dosages['nicotine'] = $nicotineMl;
            $dosages['concentrate'] = round(max(0, $dosages['concentrate'] - $nicotineMl), 4);
        }

        $totalCost = $this->stockService->calculateCost($roleData, $dosages, $volume);

        // Données à enregistrer
        $data = [
            'name'        => $post['name'],
            'description' => $post['description'] ?? null,
            'roles'       => json_encode($roleData, JSON_UNESCAPED_UNICODE),
            'dosages'     => json_encode($dosages, JSON_UNESCAPED_UNICODE),
            'volume_ml'   => $volume,
            'nicotine'    => $nicotineTarget,
            'cost'        => $totalCost,
            'price'       => isset($post['price']) ? (float)$post['price'] : 0.0,
        ];

        // Upload de l’image si présente
        $image = $this->request->getFile('image');
        if ($image && $image->isValid() && !$image->hasMoved()) {
            $newName = $image->getRandomName();
            $image->move(FCPATH . 'uploads/recipes', $newName);
            $data['image'] = $newName;
        }

        // Insertion ou mise à jour
        $model = new RecipeModel();
        if ($isUpdate) {
            $data['id'] = (int)$post['id'];
            $model->update($data['id'], $data);
        } else {
            $model->insert($data);
        }
    }

    private function getFioleVolume(?int $fioleProductId): int
    {
        if (!$fioleProductId) return 100;

        $db = \Config\Database::connect();
        $row = $db->table('stock_items')
            ->select('stock_products.unit_volume_ml')
            ->join('stock_products', 'stock_products.id = stock_items.id_stock_product')
            ->where('stock_items.id', $fioleProductId)
            ->get()
            ->getRow();

        return $row ? (int) $row->unit_volume_ml : 100;
    }

    private function getAllStockItemsGroupedByRole(): array
    {
        $stockItemModel = new StockItemModel();

        $items = $stockItemModel
            ->select('
            stock_items.id,
            stock_items.name,
            stock_roles.name as role_name,
            stock_products.unit_volume_ml
        ')
            ->join('stock_products', 'stock_products.id = stock_items.id_stock_product')
            ->join('stock_types', 'stock_types.id = stock_products.id_stock_type')
            ->join('stock_type_roles', 'stock_type_roles.id_stock_type = stock_types.id')
            ->join('stock_roles', 'stock_roles.id = stock_type_roles.id_stock_role')
            ->where('stock_items.deleted_at IS NULL', null, false)
            ->orderBy('stock_items.name', 'ASC')
            ->findAll();

        $grouped = [];
        foreach ($items as $item) {
            $role = $item['role_name'];
            if (!isset($grouped[$role])) {
                $grouped[$role] = [];
            }

            $grouped[$role][] = [
                'id'             => $item['id'],
                'name'           => $item['name'],
                'unit_volume_ml' => (float)($item['unit_volume_ml'] ?? 0),
            ];
        }

        return $grouped;
    }

    public function findDecoded($id)
    {
        $recipe = $this->find($id);

        if (!$recipe) {
            return null;
        }

        $recipe['roles'] = json_decode($recipe['roles'] ?? '{}', true);
        $recipe['dosages'] = json_decode($recipe['dosages'] ?? '{}', true);
        $recipe['nicotine'] = isset($recipe['nicotine']) ? (float)$recipe['nicotine'] : 0;

        return $recipe;
    }
}
