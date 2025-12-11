<div class="row">
    <div class="col">
        <form action="<?= isset($ticketcategory) ? base_url("/admin/ticketcategory/update") : base_url("/admin/ticketcategory/create") ?>" method="POST">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">
                        <?= isset($ticketcategory) ? "Editer " . $ticketcategory['type'] : "Créer une catégorie de ticket" ?>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="type" class="form-label">Catégorie</label>
                        <input type="text" class="form-control" id="type" placeholder="Type de catégorie" value="<?= isset($ticketcategory) ? htmlspecialchars($ticketcategory['type'], ENT_QUOTES) : ""; ?>" name="type" required>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        Annuler
                    </button>
                    <?php if (isset($ticketcategory)): ?>
                        <input type="hidden" name="id" value="<?= $ticketcategory['id']; ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <?= isset($ticketcategory) ? "Sauvegarder" : "Enregistrer" ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
