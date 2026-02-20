<?php
/**
 * admin/index_picker — version robuste (compatible ancien + nouveau controller)
 *
 * Nouveau mode (recommandé) :
 *  - $folders, $files, $breadcrumbs, $currentFolder, $filter, $sort
 *
 * Ancien mode :
 *  - $files = items avec 'url' + 'name'
 */

$isPicker = $isPicker ?? (!empty($_GET['picker']));
$pick = (string)($_GET['pick'] ?? 'image');
$isFilePicker = ($pick === 'file');

$filter = $filter ?? 'all';
$sort   = $sort ?? 'date_desc';

// Nouveau format
$folders = $folders ?? [];
$newFiles = $files ?? []; // si controller nouveau, $files contient file_name/file_path
$currentFolderId = $currentFolder['id'] ?? null;
$breadcrumbs = $breadcrumbs ?? [];

// Ancien format fallback
$legacyFiles = [];
$isLegacy = false;
if (!empty($newFiles) && isset($newFiles[0]) && array_key_exists('url', $newFiles[0])) {
    $isLegacy = true;
    $legacyFiles = $newFiles;
    $folders = []; // pas de dossiers en legacy
}

// Base url
$baseUrl = rtrim(base_url(), '/');

// Upload URL admin (si tu utilises une autre route, change ici)
$uploadUrl = $uploadUrl ?? site_url('admin/media/upload');

function isImageRowPicker(array $f): bool {
    // legacy: url/name
    if (isset($f['url'])) {
        $name = strtolower((string)($f['name'] ?? ''));
        return (bool)preg_match('~\.(jpe?g|png|webp|gif)$~i', $name);
    }
    // new: mime_type/kind
    $mime = strtolower((string)($f['mime_type'] ?? ''));
    $kind = strtolower((string)($f['kind'] ?? ''));
    return $kind === 'image' || str_starts_with($mime, 'image/');
}

function fileUrlPicker(array $f, string $baseUrl): string {
    if (isset($f['url'])) return (string)$f['url'];
    $path = (string)($f['file_path'] ?? '');
    return $baseUrl . '/' . ltrim($path, '/');
}

function fileNamePicker(array $f): string {
    return (string)($f['name'] ?? ($f['file_name'] ?? ''));
}

function currentNavUrlPicker(?int $folderId, string $filter, string $sort): string {
    $u = $folderId ? site_url('admin/media/folder/'.$folderId) : site_url('admin/media');

    $pick = (string)($_GET['pick'] ?? 'image');
    return $u.'?picker=1&pick='.$pick.'&type='.$filter.'&sort='.$sort;
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Médiathèque — Sélection (Admin)</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        .media-toolbar{
            border: 1px solid rgba(0,0,0,.08);
            border-radius: 18px;
            padding: 14px;
            box-shadow: 0 10px 30px rgba(0,0,0,.04);
        }
        .chip{
            border: 1px solid rgba(0,0,0,.12);
            border-radius: 999px;
            padding: 8px 12px;
            font-size: .9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .chip.active{ border-color: rgba(13,110,253,.55); }

        .dropzone{
            border: 2px dashed rgba(0,0,0,.18);
            border-radius: 18px;
            padding: 18px;
            cursor: pointer;
            transition: .15s ease;
        }
        .dropzone.dragover{
            border-color: rgba(13,110,253,.65);
            transform: translateY(-1px);
        }
        .card-soft{
            border: 1px solid rgba(0,0,0,.08);
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(0,0,0,.04);
            overflow: hidden;
        }
        .thumb{
            height: 140px;
            width: 100%;
            object-fit: cover;
        }
        .ellipsis{ white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .dropdown-menu{ z-index: 2000; }
    </style>
</head>
<body>

<div class="container-fluid py-3">

    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
        <div class="h5 m-0">
            <?= $isFilePicker ? 'Sélectionner un fichier' : 'Sélectionner une image' ?>
        </div>
    </div>

    <!-- Breadcrumbs (uniquement si nouveau mode) -->
    <?php if (!$isLegacy): ?>
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb mb-0">
                <?php if (empty($breadcrumbs)): ?>
                    <li class="breadcrumb-item active">Racine</li>
                <?php else: ?>
                    <?php foreach ($breadcrumbs as $i => $bc): ?>
                        <?php
                        $isLast = ($i === count($breadcrumbs)-1);
                        $id = $bc['id'] ?? null;
                        $url = $id ? site_url('admin/media/folder/'.$id) : site_url('admin/media');
                        $url .= '?picker=1&pick=' . esc($pick, 'url') . '&type=' . esc($filter, 'url') . '&sort=' . esc($sort, 'url');
                        ?>
                        <li class="breadcrumb-item <?= $isLast ? 'active' : '' ?>">
                            <?php if ($isLast): ?>
                                <?= esc($bc['name'] ?? 'Racine') ?>
                            <?php else: ?>
                                <a href="<?= esc($url) ?>"><?= esc($bc['name'] ?? 'Racine') ?></a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ol>
        </nav>
    <?php endif; ?>

    <!-- Toolbar -->
    <div class="media-toolbar mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <?php if (!$isLegacy): ?>
                <a class="chip <?= $filter==='all'?'active':'' ?>" href="<?= esc(currentNavUrlPicker($currentFolderId,'all',$sort)) ?>">Tous</a>
                <a class="chip <?= $filter==='image'?'active':'' ?>" href="<?= esc(currentNavUrlPicker($currentFolderId,'image',$sort)) ?>"><i class="fa-solid fa-image"></i> Images</a>
                <a class="chip <?= $filter==='document'?'active':'' ?>" href="<?= esc(currentNavUrlPicker($currentFolderId,'document',$sort)) ?>"><i class="fa-solid fa-file"></i> Documents</a>
                <div class="vr mx-2 d-none d-md-block"></div>
            <?php endif; ?>

            <div class="input-group" style="max-width: 340px;">
                <span class="input-group-text border-0" style="border-radius: 999px 0 0 999px;"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input id="searchInput" type="text" class="form-control border-0"
                       style="border-radius: 999px 999px;"
                       placeholder="Rechercher un nom…">
            </div>

            <?php if (!$isLegacy): ?>
                <div class="ms-auto d-flex gap-2">
                    <select class="form-select rounded-pill" style="max-width:220px" id="sortSelect">
                        <option value="date_desc" <?= $sort==='date_desc'?'selected':'' ?>>Date ↓</option>
                        <option value="date_asc"  <?= $sort==='date_asc'?'selected':'' ?>>Date ↑</option>
                        <option value="name_asc"  <?= $sort==='name_asc'?'selected':'' ?>>Nom A→Z</option>
                        <option value="name_desc" <?= $sort==='name_desc'?'selected':'' ?>>Nom Z→A</option>
                        <option value="size_desc" <?= $sort==='size_desc'?'selected':'' ?>>Taille ↓</option>
                        <option value="size_asc"  <?= $sort==='size_asc'?'selected':'' ?>>Taille ↑</option>
                    </select>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Upload (picker = images only) -->
    <?php if (!$isFilePicker): ?>
        <div class="card-soft mb-3">
            <div class="p-3">
                <div id="dropZone" class="dropzone text-center">
                    <div class="fw-semibold">Glisse-dépose tes images ici</div>
                    <div class="text-muted small mb-2">ou clique pour sélectionner — 4 Mo max / fichier</div>

                    <input id="fileInput" type="file" class="d-none" multiple accept=".jpg,.jpeg,.png,.webp,.gif">
                    <button id="btnPick" type="button" class="btn btn-primary rounded-pill px-4">Choisir des fichiers</button>
                </div>

                <div class="mt-3 d-flex flex-wrap gap-2 align-items-center">
                    <div class="text-muted small" id="fileCount">0 fichier</div>
                    <div class="ms-auto d-flex gap-2">
                        <button id="btnClear" type="button" class="btn btn-outline-secondary rounded-pill" disabled>Vider</button>
                        <button id="btnUpload" type="button" class="btn btn-success rounded-pill" disabled>Uploader</button>
                    </div>
                </div>

                <div class="progress mt-3 d-none" id="uploadProgressWrap" style="height: 10px;">
                    <div class="progress-bar" id="uploadProgress" role="progressbar" style="width: 0%"></div>
                </div>

                <div class="mt-2">
                    <div id="uploadResult" class="small"></div>
                </div>
            </div>
        </div>
    <?php endif ?>

    <!-- Dossiers (nouveau mode seulement) -->
    <?php if (!$isLegacy): ?>
        <div class="d-flex align-items-center mb-2">
            <div class="h6 m-0">Dossiers</div>
            <div class="ms-auto text-muted small"><span id="foldersCount"><?= count($folders ?? []) ?></span> dossier(s)</div>
        </div>

        <div class="row g-3 mb-3" id="foldersGrid">
            <?php if (empty($folders)): ?>
                <div class="col-12"><div class="text-muted">Aucun dossier ici.</div></div>
            <?php else: ?>
                <?php foreach ($folders as $d): ?>
                    <div class="col-12 col-md-6 col-lg-4 col-xl-3 folder-item"
                         data-name="<?= esc(strtolower($d['name'] ?? ''), 'attr') ?>">
                        <div class="card-soft folder-card">
                            <div class="p-3 d-flex align-items-center gap-3">
                                <div style="font-size:30px;"><i class="fa-solid fa-folder-open"></i></div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold ellipsis" title="<?= esc($d['name']) ?>">
                                        <?php
                                        $folderUrl = site_url('admin/media/folder/'.$d['id'])
                                            . '?picker=1&pick=' . esc($pick, 'url')
                                            . '&type=' . esc($filter, 'url')
                                            . '&sort=' . esc($sort, 'url');
                                        ?>
                                        <a class="text-decoration-none" href="<?= esc($folderUrl) ?>">
                                            <?= esc($d['name']) ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Fichiers -->
    <div class="d-flex align-items-center mb-2">
        <div class="h6 m-0">Fichiers</div>
        <div class="ms-auto text-muted small">
            <span id="filesCount"><?= count($newFiles ?? []) ?></span> fichier(s)
        </div>
    </div>

    <?php
    $filesToShow = $isLegacy ? $legacyFiles : $newFiles;
    ?>

    <div class="row g-3" id="filesGrid">
        <?php if (empty($filesToShow)): ?>
            <div class="col-12"><div class="text-muted">Aucun fichier ici.</div></div>
        <?php else: ?>
            <?php foreach ($filesToShow as $f): ?>
                <?php
                $name = fileNamePicker($f);
                $isImg = isImageRowPicker($f);
                $url = fileUrlPicker($f, $baseUrl);
                ?>
                <div class="col-12 col-md-6 col-lg-3 file-item"
                     data-name="<?= esc(strtolower($name), 'attr') ?>">
                    <div class="card-soft file-card h-100">
                        <?php if ($isImg): ?>
                            <img src="<?= esc($url) ?>" class="thumb" alt="<?= esc($name) ?>">
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center" style="height:140px;">
                                <div class="text-muted small">Document</div>
                            </div>
                        <?php endif; ?>

                        <div class="p-3">
                            <div class="fw-semibold ellipsis" title="<?= esc($name) ?>">
                                <?php if (!$isImg): ?>
                                    <i class="fa-solid fa-arrow-up-right-from-square ms-1 small text-muted"></i>
                                    <a href="<?= esc($url) ?>"
                                       target="_blank"
                                       rel="noopener"
                                       class="text-decoration-none">
                                        <?= esc($name) ?>
                                    </a>
                                <?php else: ?>
                                    <?= esc($name) ?>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex gap-2 mt-3">
                                <?php if (!$isFilePicker): ?>
                                    <?php if ($isImg): ?>
                                        <button type="button"
                                                class="btn btn-success btn-sm rounded-pill flex-grow-1"
                                                onclick="selectImage('<?= esc($url) ?>','<?= esc($name) ?>')">
                                            Utiliser
                                        </button>
                                    <?php else: ?>
                                        <button type="button"
                                                class="btn btn-primary btn-sm rounded-pill flex-grow-1"
                                                onclick="selectFile(<?= (int)($f['id'] ?? 0) ?>,'<?= esc($name, 'attr') ?>','<?= esc(($f['file_path'] ?? ''), 'attr') ?>','<?= esc($url, 'attr') ?>')">
                                            Utiliser
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button type="button"
                                            class="btn btn-primary btn-sm rounded-pill flex-grow-1"
                                            onclick="selectFile(<?= (int)($f['id'] ?? 0) ?>,'<?= esc($name, 'attr') ?>','<?= esc(($f['file_path'] ?? ''), 'attr') ?>','<?= esc($url, 'attr') ?>')">
                                        Choisir
                                    </button>
                                <?php endif; ?>

                                <a class="btn btn-outline-secondary btn-sm rounded-pill"
                                   href="<?= esc($url) ?>" target="_blank" rel="noopener">Ouvrir</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    /**
     * MEDIA PICKER (iframe/modal)
     * - Envoie toujours: { type:'MEDIA_PICKED', media:{ id,name,path,url,kind } }
     */
    (function () {
        const TARGET_ORIGIN = window.location.origin;

        function postToParent(payload) {
            console.log('[admin picker] postMessage ->', payload);
            if (window.parent && window.parent !== window) {
                window.parent.postMessage(payload, TARGET_ORIGIN);
            }
            if (window.opener && !window.opener.closed) {
                window.opener.postMessage(payload, TARGET_ORIGIN);
            }
        }

        function tryClose() { try { window.close(); } catch(e) {} }

        window.selectImage = function (absUrl, name) {
            const payload = {
                type: 'MEDIA_PICKED',
                media: { id: null, name: name || '', path: '', url: absUrl || '', kind: 'image' }
            };
            postToParent(payload);
            tryClose();
        };

        window.selectFile = function (id, name, path, url) {
            const payload = {
                type: 'MEDIA_PICKED',
                media: { id: Number(id || 0), name: name || '', path: path || '', url: url || '', kind: 'document' }
            };
            postToParent(payload);
            tryClose();
        };

        // Sort
        (function () {
            const sel = document.getElementById('sortSelect');
            if (!sel) return;

            sel.addEventListener('change', () => {
                const url = new URL(window.location.href);
                url.searchParams.set('sort', sel.value);
                url.searchParams.set('picker', '1');
                url.searchParams.set('pick', "<?= esc($pick, 'js') ?>");
                window.location.href = url.toString();
            });
        })();

        // Search filter
        (function () {
            const input = document.getElementById('searchInput');
            if (!input) return;

            const folders = () => Array.from(document.querySelectorAll('.folder-item'));
            const files   = () => Array.from(document.querySelectorAll('.file-item'));

            function apply(q) {
                const query = (q || '').trim().toLowerCase();
                let fc = 0, fic = 0;

                folders().forEach(el => {
                    const ok = (el.dataset.name || '').includes(query);
                    el.style.display = ok ? '' : 'none';
                    if (ok) fc++;
                });

                files().forEach(el => {
                    const ok = (el.dataset.name || '').includes(query);
                    el.style.display = ok ? '' : 'none';
                    if (ok) fic++;
                });

                const f1 = document.getElementById('foldersCount');
                const f2 = document.getElementById('filesCount');
                if (f1) f1.textContent = String(fc);
                if (f2) f2.textContent = String(fic);
            }

            input.addEventListener('input', () => apply(input.value));
        })();

        // Upload (uniquement pick=image)
        (function () {
            const IS_FILE_PICKER = "<?= esc($pick, 'js') ?>" === 'file';
            if (IS_FILE_PICKER) return;

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
            const maxSize = 4 * 1024 * 1024;
            const allowedExt = ['jpg','jpeg','png','webp','gif','pdf','doc','docx','csv','xls','xlsx'];

            const currentFolderId = "<?= esc((string)$currentFolderId) ?>";

            function extOf(name){
                const p = String(name || '').split('.');
                return (p.length > 1 ? p.pop() : '').toLowerCase();
            }

            function setUI(){
                if (fileCount) fileCount.textContent = `${queue.length} fichier${queue.length>1?'s':''}`;
                if (btnUpload) btnUpload.disabled = queue.length === 0;
                if (btnClear) btnClear.disabled = queue.length === 0;
            }

            function addFiles(files){
                const added = [];
                const errs = [];

                for (const f of files) {
                    const ext = extOf(f.name);
                    if (!allowedExt.includes(ext)) { errs.push(`${f.name} : extension non supportée`); continue; }
                    if (f.size <= 0) { errs.push(`${f.name} : taille invalide`); continue; }
                    if (f.size > maxSize) { errs.push(`${f.name} : > 4 Mo`); continue; }
                    added.push(f);
                }

                queue = queue.concat(added);

                if (resultBox) {
                    resultBox.innerHTML = '';
                    if (added.length) resultBox.innerHTML += `<div class="text-success">+ ${added.length} fichier(s) ajouté(s)</div>`;
                    if (errs.length) resultBox.innerHTML += `<div class="text-danger">${errs.map(e => `• ${e}`).join('<br>')}</div>`;
                }
                setUI();
            }

            if (btnPick) btnPick.addEventListener('click', () => fileInput.click());
            dropZone.addEventListener('click', (e) => {
                const id = e.target && e.target.id;
                if (id === 'btnPick' || id === 'btnUpload' || id === 'btnClear') return;
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

            if (btnClear) btnClear.addEventListener('click', () => {
                queue = [];
                if (resultBox) resultBox.innerHTML = '';
                setUI();
            });

            if (btnUpload) btnUpload.addEventListener('click', async () => {
                if (!queue.length) return;

                if (resultBox) resultBox.innerHTML = '';
                if (progressWrap) progressWrap.classList.remove('d-none');
                if (progressBar) progressBar.style.width = '0%';

                const form = new FormData();
                queue.forEach(f => form.append('files[]', f, f.name));
                form.append('folder_id', currentFolderId);

                form.append("<?= csrf_token() ?>", "<?= csrf_hash() ?>");

                try {
                    const responseText = await new Promise((resolve, reject) => {
                        const xhr = new XMLHttpRequest();
                        const UPLOAD_URL = <?= json_encode($uploadUrl) ?>;
                        xhr.open('POST', UPLOAD_URL, true);

                        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                        xhr.setRequestHeader('Accept', 'application/json');

                        xhr.upload.onprogress = (evt) => {
                            if (evt.lengthComputable && progressBar) {
                                const pct = Math.round((evt.loaded / evt.total) * 100);
                                progressBar.style.width = pct + '%';
                            }
                        };

                        xhr.onload = () => {
                            if (xhr.status >= 200 && xhr.status < 300) return resolve(xhr.responseText);
                            reject(new Error('HTTP ' + xhr.status + ' - ' + (xhr.responseText || '')));
                        };
                        xhr.onerror = () => reject(new Error('Network error'));

                        xhr.send(form);
                    });

// maintenant responseText existe ✅
                    let res = {};
                    try { res = JSON.parse(responseText || '{}'); } catch(e) {}

                    if (!res.ok || res.ok <= 0) {
                        const msg = (res.errors && res.errors.length) ? res.errors.join('<br>') : 'Upload KO';
                        if (resultBox) resultBox.innerHTML = `<div class="text-danger">${msg}</div>`;
                        throw new Error('Upload KO');
                    }

                    if (resultBox) resultBox.innerHTML = `<div class="text-success">Upload terminé. Actualisation…</div>`;
                    queue = [];
                    setUI();
                    setTimeout(() => window.location.reload(), 600);

                } catch (err) {
                    if (resultBox) resultBox.innerHTML = `<div class="text-danger">Erreur upload : ${err.message}</div>`;
                } finally {
                    setTimeout(() => {
                        if (progressWrap) progressWrap.classList.add('d-none');
                        if (progressBar) progressBar.style.width = '0%';
                    }, 1200);
                }
            });

            setUI();
        })();
    })();
</script>

</body>
</html>
