<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\StockItemModel;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\StockMovementModel;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class Order extends BaseController
{
    protected $stockItemModel;
    protected $orderModel;
    protected $orderItemModel;
    protected $stockMovementModel;

    public function __construct()
    {
        $this->stockItemModel     = new StockItemModel();
        $this->orderModel         = new OrderModel();
        $this->orderItemModel     = new OrderItemModel();
        $this->stockMovementModel = new StockMovementModel();
    }

    /**
     * GET /order/new
     * Affiche le formulaire de composition d’e-liquide,
     * en fournissant aussi la liste des fioles vides.
     */
    public function getNew()
    {
        // 1) Récupérer les concentrés (role='concentrate')
        $concentrates = $this->stockItemModel
            ->select('stock_items.*')
            ->join('stock_types',     'stock_types.id = stock_items.id_stock_type')
            ->join('stock_type_roles','stock_type_roles.id_stock_type = stock_types.id')
            ->join('stock_roles',     'stock_roles.id = stock_type_roles.id_stock_role')
            ->where('stock_roles.name','concentrate')
            ->where('stock_items.deleted_at IS NULL', null, false)
            ->findAll();

        // 2) Récupérer les bases (role='base')
        $bases = $this->stockItemModel
            ->select('stock_items.*')
            ->join('stock_types',     'stock_types.id = stock_items.id_stock_type')
            ->join('stock_type_roles','stock_type_roles.id_stock_type = stock_types.id')
            ->join('stock_roles',     'stock_roles.id = stock_type_roles.id_stock_role')
            ->where('stock_roles.name','base')
            ->where('stock_items.deleted_at IS NULL', null, false)
            ->findAll();

        // 3) Récupérer les nicotines (role='nicotine')
        $nicotines = $this->stockItemModel
            ->select('stock_items.*')
            ->join('stock_types',     'stock_types.id = stock_items.id_stock_type')
            ->join('stock_type_roles','stock_type_roles.id_stock_type = stock_types.id')
            ->join('stock_roles',     'stock_roles.id = stock_type_roles.id_stock_role')
            ->where('stock_roles.name','nicotine')
            ->where('stock_items.deleted_at IS NULL', null, false)
            ->findAll();

        // 4) Récupérer les fioles vides (role='fiole')
        $bottles = $this->stockItemModel
            ->select('stock_items.*')
            ->join('stock_types',     'stock_types.id = stock_items.id_stock_type')
            ->join('stock_type_roles','stock_type_roles.id_stock_type = stock_types.id')
            ->join('stock_roles',     'stock_roles.id = stock_type_roles.id_stock_role')
            ->where('stock_roles.name','fiole')
            ->where('stock_items.deleted_at IS NULL', null, false)
            ->findAll();

        return $this->view('front/orders/form', [
            'concentrates'       => $concentrates,
            'bases'              => $bases,
            'nicotines'          => $nicotines,
            'bottles'            => $bottles,
            'defaultVolume'      => 100,
            'defaultNicStrength' => 6,
        ], true);
    }

    /**
     * POST /order/create
     * - Valide le POST
     * - Calcule les volumes d’arôme / base / nicotine
     * - Vérifie le stock (en ml pour les liquides, en unités pour la fiole)
     * - Crée la session Stripe Checkout (en passant tous les IDs et volumes en metadata)
     */
    public function postCreate()
    {
        helper('text');

        $post = $this->request->getPost([
            'concentrate_id','base_id','nicotine_id','bottle_id',
            'volume_total','nic_strength_desired'
        ]);

        // 1) Validation rapide
        $rules = [
            'concentrate_id'       => 'required|integer',
            'base_id'              => 'required|integer',
            'nicotine_id'          => 'required|integer',
            'bottle_id'            => 'required|integer',
            'volume_total'         => 'required|integer|greater_than[0]',
            'nic_strength_desired' => 'required|integer|greater_than_equal_to[0]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()
                ->with('errors', $this->validator->getErrors())
                ->withInput();
        }

        // 2) Charger les stock_items correspondants
        $concentrate = $this->stockItemModel->find((int)$post['concentrate_id']);
        $base        = $this->stockItemModel->find((int)$post['base_id']);
        $nicotine    = $this->stockItemModel->find((int)$post['nicotine_id']);
        $bottle      = $this->stockItemModel->find((int)$post['bottle_id']);

        if (! $concentrate || ! $base || ! $nicotine || ! $bottle) {
            return redirect()->back()
                ->with('error', 'Sélection d’ingrédient ou fiole invalide.')
                ->withInput();
        }

        // 3) Lire les champs “spécifiques”
        $flavorPct    = (float)$concentrate['flavor_percentage'];   // ex. 0.10 = 10 %
        $nicConc      = (float)$nicotine['nic_concentration'];      // ex. 18 (mg/ml)
        $volumeTotal  = (int)$post['volume_total'];                 // ex. 100 ml
        $nicDesir     = (int)$post['nic_strength_desired'];         // ex. 6 mg/ml

        // 4) Calculer les volumes nécessaires (en ml)
        $volumeConcentrate = round($volumeTotal * $flavorPct, 2);
        $volumeNic         = ($nicConc > 0)
            ? round(($nicDesir * $volumeTotal) / $nicConc, 2)
            : 0.00;
        $volumeBase        = round($volumeTotal - $volumeConcentrate - $volumeNic, 2);

        if ($volumeBase < 0) {
            return redirect()->back()
                ->with('error', "Paramètres invalides : pour {$volumeTotal} ml à {$nicDesir} mg, le volume de base devient négatif.")
                ->withInput();
        }

        // 5) Vérifier le stock :
        //    - Concentrate, base, nicotine : en ml
        //    - Bottle : en unités (quantity), il faut au moins 1 fiole
        $errorsStock = [];
        if ((float)$concentrate['quantity'] < $volumeConcentrate) {
            $errorsStock[] = "Stock insuffisant pour le concentré « {$concentrate['name']} » : reste {$concentrate['quantity']} ml, besoin {$volumeConcentrate} ml.";
        }
        if ((float)$nicotine['quantity'] < $volumeNic) {
            $errorsStock[] = "Stock insuffisant pour la nicotine « {$nicotine['name']} » : reste {$nicotine['quantity']} ml, besoin {$volumeNic} ml.";
        }
        if ((float)$base['quantity'] < $volumeBase) {
            $errorsStock[] = "Stock insuffisant pour la base « {$base['name']} » : reste {$base['quantity']} ml, besoin {$volumeBase} ml.";
        }
        if ((float)$bottle['quantity'] < 1) {
            $errorsStock[] = "Plus de fioles vides « {$bottle['name']} » disponibles.";
        }

        if (! empty($errorsStock)) {
            return redirect()->back()
                ->with('error', implode(' ', $errorsStock))
                ->withInput();
        }

        // 6) Créer la session Stripe Checkout (+ passer en metadata tous les IDs + volumes)
        Stripe::setApiKey(env('stripe.secret_key'));

        $lineItems = [[
            'price_data' => [
                'currency'     => 'eur',
                'product_data' => [
                    'name' => "E-liquide {$volumeTotal} ml – {$concentrate['name']}",
                ],
                'unit_amount'  => 0, // gratuit ou calculé plus tard
            ],
            'quantity' => 1,
        ]];

        try {
            $checkoutSession = Session::create([
                'payment_method_types' => ['card'],
                'line_items'           => $lineItems,
                'mode'                 => 'payment',
                'success_url'          => base_url('/order/success?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url'           => base_url('/order/cancel'),
                'metadata' => [
                    'concentrate_id'     => $concentrate['id'],
                    'base_id'            => $base['id'],
                    'nicotine_id'        => $nicotine['id'],
                    'bottle_id'          => $bottle['id'],
                    'volume_total'       => $volumeTotal,
                    'nic_strength'       => $nicDesir,
                    'volume_concentrate' => $volumeConcentrate,
                    'volume_nic'         => $volumeNic,
                    'volume_base'        => $volumeBase,
                ],
            ]);

            return redirect()->to($checkoutSession->url);
        } catch (\Exception $e) {
            return redirect()->to('/order/new')
                ->with('error', 'Erreur Stripe : ' . $e->getMessage());
        }
    }

    /**
     * GET /order/success
     * Stripe redirige ici après paiement validé.
     * On récupère la session, on lit les metadata et on décrémente tout automatiquement.
     */
    public function getSuccess()
    {
        $sessionId = $this->request->getGet('session_id');
        if (! $sessionId) {
            return redirect()->to('/order/new')
                ->with('error', 'Aucune session Stripe détectée.');
        }

        Stripe::setApiKey(env('stripe.secret_key'));
        $checkoutSession = Session::retrieve($sessionId);
        $meta = $checkoutSession->metadata ?? null;

        if (! $meta) {
            return redirect()->to('/order/new')
                ->with('error', 'Données de paiement manquantes.');
        }

        // 1) Extraire les metadata
        $concentrateId     = (int)$meta['concentrate_id'];
        $baseId            = (int)$meta['base_id'];
        $nicotineId        = (int)$meta['nicotine_id'];
        $bottleId          = (int)$meta['bottle_id'];
        $volumeTotal       = (float)$meta['volume_total'];
        $nicStrength       = (float)$meta['nic_strength'];
        $volumeConcentrate = (float)$meta['volume_concentrate'];
        $volumeNic         = (float)$meta['volume_nic'];
        $volumeBase        = (float)$meta['volume_base'];

        $now = date('Y-m-d H:i:s');

        // ------------------------------------
        // 2) Décrémenter le stock des liquides (en ml)
        // ------------------------------------

        // A) Concentré
        $concentrate = $this->stockItemModel->find($concentrateId);
        if ($concentrate) {
            $newQtyMl = max(0, $concentrate['quantity'] - $volumeConcentrate);
            $this->stockItemModel->update($concentrateId, ['quantity' => $newQtyMl]);
            $this->stockMovementModel->insert([
                'id_stock_item' => $concentrateId,
                'type'          => 'out',
                'quantity'      => $volumeConcentrate,
                'note'          => "Commande {$volumeTotal} ml – {$nicStrength} mg (concentré)",
                'created_at'    => $now,
            ]);
        }

        // B) Nicotine
        $nic = $this->stockItemModel->find($nicotineId);
        if ($nic) {
            $newQtyMl = max(0, $nic['quantity'] - $volumeNic);
            $this->stockItemModel->update($nicotineId, ['quantity' => $newQtyMl]);
            $this->stockMovementModel->insert([
                'id_stock_item' => $nicotineId,
                'type'          => 'out',
                'quantity'      => $volumeNic,
                'note'          => "Commande {$volumeTotal} ml – {$nicStrength} mg (nicotine)",
                'created_at'    => $now,
            ]);
        }

        // C) Base
        $base = $this->stockItemModel->find($baseId);
        if ($base) {
            $newQtyMl = max(0, $base['quantity'] - $volumeBase);
            $this->stockItemModel->update($baseId, ['quantity' => $newQtyMl]);
            $this->stockMovementModel->insert([
                'id_stock_item' => $baseId,
                'type'          => 'out',
                'quantity'      => $volumeBase,
                'note'          => "Commande {$volumeTotal} ml – {$nicStrength} mg (base)",
                'created_at'    => $now,
            ]);
        }

        // ------------------------------------
        // 3) Décrémenter le stock des fioles (en unités)
        // ------------------------------------
        $bottle = $this->stockItemModel->find($bottleId);
        if ($bottle) {
            // On retire 1 unité de fiole
            $newQtyUnits = max(0, $bottle['quantity'] - 1);
            $this->stockItemModel->update($bottleId, ['quantity' => $newQtyUnits]);

            // On crée un mouvement “out” pour la fiole :
            // la colonne `quantity` dans stock_movements sera “1” (une unité retirée)
            $this->stockMovementModel->insert([
                'id_stock_item' => $bottleId,
                'type'          => 'out',
                'quantity'      => 1,
                'note'          => "Commande fiole {$bottle['unit_volume_ml']} ml (1 unité)",
                'created_at'    => $now,
            ]);
        }

        // ------------------------------------
        // 4) Enregistrer la commande dans `orders` puis `order_items`
        // ------------------------------------
        $orderData = [
            'user_id'      => session()->get('user_id') ?? null,
            'total_amount' => 0.00,
            'created_at'   => $now,
        ];
        $this->orderModel->insert($orderData);
        $orderId = $this->orderModel->getInsertID();

        // On enregistre les trois ingrédients (en ml) + la fiole (en unités) dans `order_items`
        $itemsToInsert = [
            [
                'order_id'      => $orderId,
                'stock_item_id' => $concentrateId,
                'quantity_ml'   => $volumeConcentrate,
                'unit_price'    => 0.00,
                'created_at'    => $now,
            ],
            [
                'order_id'      => $orderId,
                'stock_item_id' => $nicotineId,
                'quantity_ml'   => $volumeNic,
                'unit_price'    => 0.00,
                'created_at'    => $now,
            ],
            [
                'order_id'      => $orderId,
                'stock_item_id' => $baseId,
                'quantity_ml'   => $volumeBase,
                'unit_price'    => 0.00,
                'created_at'    => $now,
            ],
            // Pour la fiole, on la note différemment : “quantity_ml = NULL” et on stocke “unit” dans quantity_unit
            [
                'order_id'       => $orderId,
                'stock_item_id'  => $bottleId,
                'quantity_ml'    => null,
                'quantity_unit'  => 1,
                'unit_price'     => 0.00,
                'created_at'     => $now,
            ],
        ];

        foreach ($itemsToInsert as $row) {
            $this->orderItemModel->insert($row);
        }

        // 5) (Optionnel) vider un éventuel “panier” en session
        session()->remove('cart');

        return $this->view('front/orders/success', [
            'orderId'             => $orderId,
            'volumeTotal'         => $volumeTotal,
            'nicStrength'         => $nicStrength,
            'volumeConcentrate'   => $volumeConcentrate,
            'volumeNic'           => $volumeNic,
            'volumeBase'          => $volumeBase,
            'bottleVolume'        => $bottle['unit_volume_ml'],
        ], true);
    }

    /**
     * GET /order/cancel
     */
    public function getCancel()
    {
        return redirect()->to('/order/new')
            ->with('error', 'Paiement annulé.');
    }
}
