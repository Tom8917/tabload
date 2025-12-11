<form action="<?= isset($ingredient) ? base_url('/admin/ingredient/update') : base_url('/admin/ingredient/create') ?>" method="POST">
    <?php if (isset($ingredient)): ?>
        <input type="hidden" name="id" value="<?= esc($ingredient['id']) ?>">
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <h4><?= isset($ingredient) ? 'Modifier' : 'Ajouter' ?> un ingrédient</h4>
        </div>
        <div class="card-body row g-3">
            <div class="col-md-6">
                <label>Nom*</label>
                <input type="text" name="name" class="form-control" required value="<?= esc($ingredient['name'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label>Type*</label>
                <select name="type" class="form-control" required>
                    <?php
                    $types = [
                        'base_pg' => 'Base PG',
                        'base_vg' => 'Base VG',
                        'nicotine' => 'Booster',
                        'aroma' => 'Arôme',
                        'additive' => 'Additif',
                        'bottle' => 'Fiole',
                        'label' => 'Étiquette',
                    ];
                    foreach ($types as $key => $label):
                        $selected = ($ingredient['type'] ?? '') === $key ? 'selected' : '';
                        ?>
                        <option value="<?= $key ?>" <?= $selected ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label>Stock (quantité)*</label>
                <input type="number" step="0.01" name="stock" class="form-control" required value="<?= esc($ingredient['stock'] ?? '0.00') ?>">
            </div>
            <div class="col-md-4">
                <label>Unité (ml, g, unit)*</label>
                <input type="text" name="unit" class="form-control" required value="<?= esc($ingredient['unit'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label>Prix par unité (€)*</label>
                <input type="number" step="0.01" name="price_per_unit" class="form-control" required value="<?= esc($ingredient['price_per_unit'] ?? '0.00') ?>">
            </div>
        </div>
        <div class="card-footer text-end">
            <a href="<?= base_url('/admin/ingredient') ?>" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-success">Enregistrer</button>
        </div>
    </div>
</form>
