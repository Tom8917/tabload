<?php
$errors  = $errors ?? (session('errors') ?? []);
$success = $success ?? session('success');

$reportId  = (int)($report['id'] ?? 0);
$sectionId = (int)($section['id'] ?? 0);

$baseMediaUrl = site_url('media');
$folderId     = (int)($report['media_folder_id'] ?? 0);

// URL picker (ouvre direct sur le dossier du report si possible)
$qs = 'picker=1';
if ($folderId > 0) {
    $qs .= '&folder=' . $folderId;
}
$mediaPickerUrl = $baseMediaUrl . '?' . $qs;
?>

<div class="container-fluid">

    <?= view('front/reports/_steps', [
        'step'     => 'write',
        'reportId' => $reportId,
        'canEdit'  => true,
    ]) ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                Éditer la section <?= esc($section['code'] ?? '') ?> – <?= esc($section['title'] ?? '') ?>
            </h1>
            <div class="text-muted small">
                Bilan : <?= esc($report['title'] ?? '') ?>
                &nbsp;·&nbsp; Application : <?= esc($report['application_name'] ?? '') ?>
            </div>
        </div>

        <a href="<?= site_url('report/' . $reportId . '/sections') ?>"
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
        <div class="card-header">Informations de la section</div>
        <div class="card-body">
            <form method="post"
                  action="<?= site_url('report/' . $reportId . '/sections/' . $sectionId . '/update') ?>">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label">
                        Titre de la section <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           name="title"
                           class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                           value="<?= esc(old('title', (string)($section['title'] ?? ''))) ?>">
                    <?php if (isset($errors['title'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['title']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contenu</label>
                    <textarea
                            name="content"
                            id="editor"
                            class="form-control"
                            rows="12"
                    ><?= esc(old('content', (string)($section['content'] ?? ''))) ?></textarea>
                </div>

                <div class="mt-4 d-flex gap-2 justify-content-end">
                    <a href="<?= site_url('report/' . $reportId . '/sections') ?>"
                       class="btn btn-link">
                        Annuler
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- Modal Media Picker -->
<div class="modal fade" id="mediaPickerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bibliothèque d’images</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>

            <div class="modal-body p-0" style="height: 75vh;">
                <iframe
                        id="mediaPickerFrame"
                        data-src="<?= esc($mediaPickerUrl, 'attr') ?>"
                        src="<?= esc($mediaPickerUrl, 'attr') ?>"
                        style="width:100%; height:100%; border:0;"
                        loading="lazy"
                ></iframe>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const modalEl = document.getElementById('mediaPickerModal');
        const frame   = document.getElementById('mediaPickerFrame');

        if (modalEl && frame) {
            modalEl.addEventListener('show.bs.modal', () => {
                const url = frame.getAttribute('data-src');
                if (url) frame.setAttribute('src', url);
            });
        }
    })();

    tinymce.init({
        selector: '#editor',
        branding: false,
        promotion: false,

        // ✅ Confort d'édition (au lieu du textarea natif)
        min_height: 520,
        autoresize_bottom_margin: 20,
        resize: true,          // ✅ autorise l'utilisateur à redimensionner l'éditeur (pas le textarea)
        menubar: false,        // (tu peux remettre true si tu veux)

        plugins: [
            'lists', 'link', 'table', 'code', 'autoresize'
        ],

        toolbar: [
            'undo redo | blocks fontsize | bold italic underline | forecolor backcolor |',
            'alignleft aligncenter alignright | bullist numlist | table | link mediapicker | code'
        ].join(' '),

        fontsize_formats: '10px 12px 14px 16px 18px 24px 32px 40px',

        setup: function (editor) {
            const openPicker = () => {
                const modalEl = document.getElementById('mediaPickerModal');
                if (!modalEl || typeof bootstrap === 'undefined') return;
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            };

            editor.ui.registry.addButton('mediapicker', {
                text: 'Image',
                icon: 'image',
                onAction: openPicker
            });

            editor.ui.registry.addMenuItem('mediapicker', {
                text: 'Insérer une image…',
                icon: 'image',
                onAction: openPicker
            });
        }
    });

    // Réception postMessage depuis /media?picker=1
    window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin) return;

        const editor = tinymce.get('editor');
        if (!editor) return;

        const data = event.data || {};

        const closeModal = () => {
            const modalEl = document.getElementById('mediaPickerModal');
            if (!modalEl || typeof bootstrap === 'undefined') return;
            bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        };

        const escAttr = (s) => String(s || '').replaceAll('"', '&quot;');

        if (data.type === 'MEDIA_PICKED' && data.media && data.media.url) {
            const m = data.media;
            const url  = m.url;
            const name = escAttr(m.name);

            if (m.kind === 'document') {
                editor.insertContent(
                    `<p><a href="${url}" target="_blank" rel="noopener">${name || 'Télécharger'}</a></p>`
                );
            } else {
                editor.insertContent(
                    `<img src="${url}" alt="${name}" style="max-width:100%;height:auto;" />`
                );
            }

            closeModal();
            return;
        }

        // compat ancien format
        if (data.type === 'media-select' && data.url) {
            editor.insertContent(
                `<img src="${data.url}" alt="${escAttr(data.name)}" style="max-width:100%;height:auto;" />`
            );
            closeModal();
        }
    });
</script>