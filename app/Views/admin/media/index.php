<?php
// Le contrôleur doit passer $files (liste) et $picker (bool) à la vue.
// $picker = true si /admin/media?picker=1 (mode sélection pour modal)
$picker = isset($picker) ? (bool)$picker : false;
?>
<div class="container py-4">
    <?php if(session('message')): ?>
        <div class="alert alert-success"><?= esc(session('message')) ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?= esc(session('error')) ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="m-0"><?= $picker ? 'Sélectionner une image' : 'Bibliothèque d’images' ?></h3>
        <?php if($picker): ?>
            <button class="btn btn-outline-secondary" type="button" onclick="window.close()">Fermer</button>
        <?php endif; ?>
    </div>

    <?php if (!$picker): ?>
        <!-- Formulaire upload (désactivé en mode picker) -->
        <form action="<?= site_url('admin/media/upload') ?>" method="post" enctype="multipart/form-data" class="mb-4">
            <?= csrf_field() ?>
            <div class="input-group">
                <input type="file" name="files[]" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif" multiple required>
                <button class="btn btn-primary" type="submit">Uploader</button>
            </div>
            <small class="text-muted">Formats : jpg, jpeg, png, webp, gif, pdf. Taille max 4 Mo / fichier.</small>
        </form>
    <?php endif; ?>

    <?php if (empty($files)): ?>
        <div class="text-muted">Aucune image dans <code>/public/uploads/media</code>.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($files as $f): ?>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card h-100">
                        <img src="<?= esc($f['url']) ?>" class="card-img-top rounded-top-4">
                        <div class="card-body p-2">
                            <div class="small text-truncate" title="<?= esc($f['name']) ?>"><?= esc($f['name']) ?></div>
                            <div class="d-grid gap-2 mt-2">
                                <?php if ($picker): ?>
                                    <button type="button" class="btn btn-sm btn-success"
                                            onclick="selectImage('<?= esc($f['url']) ?>','<?= esc($f['name']) ?>')">
                                        Utiliser
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick="copyToClipboard('<?= esc($f['url']) ?>')">
                                        Copier l’URL
                                    </button>
                                    <a href="<?= site_url('admin/media/delete/'.rawurlencode($f['name'])) ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Supprimer cette image ?')">
                                        Supprimer
                                    </a>
                                <?php endif; ?>
                                <a class="btn btn-sm btn-outline-secondary" href="<?= esc($f['url']) ?>" target="_blank" rel="noopener">
                                    Ouvrir
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    function copyToClipboard(text){
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(()=> {
                alert('URL copiée dans le presse-papiers.');
            }).catch(()=> {
                prompt('Copiez manuellement l’URL :', text);
            });
        } else {
            prompt('Copiez manuellement l’URL :', text);
        }
    }

    // Mode picker : renvoyer l’URL à l’ouvreur (modal/popup)
    function selectImage(absUrl, name){
        if (window.opener && !window.opener.closed) {
            window.opener.postMessage({ type: 'media-select', url: absUrl, name: name }, '*');
        }
        // si ouvert en iframe/modal (postMessage parent)
        if (window.parent && window.parent !== window) {
            window.parent.postMessage({ type: 'media-select', url: absUrl, name: name }, '*');
        }
        // fermer la fenêtre si c’est un popup
        if (window.close) {
            try { window.close(); } catch(e){}
        }
    }
</script>
