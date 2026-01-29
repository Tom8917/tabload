<?php
$logs  = $logs ?? [];
$page  = $page ?? 1;
$pages = $pages ?? 1;

$badge = function(string $action): array {
    return match ($action) {
        'create'    => ['label' => 'Création',    'class' => 'bg-success'],
        'update'    => ['label' => 'Mise à jour', 'class' => 'bg-primary'],
        'delete'    => ['label' => 'Suppression', 'class' => 'bg-danger'],
        'forbidden' => ['label' => 'Refusé',      'class' => 'bg-warning text-dark'],
        default     => ['label' => strtoupper($action), 'class' => 'bg-secondary'],
    };
};

$accent = function(string $action): string {
    return match ($action) {
        'create'    => 'log-accent-success',
        'update'    => 'log-accent-primary',
        'delete'    => 'log-accent-danger',
        'forbidden' => 'log-accent-warning',
        default     => 'log-accent-secondary',
    };
};

$fmt = function($v): string {
    if ($v === null || $v === '') return '—';
    if (is_bool($v)) return $v ? 'true' : 'false';
    if (is_array($v)) return json_encode($v, JSON_UNESCAPED_UNICODE);
    return (string) $v;
};
?>

<style>
    .log-card {
        border: 1px solid rgba(0,0,0,.10);
        border-radius: 16px;
        position: relative;
        overflow: hidden;
        padding: 10px;
    }

    .log-accent {
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 6px;
        background: rgba(0,0,0,.12);
    }
    .log-accent-success { background: rgba(25,135,84,.45); }
    .log-accent-primary { background: rgba(13,110,253,.45); }
    .log-accent-danger  { background: rgba(220,53,69,.45); }
    .log-accent-warning { background: rgba(255,193,7,.55); }
    .log-accent-secondary { background: rgba(108,117,125,.45); }

    .log-card-clickable { cursor: pointer; }

    .log-caret {
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        border: 1px solid rgba(0,0,0,.08);
        color: rgba(0,0,0,.55);
        font-size: 14px;
        user-select: none;
    }
    .log-card-clickable[aria-expanded="true"] .log-caret { transform: rotate(180deg); }

    .log-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
    }
    .log-title {
        font-weight: 700;
        margin: 0;
    }
    .log-sub {
        font-size: .875rem;
    }
    .log-meta {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }

    .log-diff-wrap {
        margin-top: 12px;
        border-top: 1px solid rgba(0,0,0,.08);
        padding-top: 12px;
    }

    .log-diff-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
    }
    @media (min-width: 992px) {
        .log-diff-grid { grid-template-columns: 1fr 1fr; }
    }

    .diff-item {
        border: 1px solid rgba(0,0,0,.08);
        border-radius: 14px;
        padding: 12px;
    }
    .diff-key {
        font-weight: 700;
        margin-bottom: 8px;
        font-size: .95rem;
    }
    .diff-cols {
        display: grid;
        grid-template-columns: 1fr;
        gap: 8px;
    }
    @media (min-width: 576px) {
        .diff-cols { grid-template-columns: 1fr 1fr; }
    }

    .diff-col {
        border-radius: 12px;
        padding: 10px;
        background: rgba(0,0,0,.02);
        border: 1px solid rgba(0,0,0,.06);
    }
    .diff-label {
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .02em;
        margin-bottom: 6px;
    }
    .diff-val {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: .9rem;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .log-list { display: flex; flex-direction: column; gap: 12px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Logs</h4>
        <div class="text-muted small">Page <?= (int)$page ?> / <?= (int)$pages ?></div>
    </div>
</div>

<?php if (empty($logs)): ?>
    <div class="p-3 text-center text-muted">Aucun log.</div>
<?php else: ?>

    <div class="log-list">
        <?php foreach ($logs as $l): ?>
            <?php
            $meta = $l['meta_arr'] ?? [];
            $changes = $meta['changes'] ?? [];
            $changeCount = is_array($changes) ? count($changes) : 0;

            $collapseId = 'log_' . (int)$l['id'];
            $canToggle  = $changeCount > 0;

            $b = $badge((string)$l['action']);
            $accentClass = $accent((string)$l['action']);
            ?>

            <div class="log-card <?= $canToggle ? 'log-card-clickable' : '' ?>"
                <?= $canToggle ? 'role="button" data-bs-toggle="collapse" data-bs-target="#' . esc($collapseId) . '" aria-expanded="false"' : '' ?>>

                <div class="log-accent <?= esc($accentClass) ?>"></div>

                <div class="card-body" style="padding-left: 20px;">
                    <div class="log-header">
                        <div>
                            <div class="log-meta">
                                <span class="badge <?= esc($b['class']) ?>"><?= esc($b['label']) ?></span>
                                <span class="text-muted small"><?= esc($l['created_at']) ?></span>
                                <?php if ($changeCount > 0): ?>
                                    <span class="text-muted small">· <?= (int)$changeCount ?> changement(s)</span>
                                <?php endif; ?>
                            </div>

                            <div class="mt-2">
                                <div class="log-title">
                                    <?= esc($l['entity_type']) ?><?= $l['entity_id'] ? ' #' . esc($l['entity_id']) : '' ?>
                                </div>
                                <div class="log-sub">
                                    Par <?= esc(trim($l['user_fullname'] ?? '')) ?: 'Utilisateur' ?>
                                    <?php if (!empty($l['user_email'])): ?>
                                        · <?= esc($l['user_email']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if (!empty($l['message'])): ?>
                                <div class="mt-2">
                                    <?= esc($l['message']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($canToggle): ?>
                            <div class="log-caret" aria-hidden="true">⌄</div>
                        <?php endif; ?>
                    </div>

                    <?php if ($canToggle): ?>
                        <div class="collapse log-diff-wrap" id="<?= esc($collapseId) ?>">
                            <div class="log-diff-grid">
                                <?php foreach ($changes as $key => $chg): ?>
                                    <div class="diff-item">
                                        <div class="diff-key"><?= esc($key) ?></div>

                                        <div class="diff-cols">
                                            <div class="diff-col">
                                                <div class="diff-label">Avant</div>
                                                <div class="diff-val"><?= esc($fmt($chg['from'] ?? null)) ?></div>
                                            </div>

                                            <div class="diff-col">
                                                <div class="diff-label">Après</div>
                                                <div class="diff-val"><?= esc($fmt($chg['to'] ?? null)) ?></div>
                                            </div>
                                        </div>

                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        <?php endforeach; ?>
    </div>

    <?php if ($pages > 1): ?>
        <div class="d-flex justify-content-center gap-2 mt-4 flex-wrap">
            <?php for ($p=1; $p <= $pages; $p++): ?>
                <a class="btn btn-sm <?= $p===(int)$page ? 'btn-primary' : 'btn-outline-primary' ?>"
                   href="<?= site_url('admin/logs?page=' . $p) ?>">
                    <?= (int)$p ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

<?php endif; ?>
