<?php
/** @var array $report */
/** @var array $sectionsTree */
/** @var array $roots */
/** @var array $errors */
/** @var string|null $success */
/** @var bool $canEdit */

$errors       = $errors ?? (session('errors') ?? []);
$success      = $success ?? session('success');
$sectionsTree = $sectionsTree ?? [];
$roots        = $roots ?? $sectionsTree;
$canEdit      = $canEdit ?? false;

$doc         = (string)($report['doc_status'] ?? 'work');
$isValidated = ($doc === 'validated');

$labelMuted = $isValidated ? 'text-muted' : '';
$inputMuted = $isValidated ? 'text-muted' : '';

$canEditUnlocked = ($canEdit && !$isValidated);

$indentClass = function (int $level): string {
    return match (true) {
        $level <= 1 => 'ms-0',
        $level === 2 => 'ms-3',
        default => 'ms-5',
    };
};

$renderNode = function (array $node) use (&$renderNode, $report, $canEditUnlocked, $errors, $indentClass) {

    $level = (int)($node['level'] ?? 1);
    $ms    = $indentClass($level);

    $code  = (string)($node['code'] ?? '');
    $title = (string)($node['title'] ?? '');

    $comp     = (string)($node['compliance_status'] ?? 'non_applicable');
    $showComp = ($comp !== '' && $comp !== 'non_applicable');

    $compBadge = 'bg-secondary';
    if ($comp === 'conforme') $compBadge = 'bg-success';
    elseif ($comp === 'non_conforme') $compBadge = 'bg-danger';
    elseif ($comp === 'partiel') $compBadge = 'bg-warning';

    $nodeId   = (int)($node['id'] ?? 0);
    $reportId = (int)($report['id'] ?? 0);
    ?>
    <li class="mb-3 <?= esc($ms) ?>">
        <div class="d-flex align-items-start gap-1 mb-3">
            <div>
                <?php if ($code !== ''): ?>
                    <strong><?= esc($code) ?></strong>&nbsp;
                <?php endif; ?>

                <?= esc($title) ?>

                <?php if ($showComp): ?>
                    <span class="badge <?= esc($compBadge) ?> ms-2">
                        <?= esc(ucfirst(str_replace('_', ' ', $comp))) ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="ms-2 d-flex flex-wrap gap-2">
                <a href="<?= site_url('report/' . $reportId) ?>#section-<?= $nodeId ?>"
                   class="btn btn-sm btn-outline-primary">
                    <i class="fa-solid fa-eye"></i> Voir
                </a>

                <?php if ($canEditUnlocked): ?>
                    <a href="<?= site_url('report/' . $reportId . '/sections/' . $nodeId . '/edit') ?>"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="fa-solid fa-pen"></i> Modifier
                    </a>

                    <form method="post"
                          action="<?= site_url('report/' . $reportId . '/sections/' . $nodeId . '/delete') ?>"
                          onsubmit="return confirm('Supprimer cette section et toutes ses sous-sections ?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="fa-solid fa-trash"></i> Supprimer
                        </button>
                    </form>

                    <button class="btn btn-sm btn-outline-primary"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#childForm<?= $nodeId ?>">
                        <i class="fa-solid fa-plus"></i> Sous-partie
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($node['content'])): ?>
            <div class="mt-1 small text-muted">
                <?= esc(mb_strimwidth(strip_tags((string)$node['content']), 0, 200, '…', 'UTF-8')) ?>
            </div>
        <?php endif; ?>

        <?php if ($canEditUnlocked): ?>
            <div class="collapse mt-2" id="childForm<?= $nodeId ?>">
                <div class="card card-body">
                    <form method="post"
                          action="<?= site_url('report/' . $reportId . '/sections/' . $nodeId . '/child') ?>">
                        <?= csrf_field() ?>

                        <div class="mb-2">
                            <label class="form-label">Titre de la sous-partie <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="title"
                                   class="form-control <?= isset($errors['title_child_' . $nodeId]) ? 'is-invalid' : '' ?>">
                            <?php if (isset($errors['title_child_' . $nodeId])): ?>
                                <div class="invalid-feedback">
                                    <?= esc($errors['title_child_' . $nodeId]) ?>
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
        'reportId' => (int)($report['id'] ?? 0),
        'canEdit'  => $canEditUnlocked, // ✅ actions steps verrouillées si validé
    ]) ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Rédaction : <?= esc($report['title'] ?? '') ?></h1>
        </div>

        <div class="d-flex gap-2">
            <a href="<?= site_url('report/' . (int)($report['id'] ?? 0)) ?>" class="btn btn-outline-primary">
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

    <?php if ($canEdit): ?>
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Informations du bilan</span>

                <?php if ($isValidated): ?>
                    <span class="badge bg-dark">
                        <i class="fa-solid fa-lock"></i> Document validé
                    </span>
                <?php endif; ?>
            </div>

            <div class="card-body">
                <form method="post" action="<?= site_url('report/' . (int)($report['id'] ?? 0) . '/sections/meta') ?>">
                    <?= csrf_field() ?>

                    <!-- Bloc meta : tout verrouillé si validé, sauf doc_status (info) -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label <?= $isValidated ? 'text-muted' : '' ?>">Titre</label>
                            <input name="title"
                                   class="form-control <?= $isValidated ? 'text-muted' : '' ?> <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                                   value="<?= esc(old('title', $report['title'] ?? '')) ?>"
                                <?= $isValidated ? 'readonly' : '' ?>>
                            <?php if (isset($errors['title'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['title']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label <?= $labelMuted ?>">Application</label>
                            <input name="application_name"
                                   class="form-control <?= $inputMuted ?> <?= isset($errors['application_name']) ? 'is-invalid' : '' ?>"
                                   value="<?= esc(old('application_name', $report['application_name'] ?? '')) ?>"
                                <?= $isValidated ? 'readonly' : '' ?>>
                            <?php if (isset($errors['application_name'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['application_name']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label <?= $labelMuted ?>">Version de l’application</label>
                            <input name="application_version"
                                   class="form-control <?= $inputMuted ?>"
                                   value="<?= esc(old('application_version', $report['application_version'] ?? '')) ?>"
                                <?= $isValidated ? 'readonly' : '' ?>>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label text-muted">Auteur</label>
                            <input type="text"
                                   name="author_name"
                                   class="form-control text-muted"
                                   value="<?= esc(old('author_name', $report['author_name'] ?? '')) ?>"
                                   readonly>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <!-- doc_status = INFO uniquement -->
                        <div class="col-md-2 mb-4 mt-3">
                            <label class="form-label text-muted">Statut du document</label>
                            <?php $docVal = old('doc_status', $report['doc_status'] ?? 'work'); ?>

                            <div class="d-flex flex-wrap gap-3 mt-1 ms-3">
                                <div class="row">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="doc_status"
                                               id="doc_validated"
                                               value="validated" <?= $docVal === 'validated' ? 'checked' : '' ?>
                                               disabled>
                                        <label class="form-check-label" for="doc_validated">Document validé</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="doc_status"
                                               id="doc_approved"
                                               value="approved" <?= $docVal === 'approved' ? 'checked' : '' ?>
                                               disabled>
                                        <label class="form-check-label" for="doc_approved">Document approuvé</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="doc_status"
                                               id="doc_work"
                                               value="work" <?= $docVal === 'work' ? 'checked' : '' ?>
                                               disabled>
                                        <label class="form-check-label" for="doc_work">Document de travail</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- modification_kind : modifiable seulement si non validé -->
                        <div class="col-md-4 mb-4 mt-3">
                            <label class="form-label <?= $labelMuted ?>">Modification par rapport à l’existant</label>
                            <?php $mk = old('modification_kind', $report['modification_kind'] ?? 'creation'); ?>

                            <div class="d-flex flex-wrap mt-1">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="modification_kind"
                                           id="mk_creation" value="creation"
                                        <?= $mk === 'creation' ? 'checked' : '' ?>
                                        <?= $isValidated ? 'disabled' : '' ?>>
                                    <label class="form-check-label" for="mk_creation">Création</label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="modification_kind"
                                           id="mk_replace" value="replace"
                                        <?= $mk === 'replace' ? 'checked' : '' ?>
                                        <?= $isValidated ? 'disabled' : '' ?>>
                                    <label class="form-check-label" for="mk_replace">
                                        Annule et remplace la version précédente
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="mb-3">
                                <label class="form-label text-muted">Statut de rédaction</label>
                                <?php $st = old('status', $report['status'] ?? 'brouillon'); ?>
                                <input type="text"
                                       name="status"
                                       class="form-control text-muted"
                                       value="<?= esc($st) ?>"
                                       readonly>
                            </div>
                        </div>

                        <div class="col-md-3 mb-2">
                            <div class="mb-3">
                                <label class="form-label text-muted">Validé par</label>
                                <input class="form-control text-muted"
                                       value="<?= esc($report['validated_by'] ?? '') ?>" readonly>
                            </div>

                            <div>
                                <label class="form-label text-muted">Date de validation</label>
                                <?php $validatedAt = $report['validated_at'] ?? null; ?>
                                <input type="text" class="form-control text-muted"
                                       value="<?= esc($validatedAt ? date('d/m/Y', strtotime($validatedAt)) : '') ?>"
                                       readonly>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Picker fichier entrant : interactif seulement si non validé -->
                    <?php $fileMediaId = old('file_media_id', $report['file_media_id'] ?? ''); ?>

                    <div class="col-md-6 mb-3">
                        <label class="form-label <?= $isValidated ? 'text-muted' : '' ?>">Fichier de l'entrant</label>

                        <?php if ($isValidated): ?>
                        <input type="hidden" name="file_media_id" id="file_media_id" value="<?= esc($fileMediaId) ?>">
                        <input type="hidden" name="file_media_name" id="file_media_name"
                               value="<?= esc(old('file_media_name', $report['file_name'] ?? '')) ?>">

                            <div class="small" id="pickedFileInfo">
                                <?php if (!empty($fileMediaId)): ?>
                                    <span class="text-muted">Fichier sélectionné : </span>
                                    <strong><?= esc($report['file_name'] ?? ('#' . (int)$fileMediaId)) ?></strong>
                                <?php else: ?>
                                    <span class="text-muted">Aucun fichier sélectionné.</span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="d-flex gap-2 align-items-start">
                                <input type="hidden" name="file_media_id" id="file_media_id"
                                       value="<?= esc($fileMediaId) ?>">
                                <input type="hidden" name="file_media_name" id="file_media_name"
                                       value="<?= esc(old('file_media_name', $report['file_name'] ?? '')) ?>">

                                <button type="button"
                                        class="btn btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#mediaPickerModal">
                                    Choisir un fichier
                                </button>

                                <button type="button"
                                        class="btn btn-outline-danger"
                                        id="btnClearFile"
                                    <?= empty($fileMediaId) ? 'disabled' : '' ?>>
                                    Retirer
                                </button>
                            </div>

                            <div class="mt-2 small" id="pickedFileInfo">
                                <?php if (!empty($fileMediaId)): ?>
                                    <span class="text-muted">Fichier sélectionné : </span>
                                    <strong><?= esc($report['file_name'] ?? ('#' . (int)$fileMediaId)) ?></strong>
                                <?php else: ?>
                                    <span class="text-muted">Aucun fichier sélectionné.</span>
                                <?php endif; ?>
                            </div>

                            <div class="modal fade" id="mediaPickerModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-xl modal-dialog-centered">
                                    <div class="modal-content">

                                        <div class="modal-header">
                                            <h5 class="modal-title">Choisir un fichier</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Fermer"></button>
                                        </div>

                                        <div class="modal-body p-0" style="height: 70vh;">
                                            <iframe
                                                    id="mediaPickerFrame"
                                                    src="<?= site_url('media?picker=1&pick=file&purpose=report_file') ?>"
                                                    style="border:0;width:100%;height:100%;"
                                                    loading="lazy">
                                            </iframe>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <script>
                                (function () {
                                    const inputId = document.getElementById('file_media_id');
                                    const inputName = document.getElementById('file_media_name');
                                    const info = document.getElementById('pickedFileInfo');
                                    const clearBtn = document.getElementById('btnClearFile');

                                    const modalEl = document.getElementById('mediaPickerModal');
                                    const modal = modalEl ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;

                                    function escapeHtml(str) {
                                        return String(str).replace(/[&<>"']/g, m => ({
                                            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
                                        }[m]));
                                    }

                                    function setSelected(media) {
                                        const id = media?.id ? String(media.id) : '';
                                        const name = media?.name ? String(media.name) : (media?.file_name ? String(media.file_name) : '');
                                        const path = media?.path ? String(media.path) : (media?.file_path ? String(media.file_path) : '');

                                        if (inputId) inputId.value = id;
                                        if (inputName) inputName.value = name;

                                        if (!id) {
                                            if (info) info.innerHTML = '<span class="text-muted">Aucun fichier sélectionné.</span>';
                                            clearBtn?.setAttribute('disabled', 'disabled');
                                            return;
                                        }

                                        if (info) {
                                            info.innerHTML = `
                                                <div>
                                                  <span class="text-muted">Fichier :</span>
                                                  <strong>${escapeHtml(name || ('#' + id))}</strong>
                                                </div>
                                                ${path ? `<div class="text-muted small">${escapeHtml(path)}</div>` : ``}
                                            `;
                                        }
                                        clearBtn?.removeAttribute('disabled');
                                    }

                                    clearBtn?.addEventListener('click', () => setSelected(null));

                                    window.addEventListener('message', function (event) {
                                        if (event.origin !== window.location.origin) return;

                                        const data = event.data || {};
                                        if (data.type !== 'MEDIA_PICKED') return;

                                        setSelected(data.media || null);
                                        modal?.hide();
                                    });
                                })();
                            </script>
                        <?php endif; ?>
                    </div>

                    <hr>

                    <?php if (!$isValidated): ?>
                        <button class="btn btn-primary" type="submit">
                            Enregistrer les infos
                        </button>
                    <?php else: ?>
                        <div class="text-muted small">
                            <i class="fa-solid fa-lock"></i>
                            Document validé – modification verrouillée.
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($isValidated): ?>

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

    <?php else: ?>

        <hr class="mt-4 mb-4">

        <?php $comments = trim((string)($report['comments'] ?? '')); ?>
        <?php if ($comments !== ''): ?>
            <div class="card mb-4">
                <div class="card-header fw-semibold text-danger">
                    <h5 class="mb-0"><i class="fa-solid fa-triangle-exclamation"></i> Commentaire</h5>
                </div>
                <div class="card-body">
                    <?= nl2br(esc($comments)) ?>
                </div>
            </div>
            <hr class="mt-4 mb-4">
        <?php endif; ?>

        <div class="row">
            <?php if ($canEditUnlocked): ?>
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">Ajouter une partie (niveau 1)</div>
                        <div class="card-body">
                            <form method="post" action="<?= site_url('report/' . (int)($report['id'] ?? 0) . '/sections/root') ?>">
                                <?= csrf_field() ?>

                                <div class="mb-3">
                                    <label class="form-label">
                                        Titre de la partie <span class="text-danger">*</span>
                                    </label>
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
                                               href="<?= site_url('report/' . (int)($report['id'] ?? 0) . '/sections/' . (int)$r['id'] . '/edit') ?>">
                                                <?= esc($r['code'] ?? '') ?>. <?= esc($r['title'] ?? '') ?>
                                            </a>
                                        </div>

                                        <div class="btn-group">
                                            <form method="post"
                                                  action="<?= site_url('report/' . (int)($report['id'] ?? 0) . '/sections/' . (int)$r['id'] . '/move-up') ?>">
                                                <?= csrf_field() ?>
                                                <button class="btn btn-sm btn-outline-secondary"
                                                        type="submit" <?= $index === 0 ? 'disabled' : '' ?>>
                                                    ↑
                                                </button>
                                            </form>

                                            <form method="post"
                                                  action="<?= site_url('report/' . (int)($report['id'] ?? 0) . '/sections/' . (int)$r['id'] . '/move-down') ?>">
                                                <?= csrf_field() ?>
                                                <button class="btn btn-sm btn-outline-secondary"
                                                        type="submit" <?= $index === count($roots) - 1 ? 'disabled' : '' ?>>
                                                    ↓
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

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

    <?php endif; ?>

</div>
