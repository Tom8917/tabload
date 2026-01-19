<?php
// Le contrôleur doit passer $files (liste) et $picker (bool) à la vue.
// $picker = true si /media?picker=1 (mode sélection pour modal)
$picker = isset($picker) ? (bool)$picker : false;
?>
<div class="container-fluid py-4">
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
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-2">Uploader des fichiers</h5>

                <div id="dropZone"
                     class="border border-2 rounded-4 p-4 text-center"
                     style="border-style:dashed; cursor:pointer;">
                    <div class="mb-2">
                        <strong>Glisse-dépose</strong> tes fichiers ici
                    </div>
                    <div class="text-muted small mb-3">
                        ou clique pour sélectionner (jpg, png, webp, gif, pdf) — 4 Mo max / fichier
                    </div>

                    <input id="fileInput"
                           type="file"
                           name="files[]"
                           class="d-none"
                           accept=".jpg,.jpeg,.png,.webp,.gif,.pdf"
                           multiple>

                    <button id="btnPick" type="button" class="btn btn-primary">
                        Choisir des fichiers
                    </button>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <div class="ms-auto small text-muted align-self-center" id="fileCount">0 fichier</div>
                    <button id="btnClear" type="button" class="btn btn-outline-secondary" disabled>
                        Vider
                    </button>
                    <button id="btnUpload" type="button" class="btn btn-success" disabled>
                        Uploader
                    </button>
                </div>

                <div class="progress mt-3 d-none" id="uploadProgressWrap" style="height: 10px;">
                    <div class="progress-bar" id="uploadProgress" role="progressbar" style="width: 0%"></div>
                </div>

                <div class="mt-3">
                    <div id="uploadResult" class="small"></div>
                </div>
            </div>
        </div>
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
                                    <a href="<?= site_url('media/delete/'.rawurlencode($f['name'])) ?>"
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

    (() => {
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const btnPick = document.getElementById('btnPick');
        const btnUpload = document.getElementById('btnUpload');
        const btnClear = document.getElementById('btnClear');
        const fileCount = document.getElementById('fileCount');
        const resultBox = document.getElementById('uploadResult');
        const progressWrap = document.getElementById('uploadProgressWrap');
        const progressBar = document.getElementById('uploadProgress');

        if (!dropZone || !fileInput) return;

        let queue = [];

        const allowedExt = ['jpg','jpeg','png','webp','gif','pdf'];
        const maxSize = 4 * 1024 * 1024;

        function extOf(name){
            const p = name.split('.');
            return (p.length > 1 ? p.pop() : '').toLowerCase();
        }

        function setUI(){
            fileCount.textContent = `${queue.length} fichier${queue.length>1?'s':''}`;
            btnUpload.disabled = queue.length === 0;
            btnClear.disabled = queue.length === 0;
        }

        function addFiles(files){
            const added = [];
            const errs = [];

            for (const f of files) {
                const ext = extOf(f.name);
                if (!allowedExt.includes(ext)) {
                    errs.push(`${f.name} : extension non supportée`);
                    continue;
                }
                if (f.size <= 0) {
                    errs.push(`${f.name} : taille invalide`);
                    continue;
                }
                if (f.size > maxSize) {
                    errs.push(`${f.name} : > 4 Mo`);
                    continue;
                }
                added.push(f);
            }

            // Ajout à la queue
            queue = queue.concat(added);

            // Message
            resultBox.innerHTML = '';
            if (added.length) {
                resultBox.innerHTML += `<div class="text-success">+ ${added.length} fichier(s) ajouté(s)</div>`;
            }
            if (errs.length) {
                resultBox.innerHTML += `<div class="text-danger">${errs.map(e => `• ${e}`).join('<br>')}</div>`;
            }

            setUI();
        }

        // Click pour ouvrir le picker
        btnPick.addEventListener('click', () => fileInput.click());
        dropZone.addEventListener('click', (e) => {
            // évite double click si on clique sur le bouton
            if (e.target && e.target.id === 'btnPick') return;
            fileInput.click();
        });

        // input change
        fileInput.addEventListener('change', () => {
            if (fileInput.files && fileInput.files.length) {
                addFiles(fileInput.files);
                fileInput.value = ''; // reset
            }
        });

        // Drag UI
        ['dragenter','dragover'].forEach(evt => {
            dropZone.addEventListener(evt, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.add('bg-light');
            });
        });
        ['dragleave','drop'].forEach(evt => {
            dropZone.addEventListener(evt, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.remove('bg-light');
            });
        });

        // Drop
        dropZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            if (dt && dt.files && dt.files.length) {
                addFiles(dt.files);
            }
        });

        // Clear
        btnClear.addEventListener('click', () => {
            queue = [];
            resultBox.innerHTML = '';
            setUI();
        });

        // Upload (AJAX)
        btnUpload.addEventListener('click', async () => {
            if (!queue.length) return;

            resultBox.innerHTML = '';
            progressWrap.classList.remove('d-none');
            progressBar.style.width = '0%';

            const form = new FormData();
            // CSRF (si activé) : tu as csrf_field() côté PHP, mais en AJAX il faut l’envoyer.
            // Si ton CI4 est configuré pour lire le token via cookie, tu peux ignorer.
            // Sinon, ajoute un input hidden dans la page et récupère sa valeur ici.
            //
            // Exemple (si tu ajoutes <input type="hidden" id="csrfName" value="..."> etc):
            // form.append(document.getElementById('csrfName').value, document.getElementById('csrfValue').value);

            queue.forEach(f => form.append('files[]', f, f.name));

            try {
                // Upload avec XHR pour avoir un vrai progress
                const respText = await new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', "<?= site_url('media/upload') ?>", true);

                    xhr.upload.onprogress = (evt) => {
                        if (evt.lengthComputable) {
                            const pct = Math.round((evt.loaded / evt.total) * 100);
                            progressBar.style.width = pct + '%';
                        }
                    };

                    xhr.onload = () => {
                        if (xhr.status >= 200 && xhr.status < 400) resolve(xhr.responseText);
                        else reject(new Error('HTTP ' + xhr.status));
                    };
                    xhr.onerror = () => reject(new Error('Network error'));
                    xhr.send(form);
                });

                // Ton controller redirecte (HTML). En AJAX on va juste dire "OK" et reload la page.
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
                }, 1500);
            }
        });

        setUI();
    })();
</script>
