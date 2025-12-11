<div class="row">
    <div class="col">
        <form action="<?= isset($materialmedicaltype) ? base_url("/admin/materialmedicaltype/update") : base_url("/admin/materialmedicaltype/create") ?>" method="POST">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">
                        <?= isset($materialmedicaltype) ? "Editer " . $materialmedicaltype['type'] : "Créer un Type de Matériel Médicale" ?>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="type" class="form-label">Type</label>
                        <input type="text" class="form-control" id="type" placeholder="Type de matériel" value="<?= isset($materialmedicaltype) ? htmlspecialchars($materialmedicaltype['type'], ENT_QUOTES) : ""; ?>" name="type" required>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        Annuler
                    </button>
                    <?php if (isset($materialmedicaltype)): ?>
                        <input type="hidden" name="id" value="<?= $materialmedicaltype['id']; ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <?= isset($materialmedicaltype) ? "Sauvegarder" : "Enregistrer" ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
