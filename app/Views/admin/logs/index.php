<?php
// ----------------------------
// Admin Logs — Vue "Journal d’activité"
// (Feed pro + filtres + pagination + titre bilan)
// ----------------------------

$logs    = $logs ?? [];
$page    = (int)($page ?? 1);
$pages   = (int)($pages ?? 1);
$limit   = (int)($limit ?? 25);
$total   = (int)($total ?? 0);
$filters = $filters ?? [];

$badge = function(string $action): array {
    return match ($action) {
        'create'    => ['label' => 'Création',    'class' => 'badge-soft badge-soft-success'],
        'update'    => ['label' => 'Mise à jour', 'class' => 'badge-soft badge-soft-primary'],
        'delete'    => ['label' => 'Suppression', 'class' => 'badge-soft badge-soft-danger'],
        'forbidden' => ['label' => 'Refusé',      'class' => 'badge-soft badge-soft-warning'],
        default     => ['label' => strtoupper($action), 'class' => 'badge-soft badge-soft-neutral'],
    };
};

// Petite pastille décorative (couleur ok car non-textuelle)
$dot = function(string $action): string {
    return match ($action) {
        'create'    => 'dot-success',
        'update'    => 'dot-primary',
        'delete'    => 'dot-danger',
        'forbidden' => 'dot-warning',
        default     => 'dot-neutral',
    };
};

// Affichage d'une valeur "diff"
$fmt = function($v): string {
    if ($v === null || $v === '') return '—';
    if (is_bool($v)) return $v ? 'true' : 'false';
    if (is_array($v)) return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return (string)$v;
};

// Helper querystring (conserve filtres)
function qs(array $override = []): string {
    $cur = $_GET ?? [];
    foreach ($override as $k => $v) {
        if ($v === null || $v === '') unset($cur[$k]);
        else $cur[$k] = $v;
    }
    return http_build_query($cur);
}

// Pagination "fenêtre"
function pageWindow(int $page, int $pages, int $radius = 2): array {
    if ($pages <= 1) return [1];
    $out = [];
    $add = function($x) use (&$out) { $out[] = $x; };

    $add(1);
    $start = max(2, $page - $radius);
    $end   = min($pages - 1, $page + $radius);

    if ($start > 2) $add('…');
    for ($i = $start; $i <= $end; $i++) $add($i);
    if ($end < $pages - 1) $add('…');
    $add($pages);

    $clean = [];
    foreach ($out as $x) if (empty($clean) || end($clean) !== $x) $clean[] = $x;
    return $clean;
}

$fromN = $total ? (($page - 1) * $limit + 1) : 0;
$toN   = min($total, $page * $limit);
?>

<style>
    /* ====== Base “admin pro” : pas de texte coloré imposé ====== */
    .logs-ui { --bd: rgba(0,0,0,.08); --bd2: rgba(0,0,0,.06); --bg-soft: rgba(0,0,0,.025); }
    .logs-ui .card { border-radius: 14px; border-color: var(--bd2); }
    .logs-ui .section-title { font-size: 1.1rem; font-weight: 750; letter-spacing: -.01em; }
    .logs-ui .subline { font-size: .9rem; opacity: .75; }
    .logs-ui .toolbar { gap: .75rem; }
    .logs-ui .form-label { font-size: .8rem; opacity: .85; }

    /* ====== Feed ====== */
    .log-feed { display:flex; flex-direction:column; gap: 10px; }
    .log-item {
        border: 1px solid var(--bd2);
        border-radius: 14px;
        overflow: hidden;
    }
    .log-row {
        display:flex;
        gap: 12px;
        padding: 12px 14px;
        align-items:flex-start;
    }
    .log-left { width: 16px; display:flex; justify-content:center; padding-top: 6px; }
    .log-main { flex: 1; min-width: 0; }
    .log-right { display:flex; align-items:center; gap: 10px; }
    .log-title { font-weight: 750; line-height: 1.2; }
    .log-meta { display:flex; flex-wrap:wrap; gap: 8px; align-items:center; margin-top: 4px; }
    .log-meta .sep { opacity: .35; }

    .log-message {
        margin-top: 6px;
        background: var(--bg-soft);
        border: 1px solid var(--bd2);
        border-radius: 12px;
        padding: 8px 10px;
    }
    .log-message p { margin: 0; }

    .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }

    .dot { width: 10px; height: 10px; border-radius: 999px; background: rgba(0,0,0,.25); }
    .dot-success { background: rgba(25,135,84,.65); }
    .dot-primary { background: rgba(13,110,253,.65); }
    .dot-danger  { background: rgba(220,53,69,.65); }
    .dot-warning { background: rgba(255,193,7,.85); }
    .dot-neutral { background: rgba(108,117,125,.65); }

    /* ====== Badges soft (fond discret, texte normal) ====== */
    .badge-soft {
        display:inline-flex;
        align-items:center;
        gap:6px;
        border: 1px solid var(--bd2);
        border-radius: 999px;
        padding: .25rem .55rem;
        font-size: .78rem;
        font-weight: 650;
        background: rgba(0,0,0,.02);
    }
    .badge-soft-success { background: rgba(25,135,84,.08); border-color: rgba(25,135,84,.18); }
    .badge-soft-primary { background: rgba(13,110,253,.08); border-color: rgba(13,110,253,.18); }
    .badge-soft-danger  { background: rgba(220,53,69,.08); border-color: rgba(220,53,69,.18); }
    .badge-soft-warning { background: rgba(255,193,7,.10); border-color: rgba(255,193,7,.25); }
    .badge-soft-neutral { background: rgba(108,117,125,.08); border-color: rgba(108,117,125,.18); }

    /* ====== Bouton détail ====== */
    .btn-icon {
        width: 34px; height: 34px;
        display:inline-flex; align-items:center; justify-content:center;
        border-radius: 10px;
    }

    /* ====== Détails changes ====== */
    .log-details {
        border-top: 1px solid var(--bd2);
        background: rgba(0,0,0,.012);
        padding: 12px 14px;
    }
    .diff-grid { display:grid; grid-template-columns: 1fr; gap: 10px; }
    @media (min-width: 992px){ .diff-grid { grid-template-columns: 1fr 1fr; } }
    .diff-item {; border: 1px solid var(--bd2); border-radius: 12px; padding: 10px; }
    .diff-key { font-weight: 750; margin-bottom: 8px; }
    .diff-cols { display:grid; grid-template-columns: 1fr; gap: 8px; }
    @media (min-width: 576px){ .diff-cols { grid-template-columns: 1fr 1fr; } }
    .diff-col { border: 1px solid var(--bd2); border-radius: 12px; padding: 10px; background: rgba(0,0,0,.02); }
    .diff-label { font-size: .75rem; font-weight: 650; opacity: .7; margin-bottom: 6px; text-transform: uppercase; letter-spacing: .03em; }
    .diff-val { font-size: .9rem; white-space: pre-wrap; word-break: break-word; }

    /* ====== Pagination ====== */
    .logs-ui .pagination { margin-bottom: 0; }

    /* rotation icone chevron au toggle */
    button[aria-expanded="true"] i { transform: rotate(180deg); }
    button i { transition: transform .2s ease; }
</style>

<div class="logs-ui">

    <!-- Header -->
    <div class="d-flex align-items-start justify-content-between mb-3 flex-wrap toolbar">
        <div>
            <div class="section-title">Journal d’activité</div>
            <div class="subline">
                Affichage <?= (int)$fromN ?> - <?= (int)$toN ?> sur <?= (int)$total ?> · Page <?= (int)$page ?>/<?= (int)$pages ?>
            </div>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a class="btn btn-outline-secondary" href="<?= site_url('admin/logs') ?>" title="Réinitialiser">
                <i class="fa-solid fa-rotate-left me-2"></i>Réinitialiser
            </a>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="get" action="<?= site_url('admin/logs') ?>" class="row g-2 align-items-end">
                <div class="col-12 col-lg-4">
                    <label class="form-label mb-1">Recherche</label>
                    <input type="text" name="q" class="form-control" value="<?= esc($filters['q'] ?? '') ?>"
                           placeholder="message, entité, id, action…">
                </div>

                <div class="col-12 col-lg-3">
                    <label class="form-label mb-1">Utilisateur</label>
                    <input type="text" name="user" class="form-control" value="<?= esc($filters['user'] ?? '') ?>"
                           placeholder="nom ou email">
                </div>

                <div class="col-6 col-lg-2">
                    <label class="form-label mb-1">Action</label>
                    <select name="action" class="form-select">
                        <?php $a = (string)($filters['action'] ?? ''); ?>
                        <option value="">Toutes</option>
                        <option value="create"    <?= $a==='create'?'selected':'' ?>>Création</option>
                        <option value="update"    <?= $a==='update'?'selected':'' ?>>Mise à jour</option>
                        <option value="delete"    <?= $a==='delete'?'selected':'' ?>>Suppression</option>
                        <option value="forbidden" <?= $a==='forbidden'?'selected':'' ?>>Refusé</option>
                    </select>
                </div>

                <div class="col-6 col-lg-3">
                    <label class="form-label mb-1">Entité</label>
                    <input type="text" name="entity_type" class="form-control" value="<?= esc($filters['entity_type'] ?? '') ?>"
                           placeholder="ex: Report, User…">
                </div>

                <div class="col-6 col-lg-2">
                    <label class="form-label mb-1">ID du bilan</label>
                    <input type="text" name="entity_id" class="form-control" value="<?= esc($filters['entity_id'] ?? '') ?>"
                           placeholder="ex: 123">
                </div>

                <div class="col-6 col-lg-2">
                    <label class="form-label mb-1">Du</label>
                    <input type="date" name="from" class="form-control" value="<?= esc($filters['from'] ?? '') ?>">
                </div>

                <div class="col-6 col-lg-2">
                    <label class="form-label mb-1">Au</label>
                    <input type="date" name="to" class="form-control" value="<?= esc($filters['to'] ?? '') ?>">
                </div>

                <div class="col-6 col-lg-2">
                    <label class="form-label mb-1">Par page</label>
                    <select name="limit" class="form-select">
                        <?php foreach ([10, 25, 50, 100] as $n): ?>
                            <option value="<?= (int)$n ?>" <?= $limit===$n?'selected':'' ?>><?= (int)$n ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-lg-2">
                    <button class="btn btn-primary w-100" type="submit">
                        <i class="fa-solid fa-magnifying-glass me-2"></i>Appliquer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- FEED -->
    <?php if (empty($logs)): ?>
        <div class="card">
            <div class="card-body">
                Aucun log ne correspond à ces filtres.
            </div>
        </div>
    <?php else: ?>
        <div class="log-feed">
            <?php foreach ($logs as $l): ?>
                <?php
                $meta        = $l['meta_arr'] ?? [];
                $changes     = $meta['changes'] ?? [];
                $changeCount = is_array($changes) ? count($changes) : 0;
                $canToggle   = $changeCount > 0;

                $id         = (int)($l['id'] ?? 0);
                $collapseId = 'log_details_' . $id;

                $b        = $badge((string)($l['action'] ?? ''));
                $dotClass = $dot((string)($l['action'] ?? ''));

                $userName  = trim((string)($l['user_fullname'] ?? ''));
                $userEmail = (string)($l['user_email'] ?? '');
                $createdAt = (string)($l['created_at'] ?? '');
                $message   = trim((string)($l['message'] ?? ''));

                $entityLabel = trim((string)($l['entity_type'] ?? ''));
                $entityLower = strtolower($entityLabel);

                $entityIdVal = $l['entity_id'] ?? null;
                $entityIdInt = (is_numeric($entityIdVal) ? (int)$entityIdVal : 0);

                if ($entityLower === 'report') {
                    $title = trim((string)($l['report_title'] ?? ''));
                    $entityText = ($title !== '')
                        ? 'Bilan — ' . $title
                        : ($entityIdInt ? 'Bilan #' . $entityIdInt : 'Bilan');
                } else {
                    $entityText = $entityLabel ?: '—';
                    if ($entityIdInt) $entityText .= ' #' . $entityIdInt;
                }
                ?>
                <div class="log-item">

                    <div class="log-row">
                        <div class="log-left">
                            <span class="dot <?= esc($dotClass) ?>"></span>
                        </div>

                        <div class="log-main">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="<?= esc($b['class']) ?>">
                                    <?= esc($b['label']) ?>
                                </span>

                                <?php if ($createdAt !== ''): ?>
                                    <span style="opacity:.75;"><?= esc($createdAt) ?> · </span>
                                <?php endif; ?>

                                <?php if ($changeCount > 0): ?>
                                    <span style="opacity:.75;"><?= (int)$changeCount ?> changement(s)</span>
                                <?php endif; ?>
                            </div>

                            <div class="log-title mt-1">
                                <?= esc($entityText ?: '—') ?>
                            </div>

                            <div class="log-meta">
                                <span><?= esc($userName ?: 'Utilisateur') ?></span>
                                <?php if ($userEmail !== ''): ?>
                                    <span class="sep">·</span>
                                    <span><?= esc($userEmail) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="log-right">
                            <?php if ($canToggle): ?>
                                <button class="btn btn-outline-secondary btn-sm btn-icon"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#<?= esc($collapseId) ?>"
                                        aria-expanded="false"
                                        aria-controls="<?= esc($collapseId) ?>"
                                        title="Voir les changements">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </button>
                            <?php else: ?>
                                <span style="opacity:.6;">—</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($canToggle): ?>
                        <div class="collapse" id="<?= esc($collapseId) ?>">
                            <div class="log-details">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div style="font-weight:750;">Détails des changements</div>
                                    <div class="mono" style="opacity:.7;">log #<?= (int)$id ?></div>
                                </div>

                                <div class="diff-grid">
                                    <?php foreach ($changes as $key => $chg): ?>
                                        <div class="diff-item">
                                            <div class="diff-key"><?= esc((string)$key) ?></div>
                                            <div class="diff-cols">
                                                <div class="diff-col">
                                                    <div class="diff-label">Avant</div>
                                                    <div class="diff-val mono"><?= esc($fmt($chg['from'] ?? null)) ?></div>
                                                </div>
                                                <div class="diff-col">
                                                    <div class="diff-label">Après</div>
                                                    <div class="diff-val mono"><?= esc($fmt($chg['to'] ?? null)) ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
        <?php $window = pageWindow($page, $pages, 2); ?>
        <nav class="d-flex justify-content-center mt-3">
            <ul class="pagination pagination-sm flex-wrap gap-1">

                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= site_url('admin/logs?' . qs(['page' => max(1, $page-1)])) ?>" aria-label="Précédent">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                </li>

                <?php foreach ($window as $p): ?>
                    <?php if ($p === '…'): ?>
                        <li class="page-item disabled"><span class="page-link">…</span></li>
                    <?php else: ?>
                        <li class="page-item <?= ((int)$p === $page) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= site_url('admin/logs?' . qs(['page' => (int)$p])) ?>">
                                <?= (int)$p ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>

                <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= site_url('admin/logs?' . qs(['page' => min($pages, $page+1)])) ?>" aria-label="Suivant">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </li>

            </ul>
        </nav>
    <?php endif; ?>

</div>