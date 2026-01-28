<?php
/**
 * ADMIN - reports/sections.php
 * Objectif : variables propres, pas de redéfinition, cohérence des champs.
 */

$errors  = session()->getFlashdata('errors') ?? [];
$success = session()->getFlashdata('success');

$report       = $report ?? [];
$sectionsTree = $sectionsTree ?? [];
$admins       = $admins ?? [];
$canEdit      = $canEdit ?? true;

helper('html');

/**
 * Format date (d/m/Y) ou "—"
 */
$fmtDate = function ($value): string {
    if (empty($value)) return '—';
    try {
        return (new DateTime((string)$value))->format('d/m/Y');
    } catch (\Throwable $e) {
        return (string)$value;
    }
};

/**
 * Checkbox UI (icône)
 */
$cb = function (bool $checked): string {
    return $checked
        ? '<i class="fa-regular fa-circle-check"></i>'
        : '<i class="fa-regular fa-circle"></i>';
};

/**
 * Valeurs "old()" -> fallback BDD
 */
$doc = old('doc_status', $report['doc_status'] ?? 'work');                 // work/approved/validated
$mk  = old('modification_kind', $report['modification_kind'] ?? 'creation'); // creation/replace
$st  = old('status', $report['status'] ?? 'brouillon');                   // brouillon/en_relecture/final

/**
 * Champs report utiles
 * (⚠️ garde tes noms BDD ici : validated_by_id / corrected_by_id etc.)
 */
$reportId   = (int)($report['id'] ?? 0);
$title      = (string)($report['title'] ?? '');

$appName    = (string)($report['application_name'] ?? '—');
$appVersion    = (string)($report['application_version'] ?? '—');

$author     = (string)($report['author_name'] ?? '');

$fileId     = (string)($report['file_media_id'] ?? '');
$fileName   = (string)($report['file_name'] ?? '');
$filePath   = (string)($report['file_path'] ?? '');

$createdAt  = $report['created_at'] ?? null;
$updatedAt  = $report['updated_at'] ?? null;

$validatedAt   = $report['validated_at'] ?? null;
$validatedById = (int)($report['validated_by_id'] ?? 0);

$correctedAt   = $report['corrected_at'] ?? null;
$correctedById = (int)($report['corrected_by_id'] ?? 0); // ⚠️ corrected_by_id (pas corrected_by)

/**
 * Petites aides d’affichage
 */
$statusLabel = ($st !== '' ? $st : '—');
$docStatus   = (string)($doc ?: 'work');
$modKind     = (string)($mk ?: 'creation');

$isValidated = ($docStatus === 'validated');

/**
 * Sommaire : racines
 */
$roots = is_array($sectionsTree) ? $sectionsTree : [];

/**
 * UI sections (indentation / titres / badges)
 */
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

/**
 * Affichage du validateur (nom) depuis $admins (si dispo)
 */
$getAdminNameById = function (int $id) use ($admins): string {
    if ($id <= 0) return '—';
    foreach ($admins as $a) {
        $aid = (int)($a['id'] ?? 0);
        if ($aid !== $id) continue;

        $name = trim((string)($a['firstname'] ?? '') . ' ' . (string)($a['lastname'] ?? ''));
        if ($name === '') $name = (string)($a['name'] ?? ('Admin #' . $aid));

        return $name !== '' ? $name : '—';
    }
    return 'Admin #' . $id;
};

/**
 * Historique (info-grid) :
 * - 1ère ligne : doc_version (ou v0.1) + created_at + "Version initiale"
 * - 2ème ligne : v1.0 + validated_at + "Version finale" (si validé)
 */
$docVersion = (string)($report['doc_version'] ?? 'v0.1');
$historyRows = [
    [
        'version' => $docVersion ?: 'v0.1',
        'date'    => $fmtDate($createdAt),
        'comment' => 'Version initiale',
    ],
];

if (!empty($validatedAt)) {
    $historyRows[] = [
        'version' => 'v1.0',
        'date'    => $fmtDate($validatedAt),
        'comment' => 'Version finale',
    ];
}
?>

<?= view('admin/reports/_steps', [
    'step'     => 'write',
    'reportId' => $reportId,
    'canEdit'  => $canEdit ?? true,
]) ?>


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

    <div class="row g-4 mb-4">

        <!-- META / INFOS DOCUMENT -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header fw-semibold">Infos du document</div>
                <div class="card-body">

                    <form method="post" action="<?= site_url('admin/reports/' . $report['id'] . '/update') ?>">
                        <?= csrf_field() ?>

                        <div class="row">
                            <div class="col-md-7 mb-3">
                                <label class="form-label">Titre</label>
                                <input name="title" class="form-control"
                                       value="<?= esc(old('title', $report['title'] ?? '')) ?>">
                            </div>

                            <div class="col-md-5 mb-3">
                                <label class="form-label">Application</label>
                                <input name="application_name" class="form-control"
                                       value="<?= esc(old('application_name', $report['application_name'] ?? '')) ?>">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Auteur</label>
                                <input name="author_name" class="form-control"
                                       value="<?= esc(old('author_name', $report['author_name'] ?? '')) ?>">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Version de l'application</label>
                                <input name="application_version" class="form-control"
                                       value="<?= esc(old('application_version', $report['application_version'] ?? '')) ?>">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Version du document</label>
                                <input name="doc_version" class="form-control"
                                       value="<?= esc(old('doc_version', $report['doc_version'] ?? '')) ?>">
                            </div>

                            <div class="col-md-2 mb-3">
                                <label class="form-label">Statut</label>
                                <?php $st = old('status', $report['status'] ?? 'brouillon'); ?>
                                <select name="status" class="form-select">
                                    <option value="brouillon" <?= $st === 'brouillon' ? 'selected' : '' ?>>Brouillon
                                    </option>
                                    <option value="en relecture" <?= $st === 'en relecture' ? 'selected' : '' ?>>En
                                        relecture
                                    </option>
                                    <option value="final" <?= $st === 'final' ? 'selected' : '' ?>>Final</option>
                                </select>
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

                        <div class="d-flex justify-content-end gap-2 mt-2">
                            <button class="btn btn-primary" type="submit">Enregistrer</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <!-- VALIDATION ADMIN -->
        <div class="col-lg-4">
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
                                            <option value="<?= $id ?>" <?= $validatedById === $id ? 'selected' : '' ?>>
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

    <hr class="mb-4">

    <div class="row">
        <div class="col-12">
            <div class="card mb-2">
                <div class="card-header fw-semibold">Commentaire</div>
                <div class="card-body">

                    <form method="post" action="<?= site_url('admin/reports/' . (int)$report['id'] . '/comments') ?>">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <textarea
                                    name="comments"
                                    rows="5"
                                    class="form-control <?= isset($errors['comments']) ? 'is-invalid' : '' ?>"
                                    placeholder="Ajouter un commentaire ..."
                            ><?= esc(old('comments', $report['comments'] ?? '')) ?></textarea>

                            <?php if (isset($errors['comments'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['comments']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary" type="submit">Enregistrer le commentaire</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <hr class="mb-4">

    <div class="row">
        <?php if ($canEdit): ?>
            <div class="col-md-6">

                <!-- Ajouter une PARTIE (niveau 1) -->
                <div class="card mb-4">
                    <div class="card-header">Ajouter une partie (niveau 1)</div>
                    <div class="card-body">
                        <form method="post" action="<?= site_url('admin/reports/' . $report['id'] . '/sections/root') ?>">
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
            </div>

            <div class="col-md-6">
                <?php if (!empty($roots)): ?>
                    <div class="card mb-4">
                        <div class="card-header">Étapes de rédaction</div>
                        <div class="card-body d-flex flex-column gap-2">

                            <?php foreach ($roots as $index => $r): ?>
                                <div class="d-flex align-items-center justify-content-between gap-2 border rounded p-2">

                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <a class="btn btn-outline-primary btn-sm"
                                           href="<?= site_url('admin/reports/' . $report['id'] . '/sections/' . $r['id'] . '/edit') ?>">
                                            <?= esc($r['code']) ?>. <?= esc($r['title']) ?>
                                        </a>
                                        <!--                                <span class="text-muted small">Position: -->
                                        <?php //= (int)($r['position'] ?? 0) ?><!--</span>-->
                                    </div>

                                    <?php if ($canEdit): ?>
                                        <div class="btn-group">
                                            <form method="post"
                                                  action="<?= site_url('admin/reports/' . $report['id'] . '/sections/' . $r['id'] . '/move-up') ?>">
                                                <?= csrf_field() ?>
                                                <button class="btn btn-sm btn-outline-secondary"
                                                        type="submit" <?= $index === 0 ? 'disabled' : '' ?>>
                                                    ↑
                                                </button>
                                            </form>

                                            <form method="post"
                                                  action="<?= site_url('admin/reports/' . $report['id'] . '/sections/' . $r['id'] . '/move-down') ?>">
                                                <?= csrf_field() ?>
                                                <button class="btn btn-sm btn-outline-secondary"
                                                        type="submit" <?= $index === count($roots) - 1 ? 'disabled' : '' ?>>
                                                    ↓
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

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
