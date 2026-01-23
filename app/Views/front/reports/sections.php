<?php
$errors = $errors ?? (session('errors') ?? []);
$success = $success ?? session('success');
$sectionsTree = $sectionsTree ?? [];
$roots = $roots ?? $sectionsTree;
$canEdit = $canEdit ?? false;

$renderNode = function (array $node) use (&$renderNode, $report, $canEdit, $errors) {

    $level = (int)($node['level'] ?? 1);

    $ms = 'ms-0';
    if ($level === 2) $ms = 'ms-3';
    elseif ($level === 3) $ms = 'ms-5';
    elseif ($level >= 4) $ms = 'ms-5';

    $code = (string)($node['code'] ?? '');
    $title = (string)($node['title'] ?? '');

    $comp = (string)($node['compliance_status'] ?? 'non_applicable');
    $showComp = ($comp !== '' && $comp !== 'non_applicable');

    $compBadge = 'bg-secondary';
    if ($comp === 'conforme') $compBadge = 'bg-success';
    elseif ($comp === 'non_conforme') $compBadge = 'bg-danger';
    elseif ($comp === 'partiel') $compBadge = 'bg-warning';

    ?>
    <li class="mb-3 <?= $ms ?>">
        <div class="d-flex align-items-start gap-1 mb-3">
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
                    <i class="fa-solid fa-eye"></i> Voir
                </a>

                <?php if ($canEdit): ?>
                    <a href="<?= site_url('report/' . $report['id'] . '/sections/' . $node['id'] . '/edit') ?>"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="fa-solid fa-pen"></i> Modifier
                    </a>

                    <form method="post"
                          action="<?= site_url('report/' . $report['id'] . '/sections/' . $node['id'] . '/delete') ?>"
                          onsubmit="return confirm('Supprimer cette section et toutes ses sous-sections ?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="fa-solid fa-trash"></i> Supprimer
                        </button>
                    </form>

                    <button class="btn btn-sm btn-outline-primary"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#childForm<?= (int)$node['id'] ?>">
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
        'step' => 'write',
        'reportId' => $report['id'],
        'canEdit' => $canEdit,
    ]) ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Rédaction : <?= esc($report['title']) ?></h1>
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

    <?php if ($canEdit): ?>
        <div class="card mb-4">
            <div class="card-header">Informations du bilan (enregistrées)</div>
            <div class="card-body">
                <form method="post" action="<?= site_url('report/' . $report['id'] . '/sections/meta') ?>">
                    <?= csrf_field() ?>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Titre</label>
                            <input name="title" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                                   value="<?= esc(old('title', $report['title'] ?? '')) ?>">
                            <?php if (isset($errors['title'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['title']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Application</label>
                            <input name="application_name"
                                   class="form-control <?= isset($errors['application_name']) ? 'is-invalid' : '' ?>"
                                   value="<?= esc(old('application_name', $report['application_name'] ?? '')) ?>">
                            <?php if (isset($errors['application_name'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['application_name']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Version</label>
                            <input name="version" class="form-control"
                                   value="<?= esc(old('version', $report['version'] ?? '')) ?>">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Statut (workflow)</label>
                            <?php $st = old('status', $report['status'] ?? 'brouillon'); ?>
                            <input type="text"
                                   name="status"
                                   class="form-control text-muted"
                                   value="<?= esc($st) ?>"
                                   readonly>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Auteur</label>
                            <input type="text"
                                   name="author_name"
                                   class="form-control text-muted"
                                   value="<?= esc(old('author_name', $report['author_name'] ?? '')) ?>"
                                   readonly>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Validé par</label>
                            <input name="validated_by" class="form-control text-muted"
                                   value="<?= esc(old('validated_by', $report['validated_by'] ?? '')) ?>" readonly>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Date de validation</label>
                            <?php $validatedAt = $report['validated_at'] ?? null; ?>
                            <input type="text" class="form-control text-muted"
                                   value="<?= esc($validatedAt ? date('d/m/Y', strtotime($validatedAt)) : '') ?>"
                                   readonly>
                        </div>
                    </div>

                    <hr>

                    <div class="row">

                        <div class="col-md-6 mb-4 mt-3">
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

                        <div class="col-md-6 mb-4 mt-3">
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

                    <hr>

                    <!-- vue picker pour choisir le fichier de l'entrant -->
                    <?php $fileMediaId = old('file_media_id', $report['file_media_id'] ?? ''); ?>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fichier de l'entrant</label>

                        <div class="d-flex gap-2 align-items-start">
                            <input type="hidden" name="file_media_id" id="file_media_id"
                                   value="<?= esc($fileMediaId) ?>">

                            <input type="hidden" name="file_media_name" id="file_media_name"
                                   value="<?= esc(old('file_media_name', $report['file_media_name'] ?? '')) ?>">

                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#mediaPickerModal">
                                Choisir un fichier
                            </button>

                            <button type="button" class="btn btn-outline-danger"
                                    id="btnClearFile" <?= empty($fileMediaId) ? 'disabled' : '' ?>>
                                Retirer
                            </button>
                        </div>

                        <div class="mt-2 small" id="pickedFileInfo">
                            <?php if (!empty($fileMediaId)): ?>
                                <span class="text-muted">Fichier sélectionné : </span>
                                <strong><?= esc($report['file_name'] ?? ('#' . (int)$fileMediaId)) ?></strong>

                                <?php if (!empty($report['file_path'])): ?>
                                    <div class="text-muted small"><?= esc($report['file_path']) ?></div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Aucun fichier sélectionné.</span>
                            <?php endif; ?>
                        </div>

                        <?php if (isset($errors['file_media_id'])): ?>
                            <div class="text-danger small mt-1"><?= esc($errors['file_media_id']) ?></div>
                        <?php endif; ?>
                    </div>

                    <hr>

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
                            const info = document.getElementById('pickedFileInfo');
                            const clearBtn = document.getElementById('btnClearFile');

                            const modalEl = document.getElementById('mediaPickerModal');
                            const modal = modalEl ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;

                            function setSelected(media) {
                                const id = media?.id ? String(media.id) : '';
                                const name = media?.name ? String(media.name) : '';
                                const path = media?.path ? String(media.path) : '';

                                document.getElementById('file_media_id').value = id;
                                document.getElementById('file_media_name').value = name;

                                if (!id) {
                                    info.innerHTML = '<span class="text-muted">Aucun fichier sélectionné.</span>';
                                    clearBtn?.setAttribute('disabled', 'disabled');
                                    return;
                                }

                                info.innerHTML = `
        <div>
            <span class="text-muted">Fichier :</span>
            <strong>${escapeHtml(name)}</strong>
        </div>
        ${path ? `<div class="text-muted small">${escapeHtml(path)}</div>` : ``}
    `;
                                clearBtn?.removeAttribute('disabled');
                            }

                            function escapeHtml(str) {
                                return String(str).replace(/[&<>"']/g, m => ({
                                    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
                                }[m]));
                            }

                            clearBtn?.addEventListener('click', () => setSelected(null));

                            // Réception depuis l’iframe picker
                            window.addEventListener('message', function (event) {
                                // sécurité simple : même origin
                                if (event.origin !== window.location.origin) return;

                                const data = event.data || {};
                                if (data.type !== 'MEDIA_PICKED') return;

                                setSelected(data.media || null);
                                modal?.hide();
                            });
                        })();
                    </script>


                    <button class="btn btn-primary" type="submit">Enregistrer les infos</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <hr class="mt-4 mb-4">

    <?php $comments = trim((string)($report['comments'] ?? '')); ?>

    <?php if ($comments !== ''): ?>
        <div class="card mb-4">
            <div class="card-header fw-semibold text-danger">
                <h5><i class="fa-solid fa-triangle-exclamation"></i> Commentaire</h5>
            </div>
            <div class="card-body">
                <div>
                    <?= nl2br(esc($comments)) ?>
                </div>
            </div>
        </div>
        <hr class="mt-4 mb-4">
    <?php endif; ?>

    <div class="row">
        <?php if ($canEdit): ?>
            <div class="col-md-6">

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
                                           href="<?= site_url('report/' . $report['id'] . '/sections/' . $r['id'] . '/edit') ?>">
                                            <?= esc($r['code']) ?>. <?= esc($r['title']) ?>
                                        </a>
                                        <!--                                <span class="text-muted small">Position: -->
                                        <?php //= (int)($r['position'] ?? 0) ?><!--</span>-->
                                    </div>

                                    <?php if ($canEdit): ?>
                                        <div class="btn-group">
                                            <form method="post"
                                                  action="<?= site_url('report/' . $report['id'] . '/sections/' . $r['id'] . '/move-up') ?>">
                                                <?= csrf_field() ?>
                                                <button class="btn btn-sm btn-outline-secondary"
                                                        type="submit" <?= $index === 0 ? 'disabled' : '' ?>>
                                                    ↑
                                                </button>
                                            </form>

                                            <form method="post"
                                                  action="<?= site_url('report/' . $report['id'] . '/sections/' . $r['id'] . '/move-down') ?>">
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
