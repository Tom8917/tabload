<?php
$sectionsTree = $sectionsTree ?? [];
$versions = $versions ?? [];
$report = $report ?? [];

$fmtDate = function ($value): string {
    if (empty($value)) return '—';
    try {
        return (new DateTime((string)$value))->format('d/m/Y');
    } catch (\Throwable $e) {
        return (string)$value;
    }
};

$docStatus = (string)($report['doc_status'] ?? 'work');
$modKind   = (string)($report['modification_kind'] ?? 'creation');

$cb = function (bool $checked): string {
    return $checked ? '☑' : '☐';
};

$headingTag = function (int $level): string {
    return match ($level) {
        1       => 'h2',
        2       => 'h3',
        default => 'h4',
    };
};

$appName     = (string)($report['application_name'] ?? '—');
$appVersion  = (string)($report['application_version'] ?? '');
$docVersion  = (string)($report['doc_version'] ?? '');
$author      = (string)($report['author_name'] ?? '');
$validator   = (string)($report['validated_by'] ?? '');
$validatedAt = $report['validated_at'] ?? null;
$createdAt   = $report['created_at'] ?? null;
$updatedAt   = $report['corrected_at'] ?? ($report['author_updated_at'] ?? ($report['updated_at'] ?? null));

$status      = (string)($report['status'] ?? '');
$statusLabel = $status !== '' ? $status : '—';

$pageTitle = trim((string)($report['title'] ?? 'Bilan'));

helper('html');
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title><?= esc($pageTitle !== '' ? $pageTitle : 'Bilan') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        :root {
            --border: #d9dce1;
            --text: #1f2937;
            --muted: #6b7280;
            --primary: #1d4ed8;
            --bg-soft: #f8fafc;
            --radius: 12px;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html {
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
        }

        body {
            margin: 0;
            padding: 20px;
            font-family: "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.5;
            color: var(--text);
            background: #fff;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        a {
            color: var(--primary);
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        p {
            margin: 0 0 .5rem;
        }

        p:last-child {
            margin-bottom: 0;
        }

        h1, h2, h3, h4 {
            margin-top: 0;
            margin-bottom: .5rem;
            line-height: 1.25;
        }

        .page {
            width: 100%;
            max-width: 1040px;
            margin: 0 auto;
        }

        .toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 0 0 18px;
        }

        .toolbar button {
            appearance: none;
            -webkit-appearance: none;
            border: 1px solid var(--border);
            background: #fff;
            color: var(--text);
            padding: 10px 14px;
            border-radius: 10px;
            cursor: pointer;
            font: inherit;
            line-height: 1.2;
        }

        .toolbar button:hover {
            background: #f9fafb;
        }

        .print-note {
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 14px;
        }

        .card,
        .section-card {
            width: 100%;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            margin: 0 0 14px;
            overflow: hidden;
        }

        .card-header {
            padding: 10px 14px;
            background: var(--bg-soft);
            border-bottom: 1px solid var(--border);
        }

        .card-body {
            padding: 12px 14px;
        }

        .title-main {
            margin: 0;
            text-align: center;
            color: var(--primary);
            font-size: 1.35rem;
            line-height: 1.2;
        }

        .text-center {
            text-align: center;
        }

        .text-center h2 {
            margin-top: 0;
            margin-bottom: .45rem;
        }

        .muted {
            color: var(--muted);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            border-top: 1px solid var(--border);
            border-left: 1px solid var(--border);
        }

        .info-cell {
            padding: 10px;
            border-right: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            min-width: 0;
        }

        .history-grid {
            display: grid;
            grid-template-columns: 160px 160px minmax(0, 1fr);
            border-top: 1px solid var(--border);
            border-left: 1px solid var(--border);
        }

        .history-cell {
            padding: 10px;
            border-right: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            min-width: 0;
        }

        .summary-tree {
            font-size: .97rem;
            line-height: 1.45;
        }

        .summary-level {
            margin: 0;
            padding-left: 1.15rem;
        }

        .summary-level-1 {
            padding-left: 0;
            list-style: none;
        }

        .summary-level-2,
        .summary-level-3,
        .summary-level-4,
        .summary-level-5,
        .summary-level-6,
        .summary-level-7,
        .summary-level-8 {
            list-style: none;
            margin-top: .2rem;
        }

        .summary-item {
            margin-bottom: .15rem;
        }

        .summary-item:last-child {
            margin-bottom: 0;
        }

        .summary-text {
            display: inline;
            color: inherit;
            text-decoration: none;
        }

        .summary-item-1 > .summary-text {
            font-weight: 700;
        }

        .summary-item-2 > .summary-text {
            font-weight: 600;
        }

        .summary-item-3 > .summary-text,
        .summary-item-4 > .summary-text,
        .summary-item-5 > .summary-text,
        .summary-item-6 > .summary-text,
        .summary-item-7 > .summary-text,
        .summary-item-8 > .summary-text {
            font-weight: 400;
        }

        .section-card {
            padding: 12px 14px;
            overflow: visible;
        }

        .section-block {
            margin-top: 10px;
        }

        .level-2 {
            margin-left: 20px;
        }

        .level-3,
        .level-4,
        .level-5,
        .level-6,
        .level-7,
        .level-8 {
            margin-left: 34px;
        }

        .section-card h2,
        .section-card h3,
        .section-card h4,
        .section-block h2,
        .section-block h3,
        .section-block h4 {
            margin: 0 0 8px;
            line-height: 1.28;
        }

        .badge {
            display: inline-block;
            vertical-align: middle;
            margin-right: 8px;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 12px;
            line-height: 1.2;
            /*background: var(--primary);*/
            /*color: #fff;*/
            white-space: nowrap;
        }

        .report-content {
            min-width: 0;
        }

        .report-content p {
            margin-bottom: .5rem;
        }

        .report-content img {
            display: block;
            max-width: 100%;
            height: auto;
            margin: 8px auto;
            border-radius: 8px;
        }

        .report-content ul,
        .report-content ol {
            margin: 0 0 .8rem;
            padding-left: 1.2rem;
        }

        .report-content li + li {
            margin-top: .2rem;
        }

        .report-content table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
            margin: 10px 0;
        }

        .report-content th,
        .report-content td {
            border: 1px solid var(--border);
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        .report-content th {
            background: #f8fafc;
        }

        .report-content blockquote {
            margin: .8rem 0;
            padding-left: 12px;
            border-left: 4px solid #d1d5db;
            color: #4b5563;
        }

        .report-content pre,
        .report-content code {
            white-space: pre-wrap;
            word-break: break-word;
        }

        @media (max-width: 900px) {
            body {
                padding: 16px;
            }

            .page {
                max-width: 100%;
            }

            .history-grid {
                grid-template-columns: 140px 140px minmax(0, 1fr);
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 14px;
            }

            .card-header,
            .card-body,
            .section-card {
                padding-left: 12px;
                padding-right: 12px;
            }

            .info-grid,
            .history-grid {
                grid-template-columns: 1fr;
                border-left: 1px solid var(--border);
            }

            .info-cell,
            .history-cell {
                border-right: 0;
            }

            .level-2,
            .level-3,
            .level-4,
            .level-5,
            .level-6,
            .level-7,
            .level-8 {
                margin-left: 10px;
            }

            .title-main {
                font-size: 1.25rem;
            }
        }

        @page {
            size: A4;
            margin: 14mm 12mm 14mm 12mm;
        }

        @media print {
            html,
            body {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                background: #fff !important;
            }

            body {
                color: #000;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .page {
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .toolbar,
            .print-note {
                display: none !important;
            }

            a,
            a:visited {
                color: inherit !important;
                text-decoration: none !important;
            }

            .summary-text {
                color: #000 !important;
                text-decoration: none !important;
            }

            .card {
                break-inside: avoid;
                page-break-inside: avoid;
                box-shadow: none !important;
            }

            .section-card {
                break-inside: auto;
                page-break-inside: auto;
                box-shadow: none !important;
                overflow: visible !important;
            }

            .section-block {
                break-inside: auto;
                page-break-inside: auto;
            }

            .section-card > h2,
            .section-card > h3,
            .section-card > h4,
            .section-block > h2,
            .section-block > h3,
            .section-block > h4 {
                break-after: avoid-page;
                page-break-after: avoid;
            }

            .section-card > h2 + .report-content,
            .section-card > h3 + .report-content,
            .section-card > h4 + .report-content,
            .section-block > h2 + .report-content,
            .section-block > h3 + .report-content,
            .section-block > h4 + .report-content {
                break-before: avoid-page;
                page-break-before: avoid;
            }

            .report-content img,
            .report-content table,
            .report-content blockquote,
            .report-content pre {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            tr,
            td,
            th {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            ul,
            ol,
            li {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

<div class="page">

    <div class="toolbar">
        <button type="button" onclick="window.print()">Imprimer / PDF</button>
    </div>

    <div class="card">
        <div class="card-header">
            <h1 class="title-main">Bureau de l'intégration</h1>
        </div>
        <div class="card-body">
            <div class="text-center">
                <h2><?= esc($appName) ?> <?= $appVersion !== '' ? esc($appVersion) : '—' ?></h2>
                <h2><?= esc($report['title'] ?? '') ?></h2>
            </div>

            <p><strong>Version :</strong> <?= $docVersion !== '' ? esc($docVersion) : '—' ?>
                du <?= esc($fmtDate($createdAt)) ?></p>
            <p><strong>Statut :</strong> <?= esc($statusLabel) ?></p>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Objet et domaine d'application</strong></div>
        <div class="card-body">
            <p>
                Ce document a pour objet la description des résultats obtenus lors de la campagne
                de tests de performance menée durant la campagne d'intégration sur
                l'application <?= esc($appName) ?> avant sa mise en production.
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Statut</strong></div>
        <div class="card-body">
            <div><?= $cb($docStatus === 'validated') ?> Document validé</div>
            <div><?= $cb($docStatus === 'approved') ?> Document approuvé</div>
            <div><?= $cb($docStatus === 'work') ?> Document de travail</div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="info-grid">
                <div class="info-cell">
                    <strong>Rédigé par</strong><br>
                    <?= esc($author ?: '—') ?>
                </div>
                <div class="info-cell">
                    <strong>Date de dernière modification</strong><br>
                    <?= esc($fmtDate($updatedAt)) ?>
                </div>
                <div class="info-cell">
                    <strong>Validé par</strong><br>
                    <?= esc($validator ?: '—') ?>
                </div>
                <div class="info-cell">
                    <strong>Date de dernière validation</strong><br>
                    <?= esc($fmtDate($validatedAt)) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Modification par rapport à l'existant</strong></div>
        <div class="card-body">
            <div><?= $cb($modKind === 'creation') ?> Création</div>
            <div><?= $cb($modKind === 'replace') ?> Annule et remplace la version précédente</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Historique des évolutions</strong></div>
        <div class="card-body">
            <?php if (empty($versions)): ?>
                <p class="muted">Aucun historique pour le moment.</p>
            <?php else: ?>
                <div class="history-grid">
                    <div class="history-cell"><strong>Version</strong></div>
                    <div class="history-cell"><strong>Date</strong></div>
                    <div class="history-cell"><strong>Commentaires</strong></div>

                    <?php foreach ($versions as $v): ?>
                        <div class="history-cell"><?= esc((string)($v['version_label'] ?? '—')) ?></div>
                        <div class="history-cell"><?= esc($fmtDate($v['created_at'] ?? null)) ?></div>
                        <div class="history-cell"><?= esc(trim((string)($v['comment'] ?? '')) ?: '—') ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php
    $renderSummary = function (array $nodes, int $level = 1) use (&$renderSummary) {
        if (empty($nodes)) {
            return;
        }

        echo '<ul class="summary-level summary-level-' . $level . '">';

        foreach ($nodes as $node) {
            $code = trim((string)($node['code'] ?? ''));
            $title = trim((string)($node['title'] ?? ''));

            echo '<li class="summary-item summary-item-' . $level . '">';
            echo '<a class="summary-text" href="#section-' . (int)$node['id'] . '">';
            echo esc(($code !== '' ? $code . '. ' : '') . $title);
            echo '</a>';

            if (!empty($node['children'])) {
                $renderSummary($node['children'], $level + 1);
            }

            echo '</li>';
        }

        echo '</ul>';
    };
    ?>

    <?php if (!empty($sectionsTree)): ?>
        <div class="card">
            <div class="card-header">
                <strong>Sommaire</strong>
            </div>
            <div class="card-body">
                <div class="summary-tree">
                    <?php $renderSummary($sectionsTree); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php
    $render = function (array $node) use (&$render, $headingTag) {
    $level   = (int)($node['level'] ?? 1);
    $tag     = $headingTag($level);
    $title   = (string)($node['title'] ?? '');
    $code    = (string)($node['code'] ?? '');
    $content = (string)($node['content'] ?? '');
    ?>
    <div class="<?= $level === 1 ? 'section-card' : 'section-block level-' . $level ?>" id="section-<?= (int)$node['id'] ?>">
        <<?= $tag ?>>
        <?php if ($code !== ''): ?>
            <span><?= esc($code) ?></span>
        <?php endif; ?>
        <?= esc($title) ?>
    </<?= $tag ?>>

    <?php if (trim($content) !== ''): ?>
        <div class="report-content">
            <?= clean_html($content) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($node['children'])): ?>
        <?php foreach ($node['children'] as $child): ?>
            <?php $render($child); ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php
};
?>

<?php if (empty($sectionsTree)): ?>
    <p class="muted">Aucune section pour l’instant.</p>
<?php else: ?>
    <?php foreach ($sectionsTree as $root): ?>
        <?php $render($root); ?>
    <?php endforeach; ?>
<?php endif; ?>

</div>
</body>
</html>
