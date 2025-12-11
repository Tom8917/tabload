<form action="<?= isset($role) ? base_url('/admin/stockrole/update') : base_url('/admin/stockrole/create') ?>" method="post">
    <?php if (isset($role)): ?>
        <input type="hidden" name="id" value="<?= $role['id'] ?>">
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header">
            <h4><?= isset($role) ? 'Modifier un rôle' : 'Ajouter un nouveau rôle' ?></h4>
        </div>

        <div class="card-body">
            <div class="mb-3">
                <label for="name">Nom du rôle *</label>
                <input type="text" name="name" class="form-control" required value="<?= esc($role['name'] ?? '') ?>">
            </div>
        </div>

        <div class="card-footer text-end">
            <a href="<?= base_url('/admin/stockrole') ?>" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-success">Enregistrer</button>
        </div>
    </div>
</form>
