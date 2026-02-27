<?php
/** @var array $report */
/** @var array $sectionsTree */
/** @var array $versions */

helper('html_helper');

$report = $report ?? [];
$sectionsTree = $sectionsTree ?? [];
$versions = $versions ?? [];

$title = (string)($report['title'] ?? '');
$appName = (string)($report['application_name'] ?? '');
$appVer = trim((string)($report['application_version'] ?? ''));
$docVer = (string)($report['doc_version'] ?? '');

$author = (string)($author ?? '—');
$createdAt = (string)($createdAt ?? '—');
$updatedAt = (string)($updatedAt ?? '—');
$generatedAt = (string)($generatedAt ?? '—');

$status = (string)($report['status'] ?? '—');
$docStatus = (string)($report['doc_status'] ?? 'work');
$modKind = (string)($report['modification_kind'] ?? 'creation');

$validator = (string)($report['validated_by'] ?? ($report['validated_by_name'] ?? ''));
$validatedAt = (string)($report['validated_at'] ?? '');

$fmtDate = function (?string $value): string {
    if (empty($value)) return '—';
    try {
        return (new DateTime($value))->format('d/m/Y');
    } catch (\Throwable $e) {
        return (string)$value;
    }
};

$check = fn(bool $ok) => $ok ? '[x]' : '[ ]';

$render = function (array $node) use (&$render) {
    $level = (int)($node['level'] ?? 1);
    $code = (string)($node['code'] ?? '');
    $t = (string)($node['title'] ?? '');
    $content = (string)($node['content'] ?? '');

    $hTag = match (true) {
        $level <= 1 => 'h2',
        $level === 2 => 'h3',
        default => 'h4',
    };

    $indentMm = max(0, ($level - 1) * 6);

    ?>
<div class="sec" style="margin-left: <?= (int)$indentMm ?>mm;">
    <<?= $hTag ?> class="sec-title">
    <?php if ($code !== ''): ?>
        <span class="code"><?= esc($code) ?></span>
    <?php endif; ?>
    <?= esc($t) ?>
    </<?= $hTag ?>>

    <?php if (trim($content) !== ''): ?>
        <div class="content">
            <?php
            $html = pdf_embed_images($content);
            echo clean_html($html);
            ?>
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
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title><?= esc($title) ?></title>

    <style>
        @page {
            margin: 38mm 15mm 22mm 15mm;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11pt;
            color: #111;
        }

        .pdf-header {
            position: fixed;
            left: 0;
            right: 0;
            top: -28mm;
            height: 20mm;
            border-bottom: 1px solid #ddd;
            padding: 0 0 1mm 0;
            background: #fff;
        }

        .pdf-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: -18mm;
            height: 16mm;
            border-top: 1px solid #ddd;
            padding: 2mm 0 0 0;
            background: #fff;
            font-size: 9pt;
            color: #444;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .muted {
            color: #666;
            font-size: 9pt;
        }

        /* Cover */
        .cover {
            page-break-after: always;
        }

        .cover h1 {
            margin: 0 0 3mm 0;
            font-size: 22pt;
            font-weight: 700;
        }

        .cover .sub {
            font-size: 12pt;
            color: #444;
            margin-bottom: 10mm;
        }

        .cover-box {
            border: 1px solid #ddd;
            padding: 8mm;
        }

        .cover-box .big {
            font-size: 16pt;
            font-weight: 700;
            text-align: center;
            margin-bottom: 6mm;
        }

        /* Blocks */
        .box {
            border: 1px solid #ddd;
            padding: 6mm;
            margin: 0 0 6mm 0;
            page-break-inside: avoid;
        }

        .box .big {
            font-size: 16pt;
            font-weight: 700;
            text-align: center;
            margin-bottom: 6mm;
        }

        .box-title {
            font-weight: 700;
            margin: 0 0 4mm 0;
            font-size: 12pt;
        }

        .grid td {
            border: 1px solid #ddd;
            padding: 4mm;
            vertical-align: top;
        }

        .grid .k {
            font-weight: 700;
            margin-bottom: 1mm;
        }

        /* TOC */
        .toc {
            page-break-after: always;
        }

        .toc h2 {
            margin: 0 0 4mm 0;
        }

        .toc-item {
            margin: 0 0 2.5mm 0;
            page-break-inside: avoid;
        }

        .toc-indent-1 {
            margin-left: 0mm;
        }

        .toc-indent-2 {
            margin-left: 6mm;
        }

        .toc-indent-3 {
            margin-left: 12mm;
        }

        /* Sections */
        .page-break {
            page-break-before: always;
        }

        .sec-title {
            margin: 6mm 0 2mm 0;
            page-break-after: avoid;
        }

        .code {
            display: inline-block;
            font-size: 9pt;
            padding: 1mm 2mm;
            border: 1px solid #ccc;
            margin-right: 2mm;
        }

        .content p {
            margin: 0 0 3mm 0;
        }

        .content ul, .content ol {
            margin: 0 0 3mm 0;
            padding-left: 6mm;
        }

        .content blockquote {
            margin: 3mm 0;
            padding: 2mm 3mm;
            border-left: 2mm solid #ddd;
            color: #333;
        }

        .content img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 3mm auto;
        }

        .content table {
            margin: 3mm 0;
        }

        .content thead {
            display: table-header-group;
        }

        .content th, .content td {
            border: 1px solid #ddd;
            padding: 2mm;
        }

        .content th {
            font-weight: 700;
        }
    </style>
</head>
<body>

<div class="pdf-header">
    <table>
        <tr>
            <td style="text-align:left; vertical-align:top;">
                <div><strong><?= esc($appName) ?></strong><?= $appVer !== '' ? ' — ' . esc($appVer) : '' ?></div>
                <div class="muted">Bilan : <?= esc($title) ?></div>
            </td>
            <td style="text-align:right; vertical-align:top;">
                <div class="muted">Auteur : <?= esc($author) ?></div>
                <div class="muted">Créé : <?= esc($createdAt) ?></div>
                <div class="muted">Maj : <?= esc($updatedAt) ?></div>
            </td>
        </tr>
    </table>
</div>

<div class="pdf-footer">
    <table>
        <tr>
            <td style="text-align:left;">
                <span class="muted">Version : <?= esc($docVer !== '' ? $docVer : '—') ?></span>
            </td>
            <td style="text-align:right;">
                <span class="muted">Export : <?= esc($generatedAt) ?></span>
            </td>
        </tr>
    </table>
</div>

<div class="cover">
    <h1><?= esc($title) ?></h1>
    <div class="sub">Application : <?= esc($appName) ?><?= $appVer !== '' ? ' (' . esc($appVer) . ')' : '' ?></div>

    <div class="box">
        <div class="big">Bureau de l’intégration</div>

        <table class="grid">
            <tr>
                <td>
                    <div class="k">Statut</div>
                    <div><?= esc($status ?: '—') ?></div>
                </td>
                <td>
                    <div class="k">Document</div>
                    <div><?= esc($docVer ?: '—') ?></div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="k">Rédigé par</div>
                    <div><?= esc($author ?: '—') ?></div>
                </td>
                <td>
                    <div class="k">Créé le</div>
                    <div><?= esc($createdAt) ?></div>
                </td>
            </tr>
        </table>
    </div>

    <div class="box">
        <div class="box-title">Objet et domaine d’application</div>
        <p>
            Ce document a pour objet la description des résultats obtenus lors de la campagne de tests de performance
            menée durant la campagne d’intégration sur l’application <strong><?= esc($appName) ?></strong>
            avant sa mise en production.
        </p>
    </div>

    <div class="box">
        <div class="box-title">Statut du document</div>
        <div><?= $check($docStatus === 'validated') ?> Document validé</div>
        <div><?= $check($docStatus === 'approved') ?> Document approuvé</div>
        <div><?= $check($docStatus === 'work') ?> Document de travail</div>
    </div>

    <div class="box">
        <div class="box-title">Informations</div>
        <table class="grid">
            <tr>
                <td>
                    <div class="k">Rédigé par</div>
                    <div><?= esc($author ?: '—') ?></div>
                </td>
                <td>
                    <div class="k">Date de dernière modification</div>
                    <div><?= esc($updatedAt) ?></div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="k">Validé par</div>
                    <div><?= esc($validator !== '' ? $validator : '—') ?></div>
                </td>
                <td>
                    <div class="k">Date de dernière validation</div>
                    <div><?= esc($fmtDate($validatedAt)) ?></div>
                </td>
            </tr>
        </table>
    </div>

    <div class="box">
        <div class="box-title">Modification par rapport à l’existant</div>
        <div><?= $check($modKind === 'creation') ?> Création</div>
        <div><?= $check($modKind === 'replace') ?> Annule et remplace la version précédente</div>
    </div>

    <div class="box">
        <div class="box-title">Historique des évolutions</div>
        <table class="grid">
            <tr>
                <td style="width:20%;">
                    <div class="k">Version</div>
                </td>
                <td style="width:20%;">
                    <div class="k">Date</div>
                </td>
                <td>
                    <div class="k">Commentaire</div>
                </td>
            </tr>

            <?php if (empty($versions)): ?>
                <tr>
                    <td colspan="3" class="muted">Aucun historique pour le moment.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($versions as $v): ?>
                    <?php
                    $versionLabel = (string)($v['version_label'] ?? '—');
                    $date = (string)($v['created_at'] ?? '');
                    $comment = trim((string)($v['comment'] ?? '')) ?: '—';
                    ?>
                    <tr>
                        <td><?= esc($versionLabel) ?></td>
                        <td><?= esc($fmtDate($date)) ?></td>
                        <td><?= esc($comment) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>
</div>

<div class="toc">
    <h2>Sommaire</h2>

    <?php if (empty($sectionsTree)): ?>
        <p class="muted">Aucune section.</p>
    <?php else: ?>
        <?php
        $walk = function (array $node) use (&$walk) {
            $level = (int)($node['level'] ?? 1);
            $code = (string)($node['code'] ?? '');
            $t = (string)($node['title'] ?? '');

            $indentClass = match (true) {
                $level <= 1 => 'toc-indent-1',
                $level === 2 => 'toc-indent-2',
                default => 'toc-indent-3',
            };
            ?>
            <div class="toc-item <?= $indentClass ?>">
                <?= esc(trim(($code !== '' ? $code . '. ' : '') . $t)) ?>
            </div>
            <?php
            foreach (($node['children'] ?? []) as $child) $walk($child);
        };

        foreach ($sectionsTree as $r) $walk($r);
        ?>
    <?php endif; ?>
</div>

<?php if (!empty($sectionsTree)): ?>
    <?php foreach ($sectionsTree as $i => $root): ?>
        <div class="page-break"></div>
        <?php $render($root); ?>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>