<!doctype html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="utf-8"/>
    <title>TabLoad</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sticky-th thead th {
            position: sticky;
            top: 0;
            z-index: 2;
        }
        body {
            background-color: white;
        }
        textarea#raw {
            min-height: 260px;
        }
        .group-config-card {
            font-size: 0.8rem;
        }

        .table {
            border-collapse: separate;
            border-spacing: 0 0.25rem;
        }
        .table td,
        .table th {
            border: solid 1px;
        }

        .cell-input,
        .header-input,
        .threshold-input {
            border: none !important;
            box-shadow: none !important;
            background-color: transparent;
            padding-left: 0.1rem;
            padding-right: 0.1rem;
        }

        .cell-green {
            background-color: #00ff00 !important;
            color: #ffffff !important;
        }
        .cell-orange {
            background-color: #fe7f00 !important;
            color: #ffffff !important;
        }
        .cell-red {
            background-color: #dc0000 !important;
            color: #ffffff !important;
        }

        .col-toggle-wrap {
            border-radius: .25rem;
            padding: .15rem .35rem;
            background-color: #f8f9fa;
        }
        .col-toggle-wrap.drag-over {
            outline: 2px dashed #0d6efd;
        }

        .title-cell {
            background-color: #2a6099 !important;
            color: #ffffff !important;
        }
        .header-cell {
            background-color: #ff8000 !important;
            color: #ffffff !important;
        }
        .header-cell .header-input {
            color: #ffffff !important;
        }
    </style>
</head>
<body>
<main class="container-fluid my-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Coller le texte brut</h5>
                </div>
                <div class="card-body">
                    <p class="text-body-secondary mb-3">
                        Séparateurs acceptés : tabulation "->", point-virgule ";" , virgule "," , pipe "|".
                    </p>

                    <div class="mb-3">
                        <textarea id="raw" class="form-control"></textarea>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-sm-2">
                            <label class="form-label">Séparateur</label>
                            <select id="delimiter" class="form-select">
                                <option value="auto" selected>Auto</option>
                                <option value="tab">Tabulation</option>
                                <option value="point-virgule">Point-virgule ;</option>
                                <option value="virgule">Virgule ,</option>
                                <option value="pipe">Pipe |</option>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="hasHeader" checked>
                                <label class="form-check-label" for="hasHeader">Première ligne = en-têtes</label>
                            </div>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <button id="parseBtn" class="btn btn-primary">Transformer →</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="mb-0">Résultat</h5>
                </div>

                <div class="card ms-4 me-4 mt-4">
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-sm-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="excelSep" checked>
                                    <label class="form-check-label" for="excelSep">
                                        Forcer la séparation des colonnes dans Excel
                                    </label>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="d-grid d-sm-flex justify-content-sm-end">
                                    <button id="downloadCsvBtn" class="btn btn-outline-secondary me-sm-2 mb-2 mb-sm-0">
                                        Exporter CSV
                                    </button>
                                    <button id="downloadImgBtn" class="btn btn-outline-success me-sm-2 mb-2 mb-sm-0">
                                        Exporter image (PNG)
                                    </button>
                                    <input type="file" id="importCsvInput" accept=".csv" class="d-none">
                                    <button id="importCsvBtn" class="btn btn-outline-primary mb-2 mb-sm-0">
                                        Importer CSV
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <label for="titleInput" class="form-label">Titre (optionnel)</label>
                                <input type="text" id="titleInput" class="form-control" placeholder="Ex : P1 CIBLE">
                            </div>
                        </div>

                        <div id="tableTools" class="d-flex justify-content-between align-items-center mb-2 mt-4" style="display:none">
                            <div class="d-flex align-items-center gap-2">
                                Ajouter/Supprimer une colonne
                                <button id="colMinus" type="button" class="btn btn-sm btn-outline-secondary" title="Retirer une colonne">–</button>
                                <button id="colPlus"  type="button" class="btn btn-sm btn-outline-secondary" title="Ajouter une colonne">+</button>
                            </div>
                            <div class="small text-body-secondary">
                                <span id="colCount">0</span> colonne(s)
                            </div>
                        </div>

                        <div id="columnToggles" class="mt-3" style="display:none"></div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-9">
                            <div class="table-responsive sticky-th" id="tableWrap"></div>
                        </div>
                        <div class="col-lg-3">
                            <div id="groupConfigs"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

<script>

    // On enveloppe tout dans une fonction anonyme
    (function () {
        // On déclare les variables et les différents éléments HTML (tableau, boutons, ...).
        const $ = s => document.querySelector(s);
        const rawEl = $('#raw');
        const delimEl = $('#delimiter');
        const hdrEl = $('#hasHeader');
        const wrapEl = $('#tableWrap');
        const toolsEl = document.getElementById('tableTools');
        const colCountEl = document.getElementById('colCount');
        const colPlusBtn = document.getElementById('colPlus');
        const colMinusBtn = document.getElementById('colMinus');
        const togglesEl = document.getElementById('columnToggles');
        const groupConfigsEl = document.getElementById('groupConfigs');
        const titleInput = document.getElementById('titleInput');
        const importCsvBtn = document.getElementById('importCsvBtn');
        const importCsvInput = document.getElementById('importCsvInput');

        // On déclare les colonnes actives par défaut (0, 2, 8, 13). On définit les COL_TAUX et COL_PERC95 aux colonnes appropriées (8 et 13).
        const DEFAULT_INCLUDED_INDEXES = [0, 2, 8, 13];
        const COL_TAUX = 8;
        const COL_PERC95 = 13;

        let currentDragIdx = null;


        // Cette fonction regarde les 5 premières lignes et teste tous les séparateurs. Pour chaque séparateur tester, elle compte combien de fois il est passé. Elle choisie ensuite celui qui a le meilleur "score".
        function detectDelimiter(lines) {
            const cands = [
                {k: 'tab', d: '\t'},
                {k: 'point-virgule', d: ';'},
                {k: 'virgule', d: ','},
                {k: 'pipe', d: '|'}
            ];
            const sample = lines.slice(0, Math.min(5, lines.length));
            let best = cands[0], bestScore = -1;
            for (const c of cands) {
                let sum = 0;
                for (const l of sample) sum += (l.split(c.d).length - 1);
                if (sum > bestScore) {
                    bestScore = sum;
                    best = c;
                }
            }
            return best;
        }

        // Cette fonction coupe les lignes selon le séparateur et supprime les éventuelles espaces avant et après.
        function splitLine(line, delim) {
            return line.split(delim).map(s => s.trim());
        }

        // Ici on arrondie à deux chiffres après la virgule, et on remplace les virgules par des points. Ensuite on utilise une méthode qui arrondie vers le bas (*100 -> math.floor -> /100).
        function floor2DecimalsString(value) {
            if (value == null) return '';
            let s = String(value).trim();
            if (s === '') return '';
            s = s.replace(',', '.');
            const n = parseFloat(s);
            if (Number.isNaN(n)) return String(value);
            const floored = Math.floor(n * 100) / 100;
            return floored.toFixed(2);
        }




// Cette fonction fait la séparation en lignes, détecte le séparateur utilisé, fait les colonnes, met la première ligne en en-tête si hasHeader est à true, sinon on nomme les colonnes "Colonne i+1".
        function parseRaw(raw, forcedDelim, hasHeader) {
            raw = raw.replace(/\r\n?/g, '\n').trim();
            const lines = raw.split('\n').map(l => l.trim()).filter(Boolean);
            if (!lines.length) {
                return {headers: [], rows: [], meta: {delimiter: '', guessed: false}};
            }

            let used = forcedDelim;
            let guessed = false;
            if (!used) {
                const g = detectDelimiter(lines);
                used = g.d;
                guessed = true;
            }

            const rows = lines.map(l => splitLine(l, used));
            let headers = [];
            let data = rows;

            if (hasHeader) {
                headers = rows[0].map(h => h || 'Col');
                data = rows.slice(1);
            } else {
                const max = rows.reduce((m, r) => Math.max(m, r.length), 0);
                headers = Array.from({length: max}, (_, i) => 'Colonne ' + (i + 1));
            }

            const maxCols = headers.length;
            const norm = data.map(r => {
                const a = r.slice(0, maxCols);
                while (a.length < maxCols) a.push('');
                return a;
            });

            if (maxCols > COL_TAUX) {
                for (let i = 0; i < norm.length; i++) {
                    norm[i][COL_TAUX] = floor2DecimalsString(norm[i][COL_TAUX]);
                }
            }

            return {headers, rows: norm, meta: {delimiter: used, guessed}};
        }



// Cette petite fonction permet de formater les caractères spéciaux pour sécuriser l'affichage en HTML.
        function escapeHtml(s) {
            return String(s)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#39;');
        }

// cette fonction permet de passer à true les valeurs de DEFAULT_INCLUDE_INDEXES qui est une const ciblant les colonnes 0,2,8 et 13. En gros, ce sont ces colones qui seront cochées par défaut.
        function buildDefaultInclude(headersLength) {
            const inc = new Array(headersLength).fill(false);
            for (const idx of DEFAULT_INCLUDED_INDEXES) {
                if (idx >= 0 && idx < headersLength) {
                    inc[idx] = true;
                }
            }
            return inc;
        }

// res.thresholds low/high verifie que Valeur haute et Valeur basse existent.
        function ensureThresholds(res) {
            const n = res.rows.length;
            if (!Array.isArray(res.thresholds) || res.thresholds.length !== n) {
                res.thresholds = Array.from({length: n}, () => ({low: '', high: ''}));
            }
        }

// helper pour initialiser les décimales Perc95 par groupe
        function ensureGroupDecimals(res) {
            if (!res.groupDecimals) res.groupDecimals = {};
        }

// On lit res.include pour savoir quelles colonnes sont cochées. res.order lit l'ordre des colonnes. On construit ensuite un tableau qui permet d'afficher les colonnes dans l'ordre et affiche colonnes low et high après la colonne perc95, ou tout à la fin si perc95 n'est pas présente.
        function buildDisplayColumns(res) {
            const headers = res.headers;
            let include = res.include;
            if (!Array.isArray(include) || include.length !== headers.length) {
                include = buildDefaultInclude(headers.length);
                res.include = include;
            }

            let order = res.order;
            if (!Array.isArray(order) || order.length !== headers.length) {
                order = headers.map((_, i) => i);
                res.order = order;
            }

            const dataIdx = order.filter(i => include[i]);
            const display = [];
            let percInserted = false;

            for (const idx of dataIdx) {
                display.push({type: 'data', idx});
                if (idx === COL_PERC95) {
                    display.push({type: 'low'});
                    display.push({type: 'high'});
                    percInserted = true;
                }
            }

            if (!percInserted) {
                display.push({type: 'low'});
                display.push({type: 'high'});
            }

            return display;
        }


        function classForGroupStyle(style) {
            if (style === 'green') return ' cell-green';
            if (style === 'orange') return ' cell-orange';
            if (style === 'red') return ' cell-red';
            return '';
        }

// On compare la valeur perc95 avec low et high afin de déterminé automatiquement la couleur de la colonne perc95. On prend en compte la possibilité que low OU high peut être manquant.
        function percClassForValue(valStr, lowStr, highStr) {
            if (valStr == null || valStr === '') return '';
            let vs = String(valStr).replace(',', '.');
            let ls = (lowStr ?? '').toString().replace(',', '.');
            let hs = (highStr ?? '').toString().replace(',', '.');

            const v = parseFloat(vs);
            const low = parseFloat(ls);
            const high = parseFloat(hs);

            const hasLow = !Number.isNaN(low);
            const hasHigh = !Number.isNaN(high);
            if (!hasLow && !hasHigh) return '';

            if (hasLow && hasHigh) {
                if (v <= low) return ' cell-green';
                if (v > high) return ' cell-red';
                return ' cell-orange';
            }

            if (hasLow) {
                return (v <= low) ? ' cell-green' : ' cell-red';
            }

            if (hasHigh) {
                return (v <= high) ? ' cell-green' : ' cell-red';
            }

            return '';
        }

        // récupère le nombre de décimales pour un parcours (ligne r)
        function getPercDecimalsForRow(res, rows, r) {
            if (!res.groupDecimals) return null;
            const key = rows[r][0] || '(vide)';
            const raw = res.groupDecimals[key];
            if (raw === undefined || raw === null || raw === '') return null;
            const n = parseInt(raw, 10);
            if (Number.isNaN(n) || (n !== 2 && n !== 3)) return null; // seulement 2 ou 3
            return n;
        }

        // formate Perc95 selon le nb de décimales choisi
        function formatPerc95(value, decimals) {
            if (value == null) return '';
            let s = String(value).trim();
            if (s === '' || decimals == null) return s;
            s = s.replace(',', '.');
            const n = parseFloat(s);
            if (Number.isNaN(n)) return value;
            return n.toFixed(decimals);
        }




// On récupère les valeurs res.include (colonnes cochées) et res.order (ordre de base des colonnes), et on génère un bloc html affichant le nom de la colonne, la case à cocher, et permet de déplacer la colonne.
        function renderColumnToggles(headers) {
            const res = window.__TABLOAD_LAST;
            if (!res || !headers.length) {
                togglesEl.style.display = 'none';
                togglesEl.innerHTML = '';
                return;
            }

            let include = Array.isArray(res.include) ? res.include : null;
            if (!include || include.length !== headers.length) {
                include = buildDefaultInclude(headers.length);
                res.include = include;
            }

            let order = res.order;
            if (!Array.isArray(order) || order.length !== headers.length) {
                order = headers.map((_, i) => i);
                res.order = order;
            }

            const items = order.map(i => {
                const h = headers[i];
                const checked = include[i] ? 'checked' : '';
                return `
                <div class="col-toggle-wrap d-inline-flex align-items-center me-2 mb-1"
                     draggable="true" data-idx="${i}">
                    <span class="me-1 text-muted" style="cursor:grab;">⋮⋮</span>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input col-toggle" type="checkbox"
                               data-col="${i}" ${checked}>
                        <label class="form-check-label small">${escapeHtml(h)}</label>
                    </div>
                </div>`;
            }).join('');

            togglesEl.style.display = '';
            togglesEl.innerHTML = `
            <div class="small text-body-secondary mb-1">Colonnes à afficher (glisser pour réordonner) :</div>
            ${items}
        `;

            const checks = togglesEl.querySelectorAll('.col-toggle');
            checks.forEach(chk => {
                chk.addEventListener('change', (e) => {
                    const idx = Number(e.target.dataset.col);
                    const res = window.__TABLOAD_LAST;
                    if (!res) return;
                    if (!Array.isArray(res.include) || res.include.length !== res.headers.length) {
                        res.include = buildDefaultInclude(res.headers.length);
                    }
                    res.include[idx] = e.target.checked;
                    renderFromState();
                });
            });

            const wraps = togglesEl.querySelectorAll('.col-toggle-wrap');
            wraps.forEach(wrap => {
                wrap.addEventListener('dragstart', (e) => {
                    currentDragIdx = Number(wrap.dataset.idx);
                    e.dataTransfer.effectAllowed = 'move';
                });
                wrap.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    wrap.classList.add('drag-over');
                    e.dataTransfer.dropEffect = 'move';
                });
                wrap.addEventListener('dragleave', () => {
                    wrap.classList.remove('drag-over');
                });
                wrap.addEventListener('drop', (e) => {
                    e.preventDefault();
                    wrap.classList.remove('drag-over');
                    if (currentDragIdx === null) return;
                    const targetIdx = Number(wrap.dataset.idx);
                    const res = window.__TABLOAD_LAST;
                    if (!res || !Array.isArray(res.order)) return;
                    const order = res.order;
                    const fromPos = order.indexOf(currentDragIdx);
                    const toPos = order.indexOf(targetIdx);
                    if (fromPos === -1 || toPos === -1 || fromPos === toPos) {
                        currentDragIdx = null;
                        return;
                    }
                    order.splice(toPos, 0, order.splice(fromPos, 1)[0]);
                    currentDragIdx = null;
                    renderFromState();
                });
            });
        }



// Ici on part de la colonne 0, on analyse toute la colonne 0 (parcours utilisateur), et on affiche une interface permettant de modifier la couleur du taux d'erreur (COL_TAUX : col.8). Lire la colonne 0 permet de donner un label au select de modification de couleur du taux d'erreur correspondant.
        function renderGroupConfigs(headers, rows) {
            const res = window.__TABLOAD_LAST;
            if (!res || !rows.length) {
                groupConfigsEl.innerHTML = '';
                return;
            }
            if (!res.groupStyles) res.groupStyles = {};
            ensureGroupDecimals(res);

            const keysSet = new Set();
            rows.forEach(r => {
                const k = r[0] || '(vide)';
                keysSet.add(k);
            });
            const keys = Array.from(keysSet);

            if (!keys.length) {
                groupConfigsEl.innerHTML = '';
                return;
            }

            let html = `
            <div class="small fw-semibold mb-2">
                Taux d'erreur (col. ${COL_TAUX}) & décimales Perc95 par parcours
            </div>
            <div class="d-flex flex-column gap-2">
        `;

            keys.forEach(key => {
                const val = res.groupStyles[key] || '';
                const decVal = (res.groupDecimals && res.groupDecimals[key] !== undefined)
                    ? String(res.groupDecimals[key])
                    : '';
                html += `
                <div class="card border-0 border-start border-4 shadow-sm group-config-card">
                    <div class="card-body py-2 px-2">
                        <div class="small text-truncate mb-1" title="${escapeHtml(key)}">
                            ${escapeHtml(key)}
                        </div>
                        <div class="d-flex flex-column gap-1">
                            <select class="form-select form-select-sm group-color" data-key="${key}">
                                <option value="">Couleur taux (défaut)</option>
                                <option value="green" ${val === 'green' ? 'selected' : ''}>OK (vert)</option>
                                <option value="orange" ${val === 'orange' ? 'selected' : ''}>À surveiller (orange)</option>
                                <option value="red" ${val === 'red' ? 'selected' : ''}>Critique (rouge)</option>
                            </select>
                            <select class="form-select form-select-sm group-decimals" data-key="${key}">
                                <option value="">Décimales Perc95 (défaut)</option>
                                <option value="2" ${decVal === '2' ? 'selected' : ''}>2 décimales</option>
                                <option value="3" ${decVal === '3' ? 'selected' : ''}>3 décimales</option>
                            </select>
                        </div>
                    </div>
                </div>`;
            });

            html += '</div>';
            groupConfigsEl.innerHTML = html;

            const selects = groupConfigsEl.querySelectorAll('.group-color');
            selects.forEach(sel => {
                sel.addEventListener('change', (e) => {
                    const key = e.target.dataset.key;
                    const res = window.__TABLOAD_LAST;
                    if (!res) return;
                    if (!res.groupStyles) res.groupStyles = {};
                    res.groupStyles[key] = e.target.value || '';
                    renderFromState();
                });
            });

            const decSelects = groupConfigsEl.querySelectorAll('.group-decimals');
            decSelects.forEach(sel => {
                sel.addEventListener('change', (e) => {
                    const key = e.target.dataset.key;
                    const res = window.__TABLOAD_LAST;
                    if (!res) return;
                    ensureGroupDecimals(res);
                    const v = e.target.value;
                    if (v === '') {
                        delete res.groupDecimals[key];
                    } else {
                        res.groupDecimals[key] = v;
                    }
                    renderFromState();
                });
            });
        }









// Rendu du tableau : on verifie qu'on a toutes les données (res.include, res.order, res.thresholds, ...), on construit displayCols pour l'ordre d'affichage, on créer des fusion de cases pour col 0 et 8, on fabrique le tableau avec son body, on met en place des inputs permettant de modifier chaque case du tableau, et enfin on utilise tous les évènements déclarés (ex: valeurs basse / haute, l'arrondi décimale, les couleurs, ...).
        function renderTable(headers, rows) {
            if (!headers.length) {
                wrapEl.innerHTML = '<p class="text-body-secondary mb-0">Aucun contenu.</p>';
                toolsEl.style.display = 'none';
                togglesEl.style.display = 'none';
                groupConfigsEl.innerHTML = '';
                return;
            }

            const res = window.__TABLOAD_LAST || {};
            if (!Array.isArray(res.include) || res.include.length !== headers.length) {
                res.include = buildDefaultInclude(headers.length);
            }
            if (!Array.isArray(res.order) || res.order.length !== headers.length) {
                res.order = headers.map((_, i) => i);
            }

            ensureThresholds(res);
            ensureGroupDecimals(res);

            const displayCols = buildDisplayColumns(res);
            if (!displayCols.length) {
                wrapEl.innerHTML = '<p class="text-body-secondary mb-0">Aucune colonne sélectionnée.</p>';
                toolsEl.style.display = '';
                colCountEl.textContent = String(headers.length);
                renderColumnToggles(headers);
                renderGroupConfigs(headers, rows);
                return;
            }

            const n = rows.length;

            const firstSpan = new Array(n).fill(0);
            const firstSkip = new Array(n).fill(false);

            if (headers.length > 0) {
                let i = 0;
                while (i < n) {
                    const start = i;
                    const val = rows[i][0];
                    let j = i + 1;
                    while (j < n && rows[j][0] === val) j++;
                    const span = j - start;
                    if (span > 1) {
                        firstSpan[start] = span;
                        for (let k = start + 1; k < j; k++) {
                            firstSkip[k] = true;
                        }
                    }
                    i = j;
                }
            }

            const tauxIndex = (headers.length > COL_TAUX) ? COL_TAUX : -1;
            const tauxSpan = new Array(n).fill(0);
            const tauxSkip = new Array(n).fill(false);

            if (tauxIndex !== -1) {
                let i = 0;
                while (i < n) {
                    const start = i;
                    const val = rows[i][0];
                    let j = i + 1;
                    while (j < n && rows[j][0] === val) j++;
                    const span = j - start;

                    let master = -1;
                    for (let k = start; k < j; k++) {
                        if (rows[k][1] === 'Actions') {
                            master = k;
                            break;
                        }
                    }
                    if (master !== -1 && span > 0) {
                        tauxSpan[master] = span;
                        for (let k = start; k < j; k++) {
                            if (k !== master) tauxSkip[k] = true;
                        }
                    }
                    i = j;
                }
            }

            const hasTempsGroup = displayCols.some(c => c.type === 'data' && c.idx === COL_PERC95);
            const colspanTotal = displayCols.length;
            const title = (res.title || '').trim();

            let titleRow = '';
            if (title !== '') {
                titleRow = `
                <tr>
                    <th colspan="${colspanTotal}" class="text-center align-middle fw-bold title-cell">
                        ${escapeHtml(title)}
                    </th>
                </tr>`;
            }

            let firstHeaderRow = '<tr>';
            let secondHeaderRow = hasTempsGroup ? '<tr>' : '';

            if (hasTempsGroup) {
                let tempsDone = false;
                for (let i = 0; i < displayCols.length; i++) {
                    const col = displayCols[i];
                    if (col.type === 'data') {
                        const ci = col.idx;
                        if (ci === COL_PERC95 && !tempsDone) {
                            firstHeaderRow += `
                            <th colspan="3" class="text-center align-middle header-cell">Temps de réponse</th>`;
                            tempsDone = true;
                        } else if (ci !== COL_PERC95) {
                            firstHeaderRow += `
                            <th class="text-nowrap align-middle header-cell" rowspan="2">
                                <input type="text" value="${escapeHtml(headers[ci] ?? '')}"
                                       data-col="${ci}"
                                       class="form-control form-control-sm border-0 p-0 fw-semibold header-input"
                                       style="min-width:120px; background-color:transparent;">
                            </th>`;
                        }
                    }
                }

                for (let i = 0; i < displayCols.length; i++) {
                    const col = displayCols[i];
                    if (col.type === 'data' && col.idx === COL_PERC95) {
                        const ci = col.idx;
                        secondHeaderRow += `
                        <th class="text-nowrap align-middle header-cell">
                            <input type="text" value="${escapeHtml(headers[ci] ?? '')}"
                                   data-col="${ci}"
                                   class="form-control form-control-sm border-0 p-0 fw-semibold header-input"
                                   style="min-width:120px; background-color:transparent;">
                        </th>`;
                    } else if (col.type === 'low') {
                        secondHeaderRow += `<th class="text-nowrap align-middle header-cell">Valeur basse</th>`;
                    } else if (col.type === 'high') {
                        secondHeaderRow += `<th class="text-nowrap align-middle header-cell">Valeur haute</th>`;
                    }
                }

                firstHeaderRow += '</tr>';
                secondHeaderRow += '</tr>';
            } else {
                for (let i = 0; i < displayCols.length; i++) {
                    const col = displayCols[i];
                    if (col.type === 'data') {
                        const ci = col.idx;
                        firstHeaderRow += `
                        <th class="text-nowrap align-middle header-cell">
                            <input type="text" value="${escapeHtml(headers[ci] ?? '')}"
                                   data-col="${ci}"
                                   class="form-control form-control-sm border-0 p-0 fw-semibold header-input"
                                   style="min-width:120px; background-color:transparent;">
                        </th>`;
                    } else if (col.type === 'low') {
                        firstHeaderRow += `<th class="text-nowrap align-middle header-cell">Valeur basse</th>`;
                    } else if (col.type === 'high') {
                        firstHeaderRow += `<th class="text-nowrap align-middle header-cell">Valeur haute</th>`;
                    }
                }
                firstHeaderRow += '</tr>';
            }

            const thead = `<thead>${titleRow}${firstHeaderRow}${hasTempsGroup ? secondHeaderRow : ''}</thead>`;

            let tbody = '<tbody>';

            for (let r = 0; r < n; r++) {
                const thVal = res.thresholds[r] || {low: '', high: ''};
                tbody += '<tr>';

                for (const col of displayCols) {
                    if (col.type === 'data') {
                        const ci = col.idx;
                        let rowspanAttr = '';
                        let skip = false;

                        if (ci === 0) {
                            if (firstSkip[r]) {
                                skip = true;
                            } else if (firstSpan[r] > 1) {
                                rowspanAttr = ` rowspan="${firstSpan[r]}"`;
                            }
                        }

                        if (!skip && ci === tauxIndex && tauxIndex !== -1) {
                            if (tauxSkip[r]) {
                                skip = true;
                            } else if (tauxSpan[r] > 1) {
                                rowspanAttr = ` rowspan="${tauxSpan[r]}"`;
                            }
                        }

                        if (skip) continue;

                        let extraClass = '';

                        if (ci === COL_TAUX) {
                            const key = rows[r][0] || '(vide)';
                            const style = (res.groupStyles && res.groupStyles[key]) || '';
                            extraClass += classForGroupStyle(style);
                        }

                        if (ci === COL_PERC95 && headers.length > COL_PERC95) {
                            extraClass += percClassForValue(rows[r][ci], thVal.low, thVal.high);
                        }

                        let val = rows[r][ci] ?? '';

                        if (ci === COL_PERC95) {
                            const dec = getPercDecimalsForRow(res, rows, r);
                            if (dec != null) {
                                const formatted = formatPerc95(val, dec);
                                rows[r][ci] = formatted;
                                val = formatted;
                            }
                        }

                        tbody += `
                        <td class="text-nowrap align-middle${extraClass}"${rowspanAttr}>
                            <input type="text"
                                   class="form-control form-control-sm cell-input"
                                   data-row="${r}"
                                   data-col="${ci}"
                                   value="${escapeHtml(val)}">
                        </td>`;
                    } else if (col.type === 'low' || col.type === 'high') {
                        const v = col.type === 'low' ? thVal.low : thVal.high;
                        tbody += `
                        <td class="align-middle">
                            <input type="text"
                                   class="form-control form-control-sm threshold-input"
                                   data-row="${r}"
                                   data-which="${col.type}"
                                   value="${escapeHtml(v)}">
                        </td>`;
                    }
                }

                tbody += '</tr>';
            }

            tbody += '</tbody>';

            wrapEl.innerHTML = `<table class="table table-sm align-middle table-borderless">${thead}${tbody}</table>`;

            toolsEl.style.display = '';
            colCountEl.textContent = String(headers.length);

            const headerInputs = wrapEl.querySelectorAll('.header-input');
            headerInputs.forEach(inp => {
                inp.addEventListener('input', (e) => {
                    const idx = Number(e.target.dataset.col);
                    const res = window.__TABLOAD_LAST;
                    if (!res) return;
                    res.headers[idx] = e.target.value || `Colonne ${idx+1}`;
                    renderColumnToggles(res.headers);
                });
            });

            const cellInputs = wrapEl.querySelectorAll('.cell-input');
            cellInputs.forEach(inp => {
                inp.addEventListener('input', (e) => {
                    const r = Number(e.target.dataset.row);
                    const c = Number(e.target.dataset.col);
                    const res = window.__TABLOAD_LAST;
                    if (!res || !res.rows[r]) return;
                    res.rows[r][c] = e.target.value;
                });

                inp.addEventListener('blur', (e) => {
                    const r = Number(e.target.dataset.row);
                    const c = Number(e.target.dataset.col);
                    const res = window.__TABLOAD_LAST;
                    if (!res || !res.rows[r]) return;

                    let val = res.rows[r][c];

                    if (c === COL_TAUX) {
                        val = floor2DecimalsString(val);
                        res.rows[r][c] = val;
                    }

                    if (c === COL_PERC95) {
                        const dec = getPercDecimalsForRow(res, res.rows, r);
                        if (dec != null) {
                            val = formatPerc95(val, dec);
                            res.rows[r][c] = val;
                        }
                    }

                    renderFromState();
                });
            });

            const thInputs = wrapEl.querySelectorAll('.threshold-input');
            thInputs.forEach(inp => {
                inp.addEventListener('input', (e) => {
                    const r = Number(e.target.dataset.row);
                    const which = e.target.dataset.which;
                    const res = window.__TABLOAD_LAST;
                    if (!res) return;
                    ensureThresholds(res);
                    if (!res.thresholds[r]) res.thresholds[r] = {low: '', high: ''};
                    res.thresholds[r][which] = e.target.value;
                });

                inp.addEventListener('blur', () => {
                    renderFromState();
                });
            });

            renderColumnToggles(headers);
            renderGroupConfigs(headers, rows);
        }






// Permet de relancer le rendu du tableau.
        function renderFromState() {
            const res = window.__TABLOAD_LAST;
            if (!res) return;
            renderTable(res.headers, res.rows);
        }



// Ces fonctions permettent l'ajout et la suppression de colonne. L'ajout créé une colonne avec le nom Colonne i+1, se met dans l'ordre automatiquement. La suppression supprime la dernière colonne du tableau (toutes les valeurs concernées), et remet à jour le tableau automatiquement pour l'ordre.
        // Ajout :
        function addColumn(){
            const res = window.__TABLOAD_LAST;
            if (!res) return;
            const idxNew = res.headers.length;
            res.headers.push(`Colonne ${idxNew+1}`);
            res.rows.forEach(r => r.push(''));

            if (!Array.isArray(res.include) || res.include.length !== res.headers.length) {
                res.include = buildDefaultInclude(res.headers.length);
            } else {
                res.include.push(true);
            }

            if (!Array.isArray(res.order) || res.order.length !== res.headers.length) {
                res.order = res.headers.map((_, i) => i);
            } else {
                res.order.push(idxNew);
            }

            renderFromState();
        }
        // Suppression :
        function removeColumn(){
            const res = window.__TABLOAD_LAST;
            if (!res) return;
            if (res.headers.length <= 1) return;

            const lastIndex = res.headers.length - 1;

            res.headers.pop();
            res.rows.forEach(r => r.pop());

            if (Array.isArray(res.include)) {
                res.include.pop();
            }
            if (Array.isArray(res.order)) {
                res.order = res.order.filter(i => i !== lastIndex);
            }

            renderFromState();
        }

// Cette fonction permet le bon formatage des caractères de séparation.
        function currentDelimiter() {
            const v = delimEl.value;
            if (v === 'auto') return null;
            if (v === 'tab') return '\t';
            if (v === 'point-virgule') return ';';
            if (v === 'virgule') return ',';
            if (v === 'pipe') return '|';
            return null;
        }

// Cette fonction s'execute au moment de transformer le texte brut en tableau. Elle initalise toutes les fonctions précédentes (pas de couleurs, ordre par défaut, colonnes cochées par défaut, ...).
        function parseAndRender() {
            const raw = rawEl.value || '';
            const forced = currentDelimiter();
            const res = parseRaw(raw, forced, hdrEl.checked);

            res.include = buildDefaultInclude(res.headers.length);
            res.groupStyles = {};
            ensureThresholds(res);
            ensureGroupDecimals(res);
            res.title = titleInput.value || '';
            res.order = res.headers.map((_, i) => i);

            window.__TABLOAD_LAST = res;
            renderFromState();
        }



// Dans cette fonction, on récupère l'état de res et on recalcule les colonnes (data). On construit le tableau csv avec les bonnes en-têtes, les valeurs low et high, ... On encode également avec UTF-16 pour la prise en compte des accents.
        function exportCSV(){
            const res = window.__TABLOAD_LAST;
            if(!res || !res.headers.length) return;

            const delim = ';';
            ensureThresholds(res);

            let include = res.include;
            if (!Array.isArray(include) || include.length !== res.headers.length) {
                include = buildDefaultInclude(res.headers.length);
                res.include = include;
            }

            let order = res.order;
            if (!Array.isArray(order) || order.length !== res.headers.length) {
                order = res.headers.map((_, i) => i);
                res.order = order;
            }

            const activeIdx = order.filter(i => include[i]);
            if (!activeIdx.length) return;

            const headersOut = activeIdx.map(i => res.headers[i] ?? '');
            headersOut.push('Valeur basse', 'Valeur haute');

            const dataRows = res.rows.map((row, rIdx) => {
                const th = res.thresholds[rIdx] || {low: '', high: ''};
                const base = activeIdx.map(i => row[i] ?? '');
                base.push(th.low ?? '', th.high ?? '');
                return base;
            });

            const rowsOut = [];

            const title = (res.title || '').trim();
            if (title !== '') {
                const titleRow = new Array(headersOut.length).fill('');
                titleRow[0] = title;
                rowsOut.push(titleRow);
            }

            rowsOut.push(headersOut, ...dataRows);

            const body = rowsOut.map(row => row.map(cell => {
                const s = String(cell ?? '');
                if (s.includes('"') || s.includes('\n') || s.includes('\r') || s.includes(delim)) {
                    return '"' + s.replaceAll('"','""') + '"';
                }
                return s;
            }).join(delim)).join('\n');

            const useExcelSep = !!document.querySelector('#excelSep')?.checked;
            const csvText = (useExcelSep ? `sep=${delim}\n` : '') + body;

            const str = csvText;
            const buf = new Uint8Array(2 + str.length * 2);
            buf[0] = 0xFF; buf[1] = 0xFE;
            for (let i = 0; i < str.length; i++) {
                const code = str.charCodeAt(i);
                buf[2 + i*2] = code & 0xFF;
                buf[3 + i*2] = (code >> 8) & 0xFF;
            }

            const blob = new Blob([buf], { type: 'text/csv' });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'tabload_export.csv';
            a.click();
            URL.revokeObjectURL(a.href);
        }





// Ici, on extrait pas vraiement le tableau, on le capture. On utilise donc la librairie "html2canvas" qui va faire une sorte de capture d'écran du tableau complet et va en faire un fichier .png.
        function exportPNG() {
            const table = wrapEl.querySelector('table');
            if (!table) return;

            html2canvas(table, {
                backgroundColor: '#ffffff',
                scale: 2,
                windowWidth: table.scrollWidth,
                windowHeight: table.scrollHeight
            }).then(canvas => {
                canvas.toBlob(blob => {
                    if (!blob) return;
                    const a = document.createElement('a');
                    a.href = URL.createObjectURL(blob);
                    a.download = 'tabload_table.png';
                    a.click();
                    URL.revokeObjectURL(a.href);
                }, 'image/png');
            });
        }

        // parse une ligne CSV avec ; et guillemets
        function parseCsvLine(line, delim = ';') {
            const res = [];
            let cur = '';
            let inQuotes = false;
            for (let i = 0; i < line.length; i++) {
                const ch = line[i];
                if (ch === '"') {
                    if (inQuotes && line[i + 1] === '"') {
                        cur += '"';
                        i++;
                    } else {
                        inQuotes = !inQuotes;
                    }
                } else if (ch === delim && !inQuotes) {
                    res.push(cur);
                    cur = '';
                } else {
                    cur += ch;
                }
            }
            res.push(cur);
            return res;
        }



        // Cette fonction construit un nouveau tableau (headers + rows + thresholds) à partir d'un CSV exporté par l'outil
        function buildStateFromCsv(csvText) {
            let text = String(csvText).replace(/\r\n?/g, '\n');
            let lines = text.split('\n');
            if (!lines.length) return null;

            let delim = ';';
            if (lines[0].startsWith('sep=')) {
                const rest = lines[0].slice(4);
                if (rest.length > 0) {
                    delim = rest[0];
                }
                lines.shift();
            }

            lines = lines.map(l => l.trimEnd());
            while (lines.length && !lines[lines.length - 1]) {
                lines.pop();
            }
            if (!lines.length) return null;

            const rows = lines.map(l => parseCsvLine(l, delim));
            if (!rows.length) return null;

            let idx = 0;
            let title = '';
            const firstRow = rows[0];

            if (
                firstRow.length > 0 &&
                firstRow[0].trim() !== '' &&
                firstRow.slice(1).every(c => !c.trim())
            ) {
                title = firstRow[0].trim();
                idx = 1;
            }

            if (idx >= rows.length) return null;

            const headerRow = rows[idx];
            idx++;

            if (!headerRow || !headerRow.length) return null;
            const colCount = headerRow.length;
            if (colCount < 1) return null;

            let lowIdx = -1;
            let highIdx = -1;
            if (colCount >= 3) {
                lowIdx = colCount - 2;
                highIdx = colCount - 1;
            }

            const dataHeaderLabels = headerRow.slice(0, (lowIdx === -1 ? colCount : lowIdx));
            const dataRows = rows.slice(idx);
            if (!dataRows.length) return null;

            const rowsOut = [];
            const thresholds = [];

            dataRows.forEach(row => {
                const full = [...row];
                while (full.length < colCount) {
                    full.push('');
                }
                const data = full.slice(0, (lowIdx === -1 ? colCount : lowIdx));

                let low = '';
                let high = '';
                if (lowIdx !== -1 && highIdx !== -1) {
                    low = full[lowIdx] ?? '';
                    high = full[highIdx] ?? '';
                }

                rowsOut.push(data);
                thresholds.push({ low, high });
            });

            const res = {
                headers: dataHeaderLabels,
                rows: rowsOut,
                thresholds,
                include: buildDefaultInclude(dataHeaderLabels.length),
                order: dataHeaderLabels.map((_, i) => i),
                groupStyles: {},
                groupDecimals: {},
                meta: { delimiter: delim, guessed: false },
                title
            };

            if (dataHeaderLabels.length > COL_TAUX) {
                for (let i = 0; i < res.rows.length; i++) {
                    res.rows[i][COL_TAUX] = floor2DecimalsString(res.rows[i][COL_TAUX]);
                }
            }

            return res;
        }


        // import CSV exporté par l'outil (format plat)
        function importCsvText(csvText) {
            let text = String(csvText).replace(/\r\n?/g, '\n');
            let lines = text.split('\n');
            if (!lines.length) return;

            if (lines[0].length && lines[0].charCodeAt(0) === 0xFEFF) {
                lines[0] = lines[0].slice(1);
            }

            let delim = ';';
            if (lines[0].startsWith('sep=')) {
                const rest = lines[0].slice(4);
                if (rest.length > 0) {
                    delim = rest[0];
                }
                lines.shift();
            }

            lines = lines.map(l => l.trimEnd());
            while (lines.length && !lines[lines.length - 1]) {
                lines.pop();
            }
            if (!lines.length) return;

            const rows = lines.map(l => parseCsvLine(l, delim));
            if (!rows.length) return;

            let idx = 0;
            let title = '';
            const firstRow = rows[0];
            if (
                firstRow.length > 0 &&
                firstRow[0].trim() !== '' &&
                firstRow.slice(1).every(c => !c.trim())
            ) {
                title = firstRow[0].trim();
                idx = 1;
            }
            if (idx >= rows.length) return;

            const headerRow = rows[idx];
            idx++;

            const colCount = headerRow.length;
            if (colCount < 3) return;

            const lowIdx = colCount - 2;
            const highIdx = colCount - 1;

            const dataHeaders = headerRow.slice(0, colCount - 2);

            const dataRows = rows.slice(idx);
            if (!dataRows.length) return;

            const rowsOut = [];
            const thresholds = [];

            dataRows.forEach(row => {
                const full = [...row];
                while (full.length < colCount) {
                    full.push('');
                }
                const data = full.slice(0, colCount - 2);
                const low = full[lowIdx] ?? '';
                const high = full[highIdx] ?? '';
                rowsOut.push(data);
                thresholds.push({ low, high });
            });

            const includeAll = new Array(dataHeaders.length).fill(true);

            const res = {
                headers: dataHeaders,
                rows: rowsOut,
                thresholds,
                include: includeAll,
                order: dataHeaders.map((_, i) => i),
                groupStyles: {},
                groupDecimals: {},
                meta: { delimiter: delim, guessed: false },
                title
            };

            if (dataHeaders.length > COL_TAUX) {
                for (let i = 0; i < res.rows.length; i++) {
                    res.rows[i][COL_TAUX] = floor2DecimalsString(res.rows[i][COL_TAUX]);
                }
            }

            window.__TABLOAD_LAST = res;
            titleInput.value = title;

            renderFromState();
        }



        function fallbackImportWithoutIndex(headerRow, dataRows, title, delim) {
            const colCount = headerRow.length;
            if (colCount < 3) return;

            const lowIdx = colCount - 2;
            const highIdx = colCount - 1;

            const dataHeaders = headerRow.slice(0, colCount - 2);

            if (!dataRows.length) return;

            const rowsOut = [];
            const thresholds = [];

            dataRows.forEach(row => {
                const full = [...row];
                while (full.length < colCount) {
                    full.push('');
                }
                const data = full.slice(0, colCount - 2);
                const low = full[lowIdx] ?? '';
                const high = full[highIdx] ?? '';
                rowsOut.push(data);
                thresholds.push({low, high});
            });

            const res = {
                headers: dataHeaders,
                rows: rowsOut,
                thresholds,
                include: buildDefaultInclude(dataHeaders.length),
                order: dataHeaders.map((_, i) => i),
                groupStyles: {},
                groupDecimals: {},
                meta: {delimiter: delim, guessed: false},
                title
            };

            if (dataHeaders.length > COL_TAUX) {
                for (let i = 0; i < res.rows.length; i++) {
                    res.rows[i][COL_TAUX] = floor2DecimalsString(res.rows[i][COL_TAUX]);
                }
            }

            window.__TABLOAD_LAST = res;
            titleInput.value = title;
            renderFromState();
        }



// Ici se trouve les fonctions annexes telles que les boutons +/-, le bouton de parse, les boutons d'export, le collage dans la zone de texte qui se transforme automatiquement, le titre qui s'affiche en direct.
        colPlusBtn.addEventListener('click', addColumn);
        colMinusBtn.addEventListener('click', removeColumn);
        document.getElementById('parseBtn').addEventListener('click', parseAndRender);
        document.getElementById('downloadCsvBtn').addEventListener('click', exportCSV);
        document.getElementById('downloadImgBtn').addEventListener('click', exportPNG);
        rawEl.addEventListener('paste', () => setTimeout(parseAndRender, 0));

        titleInput.addEventListener('input', () => {
            const res = window.__TABLOAD_LAST;
            if (!res) return;
            res.title = titleInput.value || '';
            renderFromState();
        });

        importCsvBtn.addEventListener('click', () => {
            importCsvInput.click();
        });

        importCsvInput.addEventListener('change', (e) => {
            const file = e.target.files && e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = ev => {
                const text = String(ev.target.result || '');
                importCsvText(text);
                importCsvInput.value = '';
            };
            reader.readAsText(file, 'utf-16le');
        });
    })();
</script>

</body>
</html>
