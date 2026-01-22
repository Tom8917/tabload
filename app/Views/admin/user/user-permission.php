<form action="<?= isset($permission) ? base_url("/admin/userpermission/update") : base_url("/admin/userpermission/create"); ?>"
      method="POST">
    <?= csrf_field() ?>
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">
                <?= isset($permission) ? "Editer " . $permission['name'] : "Créer un rôle" ?>
            </h4>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="name" class="form-label">Nom de la permission</label>
                <input type="text" class="form-control" id="name" placeholder="name" value="<?= isset($permission) ? $permission['name'] : ""; ?>" name="name">
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                Annuler
            </button>
            <?php if (isset($permission)): ?>
                <input type="hidden" name="id" value="<?= $permission['id']; ?>">
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">
                <?= isset($permission) ? "Sauvegarder" : "Enregistrer" ?>
            </button>
        </div>
    </div>
</form>
