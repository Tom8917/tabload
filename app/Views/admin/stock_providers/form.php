<form action="<?= isset($provider) ? base_url('/admin/stockprovider/update') : base_url('/admin/stockprovider/create') ?>"
      method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <?php if (!empty($provider['id'])): ?>
        <input type="hidden" name="id" value="<?= esc($provider['id']) ?>">
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <h4><?= isset($provider) ? 'Modifier le fournisseur' : 'Nouveau fournisseur' ?></h4>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="name" class="form-label">Nom du fournisseur *</label>
                <input type="text" name="name" id="name" class="form-control" required
                       value="<?= esc($provider['name'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label for="image" class="form-label">Logo / Image</label>
                <input type="file" name="image" id="image" class="form-control">
                <?php if (!empty($provider['image'])): ?>
                    <img src="<?= base_url('uploads/stock_providers/' . esc($provider['image'])) ?>"
                         alt="Image fournisseur"
                         class="mt-2"
                         style="max-height: 200px; object-fit: contain;">
                <?php endif; ?>
            </div>
        </div>
        <div class="card-footer text-end">
            <a href="<?= base_url('/admin/stockprovider') ?>" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-success">Enregistrer</button>
        </div>
    </div>
</form>
