<?php
$filter = $filter ?? 'all';
$sort   = $sort ?? 'date_desc';
$currentFolderId = $currentFolder['id'] ?? null;

$baseUrl = rtrim(base_url(), '/');

function humanSize(int $bytes): string {
    if ($bytes <= 0) return '—';
    $units = ['B','KB','MB','GB','TB'];
    $i = 0; $v = (float)$bytes;
    while ($v >= 1024 && $i < count($units)-1) { $v /= 1024; $i++; }
    return $i === 0 ? (int)$v.' '.$units[$i] : number_format($v, 1, ',', ' ').' '.$units[$i];
}

function isImageRow(array $f): bool {
    $mime = strtolower((string)($f['mime_type'] ?? ''));
    $kind = strtolower((string)($f['kind'] ?? ''));
    return $kind === 'image' || str_starts_with($mime, 'image/');
}

function fileExt(string $name): string {
    $p = pathinfo($name, PATHINFO_EXTENSION);
    return strtolower((string)$p);
}

function fileIcon(string $ext): array {
    return match($ext) {
        'pdf' => ['<img src="'.base_url('assets/icons/pdf.png').'" alt="PDF" width="60">', 'PDF'],
        'doc', 'docx' => ['<img src="'.base_url('assets/icons/word.png').'" alt="WORD" width="60">', 'WORD'],
        'xls', 'xlsx' => ['<img src="'.base_url('assets/icons/excel.png').'" alt="EXCEL" width="60">', 'EXCEL'],
        'ppt', 'pptx' => ['<img src="'.base_url('assets/icons/powerpoint.png').'" alt="POWERPOINT" width="60">', 'POWERPOINT'],
        'zip', 'rar', '7z' => ['<img src="'.base_url('assets/icons/rar.png').'" alt="RAR" width="60">', 'RAR'],
        default => ['<img src="'.base_url('assets/icons/file.png').'" alt="FILE" width="60">', 'FILE'],
    };
}
?>
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
    .chip.active{
        border-color: rgba(13,110,253,.55);
    }

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
    .card-soft.allow-overflow{ overflow: visible; }

    .folder-card:hover, .file-card:hover{
        transform: translateY(-2px);
        box-shadow: 0 14px 34px rgba(0,0,0,.07);
    }
    .folder-card, .file-card{ transition: .15s ease; }

    .thumb{
        height: 170px;
        width: 100%;
        object-fit: cover;
    }

    .file-hero{
        height: 170px;
        display:flex;
        align-items:center;
        justify-content:center;
        flex-direction:column;
        gap: 6px;
    }

    .ellipsis{ white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .tree-wrap{ max-height: 52vh; overflow:auto; }
</style>

<div class="container-fluid">

    <?php if(session('message')): ?>
        <div class="alert alert-success"><?= esc(session('message')) ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?= esc(session('error')) ?></div>
    <?php endif; ?>

    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
        <div>
            <div class="h4 m-0">Médiathèque</div>
            <div class="text-muted small">Dossiers & fichiers — images et documents</div>
        </div>

        <div class="ms-auto d-flex gap-2">
            <button class="btn btn-outline-success rounded-pill px-3" type="button" data-bs-toggle="modal" data-bs-target="#modalFolder">
                <i class="fa-solid fa-plus me-1"></i> Nouveau dossier
            </button>
        </div>
    </div>

    <!-- Upload -->
    <div class="card-soft mb-4">
        <div class="p-3 p-md-4">
            <div id="dropZone" class="dropzone text-center">
                <div class="fw-semibold">Glisse-dépose tes fichiers ici</div>
                <div class="text-muted small mb-3">ou clique pour sélectionner — 4 Mo max / fichier</div>

                <input id="fileInput" type="file" class="d-none" multiple
                       accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
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

            <div class="mt-3">
                <div id="uploadResult" class="small"></div>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="media-toolbar mb-4">
        <div class="d-flex flex-wrap gap-2 align-items-center">

            <a class="chip <?= $filter==='all'?'active':'' ?>"
               href="<?= esc(($currentFolderId ? site_url('media/folder/'.$currentFolderId) : site_url('media')) . '?type=all&sort='.$sort) ?>">
                Tous
            </a>
            <a class="chip <?= $filter==='image'?'active':'' ?>"
               href="<?= esc(($currentFolderId ? site_url('media/folder/'.$currentFolderId) : site_url('media')) . '?type=image&sort='.$sort) ?>">
                <i class="fa-solid fa-image"></i> Images
            </a>
            <a class="chip <?= $filter==='document'?'active':'' ?>"
               href="<?= esc(($currentFolderId ? site_url('media/folder/'.$currentFolderId) : site_url('media')) . '?type=document&sort='.$sort) ?>">
                <i class="fa-solid fa-file"></i> Documents
            </a>

            <div class="vr mx-2 d-none d-md-block"></div>

            <!-- Recherche -->
            <div class="input-group" style="max-width: 340px;">
                <span class="input-group-text border-0" style="border-radius: 999px 0 0 999px;"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input id="searchInput" type="text" class="form-control border-0"
                       style="border-radius: 0 999px 999px 0;"
                       placeholder="Rechercher...">
            </div>

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
        </div>
    </div>

    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0">
            <?php foreach (($breadcrumbs ?? []) as $i => $bc): ?>
                <?php
                $isLast = ($i === count($breadcrumbs)-1);
                $id = $bc['id'] ?? null;
                $url = $id ? site_url('media/folder/'.$id) : site_url('media');
                $url .= '?type='.$filter.'&sort='.$sort;
                ?>
                <li class="breadcrumb-item <?= $isLast ? 'active' : '' ?>">
                    <?php if ($isLast): ?>
                        <?= esc($bc['name'] ?? 'Racine') ?>
                    <?php else: ?>
                        <a href="<?= esc($url) ?>"><?= esc($bc['name'] ?? 'Racine') ?></a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>

    <!-- Dossiers -->
    <div class="d-flex align-items-center mb-2">
        <div class="h5 m-0">Dossiers</div>
        <div class="ms-auto text-muted small"><span id="foldersCount"><?= count($folders ?? []) ?></span> dossier(s)</div>
    </div>

    <div class="row g-3 mb-4" id="foldersGrid">
        <?php if (empty($folders)): ?>
            <div class="col-12">
                <div class="text-muted">Aucun dossier ici.</div>
            </div>
        <?php else: ?>
            <?php foreach ($folders as $d): ?>
                <div class="col-12 col-md-6 col-lg-4 col-xl-3 folder-item"
                     data-name="<?= esc(strtolower($d['name'] ?? ''), 'attr') ?>">
                    <div class="card-soft allow-overflow folder-card">
                        <div class="p-3 d-flex align-items-center gap-3">
                            <div style="font-size:30px;"><i class="fa-solid fa-folder-open"></i></div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold ellipsis" style="text-decoration: underline" title="<?= esc($d['name']) ?>">
                                    <a class="text-decoration-none"
                                       href="<?= site_url('media/folder/'.$d['id'].'?type='.$filter.'&sort='.$sort) ?>">
                                        <?= esc($d['name']) ?>
                                    </a>
                                </div>
                            </div>

                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary rounded-pill" data-bs-toggle="dropdown">⋯</button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <form action="<?= site_url('media/folder/delete/'.$d['id']) ?>" method="post"
                                          onsubmit="return confirm('Supprimer ce dossier ? (doit être vide)')">
                                        <?= csrf_field() ?>
                                        <button class="dropdown-item text-danger" type="submit">Supprimer</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Fichiers -->
    <div class="d-flex align-items-center mb-2">
        <div class="h5 m-0">Fichiers</div>
        <div class="ms-auto text-muted small"><span id="filesCount"><?= count($files ?? []) ?></span> fichier(s)</div>
    </div>

    <div class="row g-3" id="filesGrid">
        <?php if (empty($files)): ?>
            <div class="col-12"><div class="text-muted">Aucun fichier ici.</div></div>
        <?php else: ?>
            <?php foreach ($files as $f): ?>
                <?php
                $url = $baseUrl . '/' . ltrim((string)$f['file_path'], '/');
                $isImg = isImageRow($f);
                $ext = fileExt((string)$f['file_name']);
                [$icon, $label] = fileIcon($ext);
                $safeNameJs = esc(addslashes((string)($f['file_name'] ?? '')));
                ?>
                <div class="col-12 col-md-6 col-lg-3 file-item"
                     data-name="<?= esc(strtolower($f['file_name'] ?? ''), 'attr') ?>">
                    <div class="card-soft allow-overflow file-card h-100">
                        <?php if ($isImg): ?>
                            <img src="<?= esc($url) ?>" class="thumb" alt="<?= esc($f['file_name']) ?>">
                        <?php else: ?>
                            <div class="file-hero">
                                <div style="font-size:42px;"><?= $icon ?></div>
                                <div class="badge border text-secondary"><?= esc($label) ?></div>
                            </div>
                        <?php endif; ?>

                        <div class="p-3">
                            <div class="fw-semibold ellipsis" title="<?= esc($f['file_name']) ?>">
                                <?= esc($f['file_name']) ?>
                            </div>
                            <div class="d-flex justify-content-between text-muted small mt-1">
                                <span><?= esc($f['kind'] ?? '') ?></span>
                                <span><?= humanSize((int)($f['file_size'] ?? 0)) ?></span>
                            </div>

                            <div class="d-flex gap-2 mt-3">
                                <a class="btn btn-outline-secondary btn-sm rounded-pill flex-grow-1"
                                   href="<?= esc($url) ?>" target="_blank" rel="noopener">Ouvrir</a>

                                <button class="btn btn-outline-primary btn-sm rounded-pill"
                                        type="button"
                                        onclick="copyToClipboard('<?= esc($url) ?>')">Copier</button>

                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-toggle="dropdown">⋯</button>
                                    <div class="dropdown-menu dropdown-menu-end">

                                        <button type="button" class="dropdown-item"
                                                onclick="openMoveCopyModal('move', <?= (int)$f['id'] ?>, '<?= $safeNameJs ?>')">
                                            Déplacer…
                                        </button>

                                        <button type="button" class="dropdown-item"
                                                onclick="openMoveCopyModal('copy', <?= (int)$f['id'] ?>, '<?= $safeNameJs ?>')">
                                            Copier…
                                        </button>

                                        <div class="dropdown-divider"></div>

                                        <form action="<?= site_url('media/delete/'.$f['id']) ?>" method="post"
                                              onsubmit="return confirm('Supprimer ce fichier ?')">
                                            <?= csrf_field() ?>
                                            <button class="dropdown-item text-danger" type="submit">Supprimer</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<!-- Modal pour créer un Nouveau dossier -->
<div class="modal fade" id="modalFolder" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" action="<?= site_url('media/folder/create') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="parent_id" value="<?= esc((string)$currentFolderId) ?>">

            <div class="modal-header">
                <h5 class="modal-title">Créer un dossier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>

            <div class="modal-body">
                <label class="form-label">Nom du dossier</label>
                <input type="text" class="form-control" name="name" required maxlength="150"
                       placeholder="Ex: Nouveau dossier">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-success rounded-pill">Créer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal pour Déplacer / Copier -->
<div class="modal fade" id="modalMoveCopy" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <form class="modal-content" id="moveCopyForm" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="target_folder_id" id="targetFolderId" value="">

            <div class="modal-header">
                <h5 class="modal-title" id="moveCopyTitle">Déplacer / Copier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>

            <div class="modal-body">
                <div class="small text-muted mb-2" id="moveCopySubtitle"></div>

                <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" onclick="selectFolder(null)">
                        Racine
                    </button>
                    <span class="small text-muted" id="selectedFolderLabel">Racine sélectionnée</span>
                </div>

                <div id="foldersTree" class="border rounded-3 p-2 tree-wrap">
                    <div class="text-muted small">Chargement des dossiers…</div>
                </div>

                <div class="form-text mt-2">
                    Choisis le dossier cible (arborescence uniquement).
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-primary rounded-pill" id="moveCopySubmit">Valider</button>
            </div>
        </form>
    </div>
</div>

<script>
    /* =========================================================
       1) Tri -> recharge page
       ========================================================= */
    (() => {
        const sel = document.getElementById('sortSelect');
        if (!sel) return;

        sel.addEventListener('change', () => {
            const url = new URL(window.location.href);
            url.searchParams.set('sort', sel.value);
            window.location.href = url.toString();
        });
    })();

    /* =========================================================
       2) Recherche (filtre visuel dossiers + fichiers)
       ========================================================= */
    (() => {
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

    /* =========================================================
       3) Copier URL
       ========================================================= */
    function copyToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(() => alert('URL copiée.'));
        } else {
            prompt('Copiez manuellement :', text);
        }
    }

    /* =========================================================
       4) Upload drag & drop
       ========================================================= */
    (() => {
        const dropZone     = document.getElementById('dropZone');
        const fileInput    = document.getElementById('fileInput');
        const btnPick      = document.getElementById('btnPick');
        const btnUpload    = document.getElementById('btnUpload');
        const btnClear     = document.getElementById('btnClear');
        const fileCount    = document.getElementById('fileCount');
        const resultBox    = document.getElementById('uploadResult');
        const progressWrap = document.getElementById('uploadProgressWrap');
        const progressBar  = document.getElementById('uploadProgress');

        if (!dropZone || !fileInput) return;

        let queue = [];
        const maxSize = 4 * 1024 * 1024;
        const allowedExt = ['jpg','jpeg','png','webp','gif','pdf','doc','docx','xls','xlsx','ppt','pptx'];

        const currentFolderId = "<?= esc((string)$currentFolderId) ?>";

        function extOf(name) {
            const p = name.split('.');
            return (p.length > 1 ? p.pop() : '').toLowerCase();
        }

        function setUI() {
            fileCount.textContent = `${queue.length} fichier${queue.length > 1 ? 's' : ''}`;
            btnUpload.disabled = queue.length === 0;
            btnClear.disabled = queue.length === 0;
        }

        function addFiles(files) {
            const added = [];
            const errs  = [];

            for (const f of files) {
                const ext = extOf(f.name);
                if (!allowedExt.includes(ext)) { errs.push(`${f.name} : extension non supportée`); continue; }
                if (f.size <= 0) { errs.push(`${f.name} : taille invalide`); continue; }
                if (f.size > maxSize) { errs.push(`${f.name} : > 4 Mo`); continue; }
                added.push(f);
            }

            queue = queue.concat(added);

            resultBox.innerHTML = '';
            if (added.length) resultBox.innerHTML += `<div class="text-success">+ ${added.length} fichier(s) ajouté(s)</div>`;
            if (errs.length)  resultBox.innerHTML += `<div class="text-danger">${errs.map(e => `• ${e}`).join('<br>')}</div>`;
            setUI();
        }

        btnPick?.addEventListener('click', () => fileInput.click());

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

        btnClear?.addEventListener('click', () => {
            queue = [];
            resultBox.innerHTML = '';
            setUI();
        });

        btnUpload?.addEventListener('click', async () => {
            if (!queue.length) return;

            resultBox.innerHTML = '';
            progressWrap.classList.remove('d-none');
            progressBar.style.width = '0%';

            const form = new FormData();
            queue.forEach(f => form.append('files[]', f, f.name));
            form.append('folder_id', currentFolderId);

            try {
                await new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', "<?= site_url('media/upload') ?>", true);

                    xhr.upload.onprogress = (evt) => {
                        if (evt.lengthComputable) {
                            const pct = Math.round((evt.loaded / evt.total) * 100);
                            progressBar.style.width = pct + '%';
                        }
                    };

                    xhr.onload  = () => (xhr.status >= 200 && xhr.status < 400) ? resolve(xhr.responseText) : reject(new Error('HTTP ' + xhr.status));
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

    /* =========================================================
       5) Modal Déplacer / Copier (arbre dossiers uniquement)
       ========================================================= */
    let __foldersCache = null;

    function openMoveCopyModal(action, fileId, fileName) {
        const modalEl  = document.getElementById('modalMoveCopy');
        const form     = document.getElementById('moveCopyForm');
        const title    = document.getElementById('moveCopyTitle');
        const subtitle = document.getElementById('moveCopySubtitle');
        const submit   = document.getElementById('moveCopySubmit');

        if (!modalEl || !form || !title || !subtitle || !submit) return;

        title.textContent = (action === 'move') ? 'Déplacer un fichier' : 'Copier un fichier';
        subtitle.textContent = fileName ? ('Fichier : ' + fileName) : '';
        submit.textContent = (action === 'move') ? 'Déplacer' : 'Copier';

        form.action = (action === 'move')
            ? "<?= site_url('media/move') ?>/" + fileId
            : "<?= site_url('media/copy') ?>/" + fileId;

        selectFolder(null);

        loadFoldersTree().then(renderFoldersTree).catch(() => {
            const wrap = document.getElementById('foldersTree');
            if (wrap) wrap.innerHTML = '<div class="text-danger small">Impossible de charger les dossiers.</div>';
        });

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    function selectFolder(folderId) {
        const input = document.getElementById('targetFolderId');
        const label = document.getElementById('selectedFolderLabel');

        if (!input) return;

        input.value = (folderId === null || folderId === undefined) ? '' : String(folderId);

        if (!label) return;
        if (folderId === null || folderId === undefined) {
            label.textContent = 'Racine sélectionnée';
        } else {
            const f = (__foldersCache || []).find(x => x.id === folderId);
            label.textContent = f ? ('Dossier sélectionné : ' + f.name) : ('Dossier #' + folderId);
        }
    }

    async function loadFoldersTree() {
        if (__foldersCache) return __foldersCache;

        const resp = await fetch("<?= site_url('media/folders-tree') ?>", {
            headers: { 'Accept': 'application/json' }
        });

        if (!resp.ok) throw new Error('HTTP ' + resp.status);
        const json = await resp.json();

        __foldersCache = Array.isArray(json.folders) ? json.folders : [];
        return __foldersCache;
    }

    function buildChildrenMap(folders) {
        const map = new Map();
        for (const f of folders) {
            const pid = (f.parent_id === null || f.parent_id === undefined) ? null : f.parent_id;
            if (!map.has(pid)) map.set(pid, []);
            map.get(pid).push(f);
        }
        for (const [k, arr] of map.entries()) {
            arr.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
        }
        return map;
    }

    function renderFoldersTree(folders) {
        const wrap = document.getElementById('foldersTree');
        if (!wrap) return;

        if (!folders || !folders.length) {
            wrap.innerHTML = '<div class="text-muted small">Aucun dossier.</div>';
            return;
        }

        const children = buildChildrenMap(folders);

        function node(pid, level) {
            const list = children.get(pid) || [];
            if (!list.length) return '';

            let html = `<ul class="list-unstyled mb-0" style="padding-left:${level * 14}px">`;
            for (const f of list) {
                const hasKids = (children.get(f.id) || []).length > 0;

                const caret = hasKids
                    ? `<button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2 me-2"
                   data-toggle="kids" data-id="${f.id}">+</button>`
                    : `<span class="me-2" style="display:inline-block;width:28px;"></span>`;

                html += `
        <li class="py-1">
          <div class="d-flex align-items-center">
            ${caret}
            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill"
                    onclick="selectFolder(${f.id})">
              ${escapeHtml(f.name)}
            </button>
          </div>
          <div class="kids mt-1" id="kids-${f.id}" style="display:none;">
            ${node(f.id, level + 1)}
          </div>
        </li>
      `;
            }
            html += `</ul>`;
            return html;
        }

        wrap.innerHTML = node(null, 0);

        wrap.querySelectorAll('[data-toggle="kids"]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id  = btn.getAttribute('data-id');
                const box = document.getElementById('kids-' + id);
                if (!box) return;
                const open = box.style.display !== 'none';
                box.style.display = open ? 'none' : 'block';
                btn.textContent = open ? '+' : '−';
            });
        });
    }

    function escapeHtml(str) {
        return String(str || '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }
</script>
