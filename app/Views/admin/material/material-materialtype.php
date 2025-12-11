<div class="row">
    <div class="col">
        <form action="<?= isset($materialtype) ? base_url("/admin/materialtype/update") : base_url("/admin/materialtype/create") ?>" method="POST">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">
                        <?= isset($materialtype) ? "Editer " . $materialtype['type'] : "Créer un Type de Matériel" ?>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="type" class="form-label">Type</label>
                        <input type="text" class="form-control" id="type" placeholder="Type de matériel" value="<?= isset($materialtype) ? htmlspecialchars($materialtype['type'], ENT_QUOTES) : ""; ?>" name="type" required>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        Annuler
                    </button>
                    <?php if (isset($materialtype)): ?>
                        <input type="hidden" name="id" value="<?= $materialtype['id']; ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <?= isset($materialtype) ? "Sauvegarder" : "Enregistrer" ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
