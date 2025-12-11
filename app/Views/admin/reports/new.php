<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Nouveau bilan</h1>
        <a href="<?= site_url('admin/reports') ?>" class="btn btn-outline-secondary">
            Retour à la liste
        </a>
    </div>

    <?php if (!empty($errors) && is_array($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $err): ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="post" action="<?= site_url('admin/reports') ?>">
                <?= csrf_field() ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Titre du bilan <span class="text-danger">*</span></label>
                        <input type="text"
                               name="title"
                               class="form-control"
                               value="<?= old('title') ?>"
                               required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Application étudiée <span class="text-danger">*</span></label>
                        <input type="text"
                               name="application_name"
                               class="form-control"
                               value="<?= old('application_name') ?>"
                               required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Version du document</label>
                        <input type="text"
                               name="version"
                               class="form-control"
                               placeholder="ex : v1.0, 2025-01"
                               value="<?= old('version') ?>">
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Auteur</label>
                        <input type="text"
                               name="author_name"
                               class="form-control"
                               value="<?= old('author_name') ?>">
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        Créer le bilan et passer à la rédaction
                    </button>
                    <a href="<?= site_url('admin/reports') ?>" class="btn btn-link">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>