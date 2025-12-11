<h1><?= isset($password) ? "Modifier" : "Ajouter" ?> un mot de passe</h1>

<div class="row">
    <div class="col">
        <form action="<?= isset($password) ? base_url("/admin/configpassword/update") : base_url("/admin/configpassword/create") ?>" method="POST">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">
                        <?= isset($password) ? "Editer " . $password['page_slug'] : "Créer un mot de passe pour un accès privé" ?>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="page_slug" class="form-label">Page privée</label>
                        <select name="page_slug" id="page_slug" class="form-control" required>
                            <option value="">Choisissez une page</option>
                            <?php foreach($pages as $page): ?>
                                <option value="<?= esc($page) ?>" <?= isset($password) && $password['page_slug'] === $page ? 'selected' : '' ?>>
                                    <?= ucfirst(esc($page)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <input type="hidden" name="id" value="<?= isset($password) ? esc($password['id']) : '' ?>" />
                        <label for="password">Mot de passe :</label>
                        <input type="password" name="password" value="" />
                    </div>
                    <div>
                        <textarea class="form-control" id="label" placeholder="label" name="label"><?= isset($password) ? $password['label'] : ""; ?></textarea>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        Annuler
                    </button>
                    <?php if (isset($password)): ?>
                        <input type="hidden" name="id" value="<?= $password['id']; ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <?= isset($password) ? "Sauvegarder" : "Enregistrer" ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

