<?php
$errors  = $errors ?? (session('errors') ?? []);
$success = $success ?? session('success');
?>

<div class="container-fluid">

    <?= view('front/reports/_steps', [
        'step'     => 'write',
        'reportId' => $report['id'],
        'canEdit'  => true,
    ]) ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Éditer les infos du bilan</h1>
            <div class="text-muted small">Bilan : <?= esc($report['title']) ?></div>
        </div>
        <a href="<?= site_url('report/' . $report['id'] . '/sections') ?>" class="btn btn-outline-secondary">
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
        <div class="card-header">Bureau de l’intégration</div>
        <div class="card-body">
            <form method="post" action="<?= site_url('report/' . $report['id'] . '/meta/update') ?>">
                <?= csrf_field() ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Titre</label>
                        <input name="title" class="form-control" value="<?= esc(old('title', $report['title'] ?? '')) ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Application</label>
                        <input name="application_name" class="form-control" value="<?= esc(old('application_name', $report['application_name'] ?? '')) ?>">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Version de l’application</label>
                        <input name="application_version" class="form-control"
                               value="<?= esc(old('application_version', $report['application_version'] ?? '')) ?>">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Statut (workflow)</label>
                        <select name="status" class="form-select">
                            <?php $st = old('status', $report['status'] ?? 'brouillon'); ?>
                            <option value="brouillon" <?= $st==='brouillon'?'selected':'' ?>>Brouillon</option>
                            <option value="en_relecture" <?= $st==='en_relecture'?'selected':'' ?>>En relecture</option>
                            <option value="final" <?= $st==='final'?'selected':'' ?>>Final</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Rédigé par</label>
                        <input name="author_name" class="form-control" value="<?= esc(old('author_name', $report['author_name'] ?? '')) ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Correcteur</label>
                        <input class="form-control" value="<?= esc($report['corrected_by'] ?? '') ?>" readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Validé par</label>
                        <input class="form-control" value="<?= esc($report['validated_by'] ?? '') ?>" readonly>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Enregistrer</button>
                    <a class="btn btn-outline-secondary" href="<?= site_url('report/' . $report['id']) ?>">Retour aperçu</a>
                </div>
            </form>
        </div>
    </div>
</div>
