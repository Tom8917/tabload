<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                Éditer la section <?= esc($section['code']) ?> – <?= esc($section['title']) ?>
            </h1>
            <div class="text-muted small">
                Bilan : <?= esc($report['title']) ?>
                &nbsp;·&nbsp; Application : <?= esc($report['application_name']) ?>
            </div>
        </div>
        <a href="<?= site_url('admin/reports/' . $report['id'] . '/sections') ?>"
           class="btn btn-outline-secondary">
            Retour au plan
        </a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= esc($success) ?></div>
    <?php endif; ?>

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
        <div class="card-header">
            Informations de la section
        </div>
        <div class="card-body">
            <form method="post"
                  action="<?= site_url('admin/reports/' . $report['id'] . '/sections/' . $section['id'] . '/update') ?>">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label">Titre de la section <span class="text-danger">*</span></label>
                    <input type="text"
                           name="title"
                           class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                           value="<?= old('title', $section['title']) ?>">
                    <?php if (isset($errors['title'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['title']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contenu</label>
                    <textarea name="content"
                              rows="6"
                              class="form-control"><?= old('content', $section['content']) ?></textarea>
                </div>

                <hr>

                <h5 class="mb-3">Période et conformité</h5>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Libellé de période</label>
                        <input type="text"
                               name="period_label"
                               class="form-control"
                               placeholder="ex : Trimestre 1, Période A..."
                               value="<?= old('period_label', $section['period_label']) ?>">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Numéro de période</label>
                        <input type="number"
                               name="period_number"
                               class="form-control"
                               value="<?= old('period_number', $section['period_number']) ?>">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Date de début</label>
                        <input type="date"
                               name="start_date"
                               class="form-control"
                               value="<?= old('start_date', $section['start_date']) ?>">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Date de fin</label>
                        <input type="date"
                               name="end_date"
                               class="form-control"
                               value="<?= old('end_date', $section['end_date']) ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Débit (valeur)</label>
                        <input type="text"
                               name="debit_value"
                               class="form-control"
                               value="<?= old('debit_value', $section['debit_value']) ?>">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Conformité</label>
                        <?php
                        $compliance = old('compliance_status', $section['compliance_status']);
                        ?>
                        <select name="compliance_status" class="form-select">
                            <option value="non_applicable" <?= $compliance === 'non_applicable' ? 'selected' : '' ?>>
                                Non applicable
                            </option>
                            <option value="conforme" <?= $compliance === 'conforme' ? 'selected' : '' ?>>
                                Conforme
                            </option>
                            <option value="non_conforme" <?= $compliance === 'non_conforme' ? 'selected' : '' ?>>
                                Non conforme
                            </option>
                            <option value="partiel" <?= $compliance === 'partiel' ? 'selected' : '' ?>>
                                Partiel
                            </option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        Enregistrer les modifications
                    </button>
                    <a href="<?= site_url('admin/reports/' . $report['id'] . '/sections') ?>"
                       class="btn btn-link">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>
