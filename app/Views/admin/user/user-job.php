<form action="<?= isset($job) ? base_url("/admin/job/update") : base_url("/admin/job/create"); ?>"
      method="POST">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">
                <?= isset($job) ? "Editer " . $job['type'] : "Créer un Métier" ?>
            </h4>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="type" class="form-label">Nom du métier</label>
                <input type="text" class="form-control" id="type" placeholder="type" value="<?= isset($job) ? $job['type'] : ""; ?>" name="type">
            </div>
            <div class="mb-3">
                <label for="diminutif" class="form-label">Diminutif du métier</label>
                <input type="text" class="form-control" id="diminutif" placeholder="diminutif" value="<?= isset($job) ? $job['diminutif'] : ""; ?>" name="diminutif">
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                Annuler
            </button>
            <?php if (isset($job)): ?>
                <input type="hidden" name="id" value="<?= $job['id']; ?>">
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">
                <?= isset($job) ? "Sauvegarder" : "Enregistrer" ?>
            </button>
        </div>
    </div>
</form>