<div class="row">
    <div class="col">
        <form action="<?= isset($materialmedicalbrand) ? base_url("/admin/materialmedicalbrand/update") : base_url("/admin/materialmedicalbrand/create") ?>" method="POST">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">
                        <?= isset($materialmedicalbrand) ? "Editer " . $materialmedicalbrand['marque'] : "Créer une Marque de Matériel Médicale" ?>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="marque" class="form-label">Marque</label>
                        <input type="text" class="form-control" id="marque" placeholder="Marque de matériel" value="<?= isset($materialmedicalbrand) ? htmlspecialchars($materialmedicalbrand['marque'], ENT_QUOTES) : ""; ?>" name="marque" required>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        Annuler
                    </button>
                    <?php if (isset($materialmedicalbrand)): ?>
                        <input type="hidden" name="id" value="<?= $materialmedicalbrand['id']; ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <?= isset($materialmedicalbrand) ? "Sauvegarder" : "Enregistrer" ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
