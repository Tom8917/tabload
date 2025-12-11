<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class VapeSeeder extends Seeder
{
    public function run()
    {
        // 🔓 Désactive les contraintes pour pouvoir tout vider
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');

        // 🧹 Vide les tables dans l'ordre inverse des dépendances
        $this->db->table('recipe_ingredients')->truncate(); // dépend de stock_items
        $this->db->table('stock_items')->truncate();        // dépend de stock_products
        $this->db->table('stock_products')->truncate();     // dépend de stock_types, stock_providers
        $this->db->table('stock_type_roles')->truncate();   // dépend de stock_types + stock_roles
        $this->db->table('stock_types')->truncate();        // dépend de rien
        $this->db->table('stock_providers')->truncate();
        $this->db->table('stock_roles')->truncate();

        // 🔒 Réactive les contraintes
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');

        // ✅ Appelle les seeders dans l’ordre logique
        $this->call('StockRoles');        // stock_roles
        $this->call('StockTypes');        // stock_types
        $this->call('StockProviders');    // stock_providers
        $this->call('StockTypeRole');     // pivot entre types et rôles
        $this->call('StockProducts');     // produits finaux (nécessaire pour les réceptions)
        $this->call('StockItem');         // stock actuel lié aux produits
    }
}
