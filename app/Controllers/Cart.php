<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\RecipeModel;
use App\Models\StockItemModel;
use App\Services\StockService;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class Cart extends BaseController
{
    private function getUserCartKey(): string
    {
        $user = session()->get('user');
        return $user ? 'cart_user_' . $user->id : 'cart_guest';
    }

    public function getIndex()
    {
        $cartKey = $this->getUserCartKey();
        $cart = session()->get($cartKey) ?? [];
        return $this->view('front/cart/index', ['items' => $cart], true);
    }

    public function postAdd()
    {
        $post = $this->request->getPost();

        if (!session()->get('user')) {
            return redirect()->to('/login')->with('error', 'Vous devez être connecté pour ajouter une recette au panier.');
        }

        $recipeId = (int) ($post['recipe_id'] ?? 0);
        $qty = (int) ($post['qty'] ?? 1);

        if ($recipeId < 1 || $qty < 1) {
            return redirect()->back()->with('error', 'Recette invalide ou quantité incorrecte.');
        }

        $recipeModel = new RecipeModel();
        $recipe = $recipeModel->find($recipeId);
        if (! $recipe) {
            return redirect()->back()->with('error', 'Recette introuvable.');
        }

        $cartKey = $this->getUserCartKey();
        $cart = session()->get($cartKey) ?? [];

        if (isset($cart[$recipeId])) {
            $cart[$recipeId]['qty'] += $qty;
        } else {
            $cart[$recipeId] = [
                'id'    => $recipeId,
                'name'  => $recipe['name'],
                'price' => $recipe['price'],
                'qty'   => $qty,
            ];
        }

        session()->set($cartKey, $cart);

        return redirect()->to('/cart')->with('success', 'Recette ajoutée au panier.');
    }

    public function postRemove($id)
    {
        $cartKey = $this->getUserCartKey();
        $cart = session()->get($cartKey) ?? [];

        unset($cart[$id]);
        session()->set($cartKey, $cart);

        return redirect()->to('/cart')->with('success', 'Recette retirée du panier.');
    }

    public function getClear()
    {
        $cartKey = $this->getUserCartKey();
        session()->remove($cartKey);

        return redirect()->to('/cart')->with('success', 'Panier vidé.');
    }

    public function postCheckout()
    {
        require_once APPPATH . '../vendor/autoload.php';
        Stripe::setApiKey(env('stripe.secret_key'));

        $cartKey = $this->getUserCartKey();
        $cart = session()->get($cartKey) ?? [];
        if (empty($cart)) {
            return redirect()->to('/cart')->with('error', 'Panier vide.');
        }

        $model = new RecipeModel();
        $lineItems = [];

        foreach ($cart as $recipeId => $item) {
            $recipe = $model->find($recipeId);
            if ($recipe) {
                $lineItems[] = [
                    'price_data' => [
                        'currency'     => 'eur',
                        'unit_amount'  => round($recipe['price'] * 100),
                        'product_data' => ['name' => $recipe['name']],
                    ],
                    'quantity' => $item['qty'],
                ];
            }
        }

        if (empty($lineItems)) {
            return redirect()->to('/cart')->with('error', 'Erreur dans les articles du panier.');
        }

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'success_url'          => base_url('/cart/success'),
            'cancel_url'           => base_url('/cart/cancel'),
        ]);

        return redirect()->to($session->url);
    }

    public function getSuccess()
    {
        $cartKey = $this->getUserCartKey(); // clé de session propre à l'utilisateur
        $cart = session()->get($cartKey) ?? [];

        if (empty($cart)) {
            return redirect()->to('/')->with('error', 'Panier introuvable.');
        }

        $recipeModel     = new RecipeModel();
        $stockService    = new StockService();
        $orderModel      = new \App\Models\OrderModel();
        $orderItemModel  = new \App\Models\OrderItemModel();

        $user = session()->get('user');
        $userId = $user?->id ?? null;

        $totalAmount = 0;

        // 1. Calcul du total général
        foreach ($cart as $item) {
            $totalAmount += $item['price'] * $item['qty'];
        }

        // 2. Création de la commande
        $orderId = $orderModel->insert([
            'user_id'      => $userId,
            'total_amount' => $totalAmount,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        // 3. Traitement et décrémentation
        foreach ($cart as $recipeId => $item) {
            $qty = $item['qty'];
            $recipe = $recipeModel->findDecoded($recipeId);
            if (!$recipe) continue;

            $roles   = $recipe['roles'] ?? [];
            $dosages = $recipe['dosages'] ?? [];

            foreach ($roles as $role => $stockItemId) {
                if ($role === 'fiole') {
                    // décrémenter 1 fiole par unité de recette
                    $stockService->decrementStockByUnit($stockItemId, $qty);

                    // --- Cas fiole : en unité, donc quantité_ml = 0
                    $orderItemModel->insert([
                        'order_id'      => $orderId,
                        'stock_item_id' => $stockItemId,
                        'quantity_ml'   => 0, // ✅ plus de NULL ici
                        'unit_price'    => 0, // tu peux adapter si tu veux stocker un prix réel
                        'created_at'    => date('Y-m-d H:i:s'),
                    ]);
                } else {
                    $mlTotal = ($dosages[$role] ?? 0.0) * $qty;
                    $stockService->decrementStockByMl($stockItemId, $mlTotal);

                    // --- Cas ingrédients liquides : en ml
                    $orderItemModel->insert([
                        'order_id'      => $orderId,
                        'stock_item_id' => $stockItemId,
                        'quantity_ml'   => $mlTotal,
                        'unit_price'    => 0,
                        'created_at'    => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        // 4. Vider le panier de session
        session()->remove($cartKey);

        return $this->view('front/stripe/success');
    }

    public function getCancel()
    {
        return $this->view('front/stripe/cancel');
    }
}
