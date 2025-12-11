<form action="<?= isset($product) ? base_url('/admin/stockproduct/update') : base_url('/admin/stockproduct/create') ?>"
      method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <?php if (!empty($product['id'])): ?>
        <input type="hidden" name="id" value="<?= esc($product['id']) ?>">
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header">
            <h4><?= isset($product) ? 'Modifier le produit' : 'Ajouter un produit' ?></h4>
        </div>

        <div class="card-body">
            <div class="mb-3">
                <label for="id_stock_type">Type de produit</label>
                <select name="id_stock_type" id="id_stock_type" class="form-select" required>
                    <option value="">— Choisir —</option>
                    <?php foreach ($types as $type): ?>
                        <option value="<?= $type['id'] ?>" <?= isset($product) && $product['id_stock_type'] == $type['id'] ? 'selected' : '' ?>>
                            <?= esc($type['name']) ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="id_stock_provider">Fournisseur</label>
                <select name="id_stock_provider" id="id_stock_provider" class="form-select" required>
                    <option value="">— Choisir —</option>
                    <?php foreach ($providers as $provider): ?>
                        <option value="<?= $provider['id'] ?>" <?= isset($product) && $product['id_stock_provider'] == $provider['id'] ? 'selected' : '' ?>>
                            <?= esc($provider['name']) ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="unit_price">Prix unitaire (€)</label>
                <input type="number" name="unit_price" class="form-control" step="0.01" required
                       value="<?= esc($product['unit_price'] ?? '') ?>">
            </div>
        </div>

        <div class="mb-3">
            <label for="image">Image du produit (facultatif)</label>
            <input type="file" name="image" id="image" class="form-control">
            <?php if (!empty($product['image'])): ?>
                <img src="<?= base_url('uploads/stock_products/' . esc($product['image'])) ?>"
                     alt="Aperçu" class="mt-2"
                     style="max-height: 200px; object-fit: contain;">
            <?php endif; ?>
        </div>

        <div class="card-footer text-end">
            <a href="<?= base_url('/admin/stockproduct') ?>" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-success">Enregistrer</button>
        </div>
    </div>
</form>
