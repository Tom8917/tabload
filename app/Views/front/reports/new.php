<?php
$errors = $errors ?? (session('errors') ?? []);
$success = $success ?? session('success');

// utilisateur connecté pour afficher le nom (non envoyé en POST)
$user = session()->get('user');
$author = trim((string)($user->firstname ?? '') . ' ' . (string)($user->lastname ?? ''));
if ($author === '') $author = 'Utilisateur';
?>

<div class="container-fluid">

    <?= view('front/reports/_steps', ['step' => 'config']) ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Configuration du bilan</h1>
        <a href="<?= site_url('report') ?>" class="btn btn-outline-secondary">
            Retour à la liste
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
        <div class="card-body">
            <form method="post" action="<?= site_url('report') ?>">
                <?= csrf_field() ?>

                <h5 class="mb-3">Informations générales</h5>

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
                               class="form-control"
                               value="<?= esc($author) ?>"
                               disabled>
                        <div class="form-text">Renseigné automatiquement à la création.</div>
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="mb-3">Sections à inclure</h5>
                <div class="text-muted small mb-3">
                    Cochez les blocs de tests que vous souhaitez inclure. Le squelette sera créé automatiquement.
                </div>

                <div class="row">
                    <!-- Test à la cible -->
                    <div class="col-md-3">
                        <div class="border rounded p-3 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="tplTarget"
                                       name="tpl_target_enabled" value="1"
                                    <?= old('tpl_target_enabled', '1') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="tplTarget">
                                    Inclure le test à la cible
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Endurance -->
                    <div class="col-md-3">
                        <div class="border rounded p-3 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="tplEndurance"
                                       name="tpl_endurance_enabled" value="1"
                                    <?= old('tpl_endurance_enabled', '1') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="tplEndurance">
                                    Inclure le test d’endurance
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Limites -->
                    <div class="col-md-3">
                        <div class="border rounded p-3 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="tplLimits"
                                       name="tpl_limits_enabled" value="1"
                                    <?= old('tpl_limits_enabled', '1') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="tplLimits">
                                    Inclure le test aux limites
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Surcharge -->
                    <div class="col-md-3">
                        <div class="border rounded p-3 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="tplOverload"
                                       name="tpl_overload_enabled" value="1"
                                    <?= old('tpl_overload_enabled', '1') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="tplOverload">
                                    Inclure le test de surcharge
                                </label>
                            </div>
                        </div>
                    </div>

                </div>


                <hr class="my-4">

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


                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="<?= site_url('report') ?>" class="btn btn-link">
                        Annuler
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Créer le bilan et générer le squelette
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>
