<?php
$errors       = $errors ?? (session('errors') ?? []);
$success      = $success ?? session('success');
$sectionsTree = $sectionsTree ?? [];
$roots        = $roots ?? $sectionsTree;
$canEdit      = $canEdit ?? false;

/**
 * Rendu récursif du plan : garantit que les sous-parties
 * s'affichent sous le bon parent (children).
 */
$renderNode = function (array $node) use (&$renderNode, $report, $canEdit, $errors) {

    $level = (int)($node['level'] ?? 1);

    $ms = 'ms-0';
    if ($level === 2) $ms = 'ms-3';
    elseif ($level === 3) $ms = 'ms-5';
    elseif ($level >= 4) $ms = 'ms-5';

    $code  = (string)($node['code'] ?? '');
    $title = (string)($node['title'] ?? '');

    // Badge conformité (optionnel)
    $comp = (string)($node['compliance_status'] ?? 'non_applicable');
    $showComp = ($comp !== '' && $comp !== 'non_applicable');

    $compBadge = 'bg-secondary';
    if ($comp === 'conforme') $compBadge = 'bg-success';
    elseif ($comp === 'non_conforme') $compBadge = 'bg-danger';
    elseif ($comp === 'partiel') $compBadge = 'bg-warning';

    ?>
    <li class="mb-3 <?= $ms ?>">
        <div class="d-flex justify-content-between align-items-start gap-2">
            <div>
                <?php if ($code !== ''): ?>
                    <strong><?= esc($code) ?></strong>&nbsp;
                <?php endif; ?>

                <?= esc($title) ?>

                <?php if ($showComp): ?>
                    <span class="badge <?= $compBadge ?> ms-2">
                        <?= esc(ucfirst(str_replace('_', ' ', $comp))) ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="ms-2 d-flex flex-wrap gap-2">
                <a href="<?= site_url('report/' . $report['id']) ?>#section-<?= (int)$node['id'] ?>"
                   class="btn btn-sm btn-outline-primary">
                    Voir
                </a>

                <?php if ($canEdit): ?>
                    <a href="<?= site_url('report/' . $report['id'] . '/sections/' . $node['id'] . '/edit') ?>"
                       class="btn btn-sm btn-outline-secondary">
                        Modifier
                    </a>

                    <form method="post"
                          action="<?= site_url('report/' . $report['id'] . '/sections/' . $node['id'] . '/delete') ?>"
                          onsubmit="return confirm('Supprimer cette section et toutes ses sous-sections ?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            Supprimer
                        </button>
                    </form>

                    <button class="btn btn-sm btn-outline-primary"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#childForm<?= (int)$node['id'] ?>">
                        + Sous-partie
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($node['content'])): ?>
            <div class="mt-1 small text-muted">
                <?= esc(mb_strimwidth(strip_tags((string)$node['content']), 0, 200, '…', 'UTF-8')) ?>
            </div>
        <?php endif; ?>

        <?php if ($canEdit): ?>
            <div class="collapse mt-2" id="childForm<?= (int)$node['id'] ?>">
                <div class="card card-body">
                    <form method="post"
                          action="<?= site_url('report/' . $report['id'] . '/sections/' . $node['id'] . '/child') ?>">
                        <?= csrf_field() ?>

                        <div class="mb-2">
                            <label class="form-label">Titre de la sous-partie <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="title"
                                   class="form-control <?= isset($errors['title_child_' . $node['id']]) ? 'is-invalid' : '' ?>">
                            <?php if (isset($errors['title_child_' . $node['id']])): ?>
                                <div class="invalid-feedback">
                                    <?= esc($errors['title_child_' . $node['id']]) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Contenu</label>
                            <textarea name="content" rows="3" class="form-control"></textarea>
                        </div>

                        <button type="submit" class="btn btn-sm btn-primary">
                            Ajouter la sous-partie
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($node['children']) && is_array($node['children'])): ?>
            <ul class="list-unstyled mt-3">
                <?php foreach ($node['children'] as $child): ?>
                    <?php $renderNode($child); ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </li>
    <?php
};
?>

<div class="container-fluid">

    <?= view('front/reports/_steps', [
        'step'     => 'write',
        'reportId' => $report['id'],
        'canEdit'  => $canEdit,
    ]) ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Rédaction : <?= esc($report['title']) ?></h1>
            <div class="text-muted small">
                Application : <?= esc($report['application_name']) ?>
                <?php if (!empty($report['version'])): ?>
                    &nbsp;·&nbsp; Version : <?= esc($report['version']) ?>
                <?php endif; ?>
                <?php if (!empty($report['author_name'])): ?>
                    &nbsp;·&nbsp; Auteur : <?= esc($report['author_name']) ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="<?= site_url('report/' . $report['id']) ?>" class="btn btn-outline-primary">
                Aperçu
            </a>
            <a href="<?= site_url('report') ?>" class="btn btn-outline-secondary">
                Retour à la liste
            </a>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= esc($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors) && is_array($errors) && empty($errors['title_root'])): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $err): ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!$canEdit): ?>
        <div class="alert alert-info">
            Lecture seule : vous pouvez consulter le plan, mais vous ne pouvez pas modifier ce bilan.
        </div>
    <?php endif; ?>

    <?php if ($canEdit): ?>
        <!-- Ajouter une PARTIE (niveau 1) -->
        <div class="card mb-4">
            <div class="card-header">Ajouter une partie (niveau 1)</div>
            <div class="card-body">
                <form method="post" action="<?= site_url('report/' . $report['id'] . '/sections/root') ?>">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label">Titre de la partie <span class="text-danger">*</span></label>
                        <input type="text"
                               name="title"
                               class="form-control <?= isset($errors['title_root']) ? 'is-invalid' : '' ?>"
                               value="<?= old('title') ?>">
                        <?php if (isset($errors['title_root'])): ?>
                            <div class="invalid-feedback"><?= esc($errors['title_root']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contenu (optionnel)</label>
                        <textarea name="content" rows="3" class="form-control"><?= old('content') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Ajouter la partie</button>
                </form>
            </div>
        </div>

        <?php if (!empty($roots)): ?>
            <div class="card mb-4">
                <div class="card-header">Étapes de rédaction</div>
                <div class="card-body d-flex flex-wrap gap-2">
                    <?php foreach ($roots as $r): ?>
                        <a class="btn btn-outline-primary btn-sm"
                           href="<?= site_url('report/' . $report['id'] . '/sections/' . $r['id'] . '/edit') ?>">
                            <?= esc($r['code']) ?>. <?= esc($r['title']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Plan du bilan -->
    <div class="card">
        <div class="card-header">Plan du bilan</div>
        <div class="card-body">
            <?php if (empty($sectionsTree)): ?>
                <p class="text-muted mb-0">Aucune section pour l’instant.</p>
            <?php else: ?>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($sectionsTree as $node): ?>
                        <?php $renderNode($node); ?>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

</div>
