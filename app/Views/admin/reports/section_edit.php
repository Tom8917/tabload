<script>
    const IS_CORRECTOR = <?= json_encode(($user->role ?? '') === 'corrector') ?>;
</script>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                Section <?= $section['code'] ?> – <?= $section['title'] ?>
            </h1>
            <div class="text-muted small">
                Bilan : <?= $report['title'] ?>
                &nbsp;·&nbsp; Application : <?= $report['application_name'] ?>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="<?= site_url('admin/reports/' . $report['id'] . '/sections') ?>"
               class="btn btn-outline-secondary">
                Retour au plan
            </a>

            <?php if (empty($canEdit)): ?>
                <a href="<?= current_url() ?>?edit=1" class="btn btn-primary">
                    Déverrouiller l’édition
                </a>
            <?php else: ?>
                <a href="<?= current_url() ?>" class="btn btn-outline-secondary">
                    Quitter l’édition
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if (!empty($errors) && is_array($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $err): ?>
                    <li><?= $err ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (empty($canEdit)): ?>
        <div class="alert alert-info">
            Mode consultation : le contenu est en lecture seule.
            Cliquez sur <strong>Déverrouiller l’édition</strong> pour modifier.
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
                           value="<?= old('title', $section['title']) ?>"
                        <?= empty($canEdit) ? 'readonly' : '' ?>>
                    <?php if (isset($errors['title'])): ?>
                        <div class="invalid-feedback"><?= $errors['title'] ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contenu</label>
                    <textarea id="contentEditor"
                              name="content"
                              rows="10"
                              class="form-control"
                              <?= empty($canEdit) ? 'readonly' : '' ?>><?= old('content', $section['content']) ?></textarea>
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
                               value="<?= old('period_label', $section['period_label']) ?>"
                            <?= empty($canEdit) ? 'readonly' : '' ?>>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Numéro de période</label>
                        <input type="number"
                               name="period_number"
                               class="form-control"
                               value="<?= old('period_number', $section['period_number']) ?>"
                            <?= empty($canEdit) ? 'readonly' : '' ?>>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Date de début</label>
                        <input type="date"
                               name="start_date"
                               class="form-control"
                               value="<?= old('start_date', $section['start_date']) ?>"
                            <?= empty($canEdit) ? 'readonly' : '' ?>>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Date de fin</label>
                        <input type="date"
                               name="end_date"
                               class="form-control"
                               value="<?= old('end_date', $section['end_date']) ?>"
                            <?= empty($canEdit) ? 'readonly' : '' ?>>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Débit (valeur)</label>
                        <input type="text"
                               name="debit_value"
                               class="form-control"
                               value="<?= old('debit_value', $section['debit_value']) ?>"
                            <?= empty($canEdit) ? 'readonly' : '' ?>>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Conformité</label>
                        <?php $compliance = old('compliance_status', $section['compliance_status']); ?>
                        <select name="compliance_status"
                                class="form-select"
                            <?= empty($canEdit) ? 'disabled' : '' ?>>
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

                        <?php if (empty($canEdit) && !empty($compliance)): ?>
                            <div class="form-text">
                                Valeur actuelle : <strong><?= $compliance ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <?php if (!empty($canEdit)): ?>
                        <button type="submit" class="btn btn-primary">
                            Enregistrer les modifications
                        </button>
                        <a href="<?= site_url('admin/reports/' . $report['id'] . '/sections') ?>"
                           class="btn btn-link">
                            Annuler
                        </a>
                    <?php else: ?>
                        <a href="<?= current_url() ?>?edit=1" class="btn btn-primary">
                            Déverrouiller l’édition
                        </a>
                        <a href="<?= site_url('admin/reports/' . $report['id'] . '/sections') ?>"
                           class="btn btn-link">
                            Retour
                        </a>
                    <?php endif; ?>
                </div>
            </form>

        </div>
    </div>

</div>

<script>
    const CAN_EDIT = <?= empty($canEdit) ? 'false' : 'true' ?>;

    tinymce.init({
        selector: '#contentEditor',
        height: 520,
        menubar: false,
        branding: false,
        plugins: 'link lists image table code autoresize',
        toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link table | mediaLibrary image | code',
        readonly: CAN_EDIT ? 0 : 1,

        // upload images (admin)
        images_upload_url: '<?= site_url('admin/reports/sections/upload-image') ?>',
        images_upload_credentials: true,
        automatic_uploads: true,

        setup: (editor) => {

            // bouton bibliothèque
            editor.ui.registry.addButton('mediaLibrary', {
                text: 'Bibliothèque',
                icon: 'gallery',
                onAction: () => {
                    if (!CAN_EDIT) return;
                    const modalEl = document.getElementById('mediaPickerModal');
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                }
            });

            // format "correcteur rouge"
            editor.on('init', () => {
                editor.formatter.register('correctorRed', {
                    inline: 'span',
                    classes: 'corrector-red'
                });
            });

            if (CAN_EDIT) {
                // 🔴 Quand l'admin tape (y compris remplacement d'une sélection), on force le format rouge
                editor.on('beforeinput', (e) => {
                    // on ne force pas sur delete, etc.
                    if (!e || !e.inputType) return;
                    if (e.inputType.startsWith('delete')) return;

                    // active le format rouge pour l'insertion à venir
                    editor.formatter.apply('correctorRed');
                });

                // 🔴 Quand l'admin colle, on enveloppe en rouge
                editor.on('paste', (e) => {
                    // TinyMCE gère déjà l'insertion, mais on s'assure du rouge
                    setTimeout(() => editor.formatter.apply('correctorRed'), 0);
                });

                // 🔴 Par sécurité, après un undo/redo on ne garde pas le format actif à tort
                editor.on('undo redo', () => {
                    // rien de spécial, mais tu peux choisir de réappliquer si tu veux
                });
            }
        }
    });

    // réception image depuis media (admin)
    window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin) return;
        if (!event.data || event.data.type !== 'MEDIA_SELECTED') return;

        const url = event.data.url;
        if (!url) return;

        const editor = tinymce.get('contentEditor');
        if (editor) {
            // insertion rouge pour admin (optionnel : image non rouge)
            editor.insertContent(`<p><img src="${url}" alt=""></p>`);
        }

        const modalEl = document.getElementById('mediaPickerModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    });
</script>

<!-- Modal Media admin -->
<div class="modal fade" id="mediaPickerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bibliothèque média</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body p-0" style="height: 70vh;">
                <iframe
                        src="<?= site_url('media') ?>?picker=1"
                        style="border:0; width:100%; height:100%;"
                        loading="lazy"></iframe>
            </div>
        </div>
    </div>
</div>

<style>
    .corrector-red { color: #d10000; font-weight: 600; }
</style>
