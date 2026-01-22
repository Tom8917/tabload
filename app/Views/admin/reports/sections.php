<?php
$errors  = $errors ?? (session('errors') ?? []);
$success = $success ?? session('success');

$report       = $report ?? [];
$sectionsTree = $sectionsTree ?? [];
$admins       = $admins ?? [];

$canEdit = $canEdit ?? true;

$doc = old('doc_status', $report['doc_status'] ?? 'work');
$mk  = old('modification_kind', $report['modification_kind'] ?? 'creation');
$st  = old('status', $report['status'] ?? 'brouillon');

// Infos “Validation / Corrections”
$validatedAt = $report['validated_at'] ?? null;
$validatedBy = (int)($report['validated_by_id'] ?? 0);
$correctedAt = $report['corrected_at'] ?? null;
?>

<?= view('admin/reports/_steps', [
    'step'     => 'write',
    'reportId' => (int)($report['id'] ?? 0),
    'canEdit'  => $canEdit ?? true,
]) ?>

<?php
helper('html');

$fmtDate = function ($value): string {
    if (empty($value)) return '—';
    try {
        return (new DateTime((string)$value))->format('d/m/Y');
    } catch (\Throwable $e) {
        return (string)$value;
    }
};

$cb = function (bool $checked): string {
    return $checked ? '<i class="fa-regular fa-circle-check"></i>' : '<i class="fa-regular fa-circle"></i>';
};

$docStatus = (string)($report['doc_status'] ?? 'work');
$modKind   = (string)($report['modification_kind'] ?? 'creation');

$appName    = (string)($report['application_name'] ?? '—');
$appVersion = (string)($report['version'] ?? '');
$author     = (string)($report['author_name'] ?? '');
$fileId     = (string)($report['file_media_id'] ?? '');
$fileName   = (string)($report['file_name'] ?? '');
$validatedAt = $report['validated_at'] ?? null;
$createdAt   = $report['created_at'] ?? null;
$updatedAt   = $report['updated_at'] ?? null;

$status = (string)($report['status'] ?? '');
$statusLabel = $status !== '' ? $status : '—';

$errors = $errors ?? (session('errors') ?? []);
$success = $success ?? session('success');

$sectionsTree = $sectionsTree ?? [];
$admins = $admins ?? [];

$scrollOffset = 90;

$indentClass = function (int $level): string {
    return match (true) {
        $level <= 1 => 'ms-0',
        $level === 2 => 'ms-3',
        default => 'ms-5',
    };
};
$headingClass = function (int $level): string {
    return match ($level) {
        1 => 'h4 fw-bold mb-2',
        2 => 'h5 fw-semibold mb-2',
        default => 'h6 fw-semibold mb-2 text-body',
    };
};
$badgeLevel = function (int $level): string {
    return match ($level) {
        1 => 'bg-primary',
        2 => 'bg-secondary',
        default => 'bg-dark',
    };
};

$status = (string)($report['status'] ?? 'brouillon');
$docStatusLabel = ucfirst(str_replace('_', ' ', $status));

$validatedAt = $report['validated_at'] ?? null;
$validatedBy = (int)($report['validated_by_id'] ?? 0);

$correctedAt = $report['corrected_at'] ?? null;
$correctedBy = (int)($report['corrected_by'] ?? 0);

// Sommaire roots
$roots = $sectionsTree;
?>

<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                Bilan : <?= esc($report['title'] ?? '') ?>
            </h1>
        </div>

        <div class="d-flex gap-2">
            <a href="<?= site_url('admin/reports/' . (int)($report['id'] ?? 0)) ?>" class="btn btn-outline-primary">
                Consulter
            </a>
            <a href="<?= site_url('admin/reports') ?>" class="btn btn-outline-secondary">
                Retour à la liste
            </a>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= esc($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors) && is_array($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $k => $err): ?>
                    <li><?= esc(is_string($err) ? $err : (string)$k) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row g-4 mb-5">

        <!-- META / INFOS DOCUMENT -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header fw-semibold">Infos du document</div>
                <div class="card-body">

                    <form method="post" action="<?= site_url('admin/reports/' . $report['id'] . '/update') ?>">
                        <?= csrf_field() ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Titre</label>
                                <input name="title" class="form-control"
                                       value="<?= esc(old('title', $report['title'] ?? '')) ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Application</label>
                                <input name="application_name" class="form-control"
                                       value="<?= esc(old('application_name', $report['application_name'] ?? '')) ?>">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Version</label>
                                <input name="version" class="form-control"
                                       value="<?= esc(old('version', $report['version'] ?? '')) ?>">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Statut (workflow)</label>
                                <?php $st = old('status', $report['status'] ?? 'brouillon'); ?>
                                <select name="status" class="form-select">
                                    <option value="brouillon" <?= $st === 'brouillon' ? 'selected' : '' ?>>Brouillon
                                    </option>
                                    <option value="en_relecture" <?= $st === 'en_relecture' ? 'selected' : '' ?>>En
                                        relecture
                                    </option>
                                    <option value="final" <?= $st === 'final' ? 'selected' : '' ?>>Final</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Auteur</label>
                                <input name="author_name" class="form-control"
                                       value="<?= esc(old('author_name', $report['author_name'] ?? '')) ?>">
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-md-12 mt-3">
                                <label class="form-label">Statut du document</label>
                                <?php $doc = old('doc_status', $report['doc_status'] ?? 'work'); ?>

                                <div class="d-flex flex-wrap gap-3 mt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="doc_status" id="doc_validated"
                                               value="validated"
                                            <?= $doc === 'validated' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="doc_validated">Document validé</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="doc_status" id="doc_approved"
                                               value="approved"
                                            <?= $doc === 'approved' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="doc_approved">Document approuvé</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="doc_status" id="doc_work"
                                               value="work"
                                            <?= $doc === 'work' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="doc_work">Document de travail</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 mt-4 mb-4">
                                <label class="form-label">Modification par rapport à l’existant</label>
                                <?php $mk = old('modification_kind', $report['modification_kind'] ?? 'creation'); ?>

                                <div class="d-flex flex-wrap gap-3 mt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="modification_kind"
                                               id="mk_creation" value="creation"
                                            <?= $mk === 'creation' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="mk_creation">Création</label>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="modification_kind"
                                               id="mk_replace" value="replace"
                                            <?= $mk === 'replace' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="mk_replace">Annule et remplace la version
                                            précédente</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-2">
                            <button class="btn btn-primary" type="submit">Enregistrer</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <!-- VALIDATION ADMIN -->
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header fw-semibold">Validation / Corrections</div>
                <div class="card-body">

                    <div class="mb-3">
                        <div class="small text-muted">Dernière correction</div>
                        <div>
                            <?= !empty($correctedAt) ? esc($correctedAt) : '<span class="text-muted">—</span>' ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted">Validation</div>
                        <div>
                            <?= !empty($validatedAt) ? esc($validatedAt) : '<span class="text-muted">Non validé</span>' ?>
                        </div>
                    </div>

                    <hr>

                    <!-- Désigner validateur (optionnel) -->
                    <form method="post"
                          action="<?= site_url('admin/reports/' . $report['id'] . '/assign-validator') ?>">
                        <?= csrf_field() ?>

                        <label class="form-label">Validateur désigné</label>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <select name="validated_by" class="form-select">
                                        <option value="">Aucun</option>
                                        <?php foreach ($admins as $a): ?>
                                            <?php
                                            $id = (int)($a['id'] ?? 0);
                                            $name = trim(
                                                (string)($a['firstname'] ?? '') . ' ' . (string)($a['lastname'] ?? '')
                                            );
                                            if ($name === '') $name = (string)($a['name'] ?? ('Admin #' . $id));
                                            ?>
                                            <option value="<?= $id ?>" <?= $validatedBy === $id ? 'selected' : '' ?>>
                                                <?= esc($name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    Enregistrer le validateur
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <form method="post"
                              action="<?= site_url('admin/reports/' . $report['id'] . '/mark-in-review') ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-outline-warning" type="submit">
                                Passer en relecture
                            </button>
                        </form>

                        <form method="post" action="<?= site_url('admin/reports/' . $report['id'] . '/validate') ?>"
                              onsubmit="return confirm('Valider ce bilan ? La date sera enregistrée automatiquement.');">
                            <?= csrf_field() ?>
                            <button class="btn btn-outline-success" type="submit">
                                Valider
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <?php if ($canEdit): ?>
        <!-- Ajouter une PARTIE (niveau 1) -->
        <div class="card mb-4">
            <div class="card-header">Ajouter une partie (niveau 1)</div>
            <div class="card-body">
                <form method="post" action="<?= site_url('admin/reports/' . (int)($report['id'] ?? 0) . '/sections/root') ?>">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label">Titre de la partie <span class="text-danger">*</span></label>
                        <input type="text"
                               name="title"
                               class="form-control <?= isset($errors['title_root']) ? 'is-invalid' : '' ?>"
                               value="<?= esc(old('title')) ?>">
                        <?php if (isset($errors['title_root'])): ?>
                            <div class="invalid-feedback"><?= esc($errors['title_root']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contenu (optionnel)</label>
                        <textarea name="content" rows="3" class="form-control"><?= esc(old('content')) ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Ajouter la partie</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?php
    /**
     * Rendu récursif ADMIN (identique front dans l’esprit)
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
            <div class="d-flex align-items-start gap-1 mb-2">

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
                    <a href="<?= site_url('admin/reports/' . (int)($report['id'] ?? 0)) ?>#section-<?= (int)($node['id'] ?? 0) ?>"
                       class="btn btn-sm btn-outline-primary">
                        Voir
                    </a>

                    <?php if ($canEdit): ?>
                        <a href="<?= site_url('admin/reports/' . (int)($report['id'] ?? 0) . '/sections/' . (int)($node['id'] ?? 0) . '/edit') ?>"
                           class="btn btn-sm btn-outline-secondary">
                            Modifier
                        </a>

                        <form method="post"
                              action="<?= site_url('admin/reports/' . (int)($report['id'] ?? 0) . '/sections/' . (int)($node['id'] ?? 0) . '/delete') ?>"
                              onsubmit="return confirm('Supprimer cette section et toutes ses sous-sections ?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                Supprimer
                            </button>
                        </form>

                        <button class="btn btn-sm btn-outline-primary"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#childForm<?= (int)($node['id'] ?? 0) ?>">
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
                <div class="collapse mt-2" id="childForm<?= (int)($node['id'] ?? 0) ?>">
                    <div class="card card-body">
                        <form method="post"
                              action="<?= site_url('admin/reports/' . (int)($report['id'] ?? 0) . '/sections/' . (int)($node['id'] ?? 0) . '/child') ?>">
                            <?= csrf_field() ?>

                            <div class="mb-2">
                                <label class="form-label">Titre de la sous-partie <span class="text-danger">*</span></label>
                                <input type="text"
                                       name="title"
                                       class="form-control <?= isset($errors['title_child_' . (int)($node['id'] ?? 0)]) ? 'is-invalid' : '' ?>"
                                       value="<?= esc(old('title')) ?>">
                                <?php if (isset($errors['title_child_' . (int)($node['id'] ?? 0)])): ?>
                                    <div class="invalid-feedback">
                                        <?= esc($errors['title_child_' . (int)($node['id'] ?? 0)]) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Contenu</label>
                                <textarea name="content" rows="3" class="form-control"><?= esc(old('content')) ?></textarea>
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

    <!-- Plan du bilan -->
    <div class="card">
        <div class="card-header">Plan du bilan</div>
        <div class="card-body">
            <?php if (empty($sectionsTree)): ?>
                <p class="text-muted mb-0">
                    Aucune section pour l’instant. Commencez par ajouter une partie ci-dessus.
                </p>
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
