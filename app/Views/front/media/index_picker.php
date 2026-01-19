<?php
$files     = $files ?? [];
$uploadUrl = $uploadUrl ?? site_url('media/upload');
$deleteUrl = $deleteUrl ?? site_url('media/delete');
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bibliothèque d’images</title>

    <!-- Bootstrap (simple). Remplace par ton CSS local si tu veux -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background:#f8f9fa; }
        .dropzone{
            border:2px dashed #adb5bd;
            border-radius:16px;
            padding:18px;
            background:#fff;
            cursor:pointer;
        }
        .dropzone.dragover{ background:#f1f3f5; border-color:#6c757d; }
        .thumb{ height:110px; width:100%; object-fit:cover; }
        .small-muted{ font-size:.85rem; color:#6c757d; }
    </style>
</head>
<body>

<div class="container py-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="m-0">Bibliothèque d’images</h5>
        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="tryClose()">Fermer</button>
    </div>

    <!-- Upload zone -->
    <div class="dropzone mb-3" id="dropZone">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <div>
                <div><strong>Glisse-dépose</strong> des fichiers ici</div>
                <div class="small-muted">ou clique pour sélectionner (jpg/png/webp/gif/pdf) — 4 Mo max / fichier</div>
            </div>
            <div class="ms-auto d-flex gap-2">
                <input id="fileInput" class="d-none" type="file" multiple
                       accept=".jpg,.jpeg,.png,.webp,.gif,.pdf">
                <button id="btnPick" type="button" class="btn btn-primary btn-sm">Choisir</button>
                <button id="btnUpload" type="button" class="btn btn-success btn-sm" disabled>Uploader</button>
                <button id="btnClear" type="button" class="btn btn-outline-secondary btn-sm" disabled>Vider</button>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 mt-2">
            <div class="small-muted" id="fileCount">0 fichier</div>
            <div class="progress flex-grow-1 d-none" id="progressWrap" style="height:8px;">
                <div class="progress-bar" id="progressBar" style="width:0%"></div>
            </div>
        </div>

        <div class="mt-2 small" id="resultBox"></div>
    </div>

    <!-- Grid -->
    <?php if (empty($files)): ?>
        <div class="alert alert-light border">Aucun fichier.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($files as $f): ?>
                <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                    <div class="card h-100 shadow-sm">
                        <img src="<?= esc($f['url']) ?>" class="card-img-top thumb" alt="<?= esc($f['name']) ?>">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="small text-truncate" title="<?= esc($f['name']) ?>"><?= esc($f['name']) ?></div>
                            </div>

                            <div class="d-grid gap-2 mt-2">
                                <button type="button" class="btn btn-success btn-sm"
                                        onclick="selectImage('<?= esc($f['url']) ?>','<?= esc($f['name']) ?>')">
                                    Utiliser
                                </button>

                                <div class="d-flex gap-2">
                                    <a class="btn btn-outline-secondary btn-sm w-100" href="<?= esc($f['url']) ?>" target="_blank" rel="noopener">
                                        Ouvrir
                                    </a>
                                </div>

                                <?php if (!empty($f['in_db']) && !empty($f['id'])): ?>
                                    <div class="small-muted text-center">en base (#<?= (int)$f['id'] ?>)</div>
                                <?php else: ?>
                                    <div class="small-muted text-center">fichier (hors base)</div>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<script>
    function selectImage(absUrl, name){
        const payload = { type: 'media-select', url: absUrl, name };

        if (window.parent && window.parent !== window) {
            window.parent.postMessage(payload, window.location.origin);
        }
        if (window.opener && !window.opener.closed) {
            window.opener.postMessage(payload, window.location.origin);
        }
        tryClose();
    }
    function tryClose(){ try{ window.close(); }catch(e){} }


    (() => {
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const btnPick = document.getElementById('btnPick');
        const btnUpload = document.getElementById('btnUpload');
        const btnClear = document.getElementById('btnClear');
        const fileCount = document.getElementById('fileCount');
        const resultBox = document.getElementById('resultBox');
        const progressWrap = document.getElementById('progressWrap');
        const progressBar = document.getElementById('progressBar');

        let queue = [];
        const allowedExt = ['jpg','jpeg','png','webp','gif','pdf'];
        const maxSize = 4 * 1024 * 1024;

        const extOf = (name) => (name.split('.').pop() || '').toLowerCase();

        function setUI(){
            fileCount.textContent = `${queue.length} fichier${queue.length>1?'s':''}`;
            btnUpload.disabled = queue.length === 0;
            btnClear.disabled = queue.length === 0;
        }

        function addFiles(files){
            const errs = [];
            let added = 0;

            for (const f of files){
                const ext = extOf(f.name);
                if (!allowedExt.includes(ext)) { errs.push(`${f.name} : extension non supportée`); continue; }
                if (f.size <= 0) { errs.push(`${f.name} : taille invalide`); continue; }
                if (f.size > maxSize) { errs.push(`${f.name} : > 4 Mo`); continue; }
                queue.push(f); added++;
            }

            resultBox.innerHTML = '';
            if (added) resultBox.innerHTML += `<div class="text-success">+ ${added} fichier(s) ajouté(s)</div>`;
            if (errs.length) resultBox.innerHTML += `<div class="text-danger">${errs.map(e=>'• '+e).join('<br>')}</div>`;
            setUI();
        }

        btnPick.addEventListener('click', () => fileInput.click());
        dropZone.addEventListener('click', (e) => {
            if (e.target && (e.target.id === 'btnPick' || e.target.id === 'btnUpload' || e.target.id === 'btnClear')) return;
            fileInput.click();
        });

        fileInput.addEventListener('change', () => {
            if (fileInput.files && fileInput.files.length) addFiles(fileInput.files);
            fileInput.value = '';
        });

        ['dragenter','dragover'].forEach(evt => {
            dropZone.addEventListener(evt, (e) => {
                e.preventDefault(); e.stopPropagation();
                dropZone.classList.add('dragover');
            });
        });
        ['dragleave','drop'].forEach(evt => {
            dropZone.addEventListener(evt, (e) => {
                e.preventDefault(); e.stopPropagation();
                dropZone.classList.remove('dragover');
            });
        });
        dropZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            if (dt && dt.files && dt.files.length) addFiles(dt.files);
        });

        btnClear.addEventListener('click', () => {
            queue = [];
            resultBox.innerHTML = '';
            setUI();
        });

        btnUpload.addEventListener('click', async () => {
            if (!queue.length) return;

            resultBox.innerHTML = '';
            progressWrap.classList.remove('d-none');
            progressBar.style.width = '0%';

            const form = new FormData();
            queue.forEach(f => form.append('files[]', f, f.name));

            try {
                const respText = await new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', "<?= esc($uploadUrl, 'attr') ?>", true);

                    xhr.upload.onprogress = (evt) => {
                        if (evt.lengthComputable) {
                            const pct = Math.round((evt.loaded / evt.total) * 100);
                            progressBar.style.width = pct + '%';
                        }
                    };

                    xhr.onload = () => (xhr.status >= 200 && xhr.status < 400) ? resolve(xhr.responseText) : reject(new Error('HTTP ' + xhr.status));
                    xhr.onerror = () => reject(new Error('Network error'));
                    xhr.send(form);
                });

                resultBox.innerHTML = `<div class="text-success">Upload terminé. Actualisation…</div>`;
                queue = [];
                setUI();
                setTimeout(() => window.location.reload(), 600);

            } catch (err) {
                resultBox.innerHTML = `<div class="text-danger">Erreur upload : ${err.message}</div>`;
            } finally {
                setTimeout(() => {
                    progressWrap.classList.add('d-none');
                    progressBar.style.width = '0%';
                }, 1200);
            }
        });

        setUI();
    })();
</script>

</body>
</html>
