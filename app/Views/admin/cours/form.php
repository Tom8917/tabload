<?php
$isEdit = !empty($cours);
$action = $isEdit ? site_url('admin/cours/update/'.$cours['id']) : site_url('admin/cours/store');
$base   = rtrim(base_url(), '/');

// valeur courante (image principale)
$currentImageRel = old('image_url', $cours['image'] ?? '');
$currentImageAbs = $currentImageRel
    ? (str_starts_with($currentImageRel, 'http') ? $currentImageRel : $base.'/'.$currentImageRel)
    : '';
?>
<div class="container py-4">
    <h3 class="mb-3"><?= $isEdit ? 'Modifier le cours' : 'Créer un cours' ?></h3>

    <?php if($errors = session('errors')): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $e): ?><div><?= esc($e) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="<?= $action ?>" method="post">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label">Titre *</label>
            <input type="text" name="title" class="form-control" required
                   value="<?= esc(old('title', $cours['title'] ?? '')) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Slug (URL)</label>
            <input type="text" name="slug" class="form-control"
                   value="<?= esc(old('slug', $cours['slug'] ?? '')) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" rows="3" class="form-control"><?= esc(old('description', $cours['description'] ?? '')) ?></textarea>
        </div>

        <!-- Image principale (choisie uniquement depuis Media) -->
        <div class="mb-3">
            <label class="form-label">Image principale (bibliothèque)</label>
            <div class="input-group">
                <input type="text" name="image_url" id="coursMainImageUrl" class="form-control"
                       placeholder="uploads/media/..."
                       value="<?= esc($currentImageRel) ?>">
                <button type="button"
                        class="btn btn-outline-secondary"
                        data-bs-toggle="modal"
                        data-bs-target="#mediaPickerModal"
                        data-mode="main"
                        onclick="loadMedia()">📁 Choisir</button>
            </div>
            <small class="text-muted">Seules les images de <code>uploads/media/</code> sont acceptées.</small>

            <div class="mt-2">
                <img id="coursMainImagePreview"
                     src="<?= esc($currentImageAbs) ?>"
                     alt=""
                     class="img-fluid rounded"
                     style="max-width:240px; <?= empty($currentImageRel) ? 'display:none;' : '' ?>">
            </div>
        </div>

        <!-- Contenu avec Summernote (fond blanc) -->
        <div class="mb-3">
            <label class="form-label">Contenu</label>
            <textarea id="editorContent" name="content"><?= esc(old('content', $cours['content'] ?? '')) ?></textarea>
            <small class="text-muted d-block mt-1">
                Utilise le bouton <strong>Média</strong> pour insérer une image depuis la bibliothèque (pas d’upload direct).
            </small>
        </div>

        <div class="d-flex gap-2">
            <a href="<?= site_url('admin/cours') ?>" class="btn btn-outline-secondary">Retour</a>
            <button class="btn btn-primary"><?= $isEdit ? 'Mettre à jour' : 'Créer' ?></button>
        </div>
    </form>
</div>

<!-- Modal Media Picker (sert à la fois pour l’image principale et pour l’éditeur) -->
<div class="modal fade" id="mediaPickerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bibliothèque d’images</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div id="mediaGrid" class="row g-3">
                    <!-- images chargées ici -->
                </div>
            </div>
            <div class="modal-footer">
                <small class="text-muted me-auto">Astuce : double-clique une image pour la sélectionner.</small>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Summernote -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>

<!-- Styles: éditeur fond blanc -->
<style>
    .note-editor.note-frame .note-editing-area .note-editable {
        background-color: #fff !important;
        color: #000;
    }
    .note-editor.note-frame .note-codable {
        background-color: #fff !important;
        color: #000;
    }
</style>

<script>
    const baseUrl = '<?= $base ?>';
    let mediaPickerMode = 'main'; // 'main' (image principale) ou 'editor' (insertion dans l’éditeur)

    // Charge la liste des images pour la modal
    async function loadMedia(){
        const grid = document.getElementById('mediaGrid');
        grid.innerHTML = '<div class="text-center py-5">Chargement…</div>';

        try{
            const res = await fetch('<?= site_url('admin/media/list') ?>', { headers: { 'X-Requested-With':'XMLHttpRequest' }});
            if(!res.ok) throw new Error('HTTP '+res.status);
            const data = await res.json();

            if(!data.files || data.files.length === 0){
                grid.innerHTML = '<div class="text-muted">Aucune image dans la bibliothèque.</div>';
                return;
            }

            grid.innerHTML = '';
            data.files.forEach(f => {
                const col = document.createElement('div');
                col.className = 'col-6 col-sm-4 col-md-3 col-lg-3';
                col.innerHTML = `
          <div class="card h-100 media-item" data-rel="${f.rel}" data-url="${f.url}" style="cursor:pointer;">
            <img src="${f.url}" class="card-img-top" alt="">
            <div class="card-body p-2">
              <div class="small text-truncate" title="${f.name}">${f.name}</div>
              <button type="button" class="btn btn-sm btn-primary w-100 mt-2 select-btn">Utiliser</button>
            </div>
          </div>`;
                grid.appendChild(col);
            });

            grid.querySelectorAll('.select-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const card = e.target.closest('.media-item');
                    applySelected(card.dataset.rel, card.dataset.url);
                });
            });

            // double-clic sur carte
            grid.querySelectorAll('.media-item').forEach(card => {
                card.addEventListener('dblclick', () => {
                    applySelected(card.dataset.rel, card.dataset.url);
                });
            });

        }catch(err){
            console.error(err);
            grid.innerHTML = '<div class="text-danger">Erreur lors du chargement des images.</div>';
        }
    }

    // Applique la sélection soit à l’image principale, soit dans l’éditeur
    function applySelected(rel, absUrl){
        const modalEl = document.getElementById('mediaPickerModal');
        const modal   = bootstrap.Modal.getInstance(modalEl);

        if (mediaPickerMode === 'main') {
            // image principale : on remplit le champ + preview
            document.getElementById('coursMainImageUrl').value = rel;
            const img = document.getElementById('coursMainImagePreview');
            img.src = absUrl;
            img.style.display = 'inline-block';
            modal.hide();
        } else {
            // insertion dans l’éditeur : on insère un <img> au caret
            $('#editorContent').summernote('focus');
            $('#editorContent').summernote('pasteHTML', `<img src="${absUrl}" alt="" class="img-fluid"/>`);
            modal.hide();
        }
    }

    // Init Summernote (désactive l’upload direct; ajoute un bouton "Média")
    document.addEventListener('DOMContentLoaded', function(){
        $('#editorContent').summernote({
            placeholder: 'Écris le contenu…',
            tabsize: 2,
            height: 420,
            disableDragAndDrop: true,      // pas de drop upload
            dialogsInBody: true,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                // on enlève 'picture' pour éviter l’upload local
                ['insert', ['link', 'video', 'mediaPickerBtn']],
                ['view', ['fullscreen', 'codeview']]
            ],
            buttons: {
                mediaPickerBtn: function (context) {
                    // bouton custom qui ouvre la modal en mode "editor"
                    const ui = $.summernote.ui;
                    return ui.button({
                        contents: '<i class="note-icon-picture"></i> Média',
                        tooltip: 'Insérer depuis la bibliothèque',
                        click: function () {
                            mediaPickerMode = 'editor';
                            // Ouvre la modal et charge la liste
                            const modalEl = document.getElementById('mediaPickerModal');
                            const modal   = new bootstrap.Modal(modalEl);
                            loadMedia();
                            modal.show();
                        }
                    }).render();
                }
            }
        });

        // Quand on clique sur le bouton "Choisir" de l’image principale, on passe en mode main
        const chooseBtn = document.querySelector('[data-bs-target="#mediaPickerModal"][data-mode="main"]');
        if (chooseBtn) {
            chooseBtn.addEventListener('click', function(){
                mediaPickerMode = 'main';
            });
        }
    });
</script>
