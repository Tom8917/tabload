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
                        <label class="form-label">Version</label>
                        <input name="version" class="form-control" value="<?= esc(old('version', $report['version'] ?? '')) ?>">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Statut</label>
                        <select name="status" class="form-select">
                            <?php
                            $st = old('status', $report['status'] ?? 'draft');

                            // compat si anciens statuts FR en base
                            $st = match ($st) {
                                'brouillon' => 'draft',
                                'en_relecture', 'a_corriger', 'à corriger' => 'in_review',
                                'valide', 'validé', 'conforme' => 'validated',
                                'non_conforme', 'refusé', 'rejeté' => 'rejected',
                                default => $st,
                            };
                            ?>
                            <option value="draft" <?= $st==='draft'?'selected':'' ?>>Brouillon</option>
                            <option value="in_review" <?= $st==='in_review'?'selected':'' ?>>À corriger</option>
                            <option value="validated" <?= $st==='validated'?'selected':'' ?>>Validé</option>
                            <option value="rejected" <?= $st==='rejected'?'selected':'' ?>>Non conforme</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Rédigé par</label>
                        <input name="author_name" class="form-control" value="<?= esc(old('author_name', $report['author_name'] ?? '')) ?>">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Validé par (correcteur)</label>
                        <input name="corrector_name" class="form-control" value="<?= esc(old('corrector_name', $report['corrector_name'] ?? '')) ?>">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Date de validation</label>
                        <input type="date" name="validated_at" class="form-control" value="<?= esc(old('validated_at', $report['validated_at'] ?? '')) ?>">
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
