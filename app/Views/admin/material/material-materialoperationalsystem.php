<div class="row">
    <div class="col">
        <form action="<?= isset($materialoperationalsystem) ? base_url("/admin/materialoperationalsystem/update") : base_url("/admin/materialoperationalsystem/create") ?>" method="POST">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">
                        <?= isset($materialoperationalsystem) ? "Editer " . $materialoperationalsystem['type'] : "Ajouter un OS de Matériel" ?>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="type" class="form-label">OS</label>
                        <input type="text" class="form-control" id="type" placeholder="OS du matériel" value="<?= isset($materialoperationalsystem) ? htmlspecialchars($materialoperationalsystem['type'], ENT_QUOTES) : ""; ?>" name="type" required>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        Annuler
                    </button>
                    <?php if (isset($materialoperationalsystem)): ?>
                        <input type="hidden" name="id" value="<?= $materialoperationalsystem['id']; ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <?= isset($materialoperationalsystem) ? "Sauvegarder" : "Enregistrer" ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
