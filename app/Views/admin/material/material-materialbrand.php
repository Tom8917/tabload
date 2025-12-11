<div class="row">
    <div class="col">
        <form action="<?= isset($materialbrand) ? base_url("/admin/materialbrand/update") : base_url("/admin/materialbrand/create") ?>" method="POST">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">
                        <?= isset($materialbrand) ? "Editer " . $materialbrand['marque'] : "Créer une Marque de Matériel" ?>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="marque" class="form-label">Marque</label>
                        <input type="text" class="form-control" id="marque" placeholder="Marque de matériel" value="<?= isset($materialbrand) ? htmlspecialchars($materialbrand['marque'], ENT_QUOTES) : ""; ?>" name="marque" required>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        Annuler
                    </button>
                    <?php if (isset($materialbrand)): ?>
                        <input type="hidden" name="id" value="<?= $materialbrand['id']; ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <?= isset($materialbrand) ? "Sauvegarder" : "Enregistrer" ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
