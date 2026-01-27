<?php
$sectionsTree = $sectionsTree ?? [];
$canEdit      = $canEdit ?? true; // admin => true

$indentClass = function (int $level): string {
    return match (true) {
        $level <= 1 => 'ms-0',
        $level === 2 => 'ms-3',
        default => 'ms-5',
    };
};

$headingClass = function (int $level): string {
    return match ($level) {
        1 => 'h4 fw-bold mb-2',
        2 => 'h5 fw-semibold mb-2',
        default => 'h6 fw-semibold mb-2 text-body',
    };
};

$badgeLevel = function (int $level): string {
    return match ($level) {
        1 => 'bg-primary',
        2 => 'bg-secondary',
        default => 'bg-dark',
    };
};

$roots        = $sectionsTree;
$scrollOffset = 90;

$docStatus = (string)($report['doc_status'] ?? 'work');
$modKind   = (string)($report['modification_kind'] ?? 'creation');

$cb = function (bool $checked): string {
    return $checked ? '<i class="fa-regular fa-circle-check"></i>' : '<i class="fa-regular fa-circle"></i>';
};

helper('html');

$fmtDate = function ($value): string {
    if (empty($value)) return '—';
    try {
        return (new DateTime((string)$value))->format('d/m/Y');
    } catch (\Throwable $e) {
        return (string)$value;
    }
};

$status      = (string)($report['status'] ?? '');
$statusLabel = $status !== '' ? $status : '—';

$mediaId = (int)($report['file_media_id'] ?? 0);
$entFile = '';
if ($mediaId > 0) {
    $m = model(\App\Models\MediaModel::class)->find($mediaId);
    $entFile = trim((string)($m['file_name'] ?? ''));
}
if ($entFile === '') $entFile = 'Aucun document renseigné';

// ⚠️ mêmes champs que ton front
$appName     = (string)($report['application_name'] ?? '—');
$appVersion  = (string)($report['application_version'] ?? ''); // pas "version" ici
$docVersion  = (string)($report['doc_version'] ?? '');
$author      = (string)($report['author_name'] ?? '');
$fileId      = (string)($report['file_media_id'] ?? '');
$fileName    = (string)($report['file_name'] ?? '');
$corrector   = (string)($report['corrected_by'] ?? '');
$validator   = (string)($report['validated_by'] ?? '');
$validatedAt = $report['validated_at'] ?? null;
$createdAt   = $report['created_at'] ?? null;

// même logique front pour la “dernière modif”
$updatedAt = $report['corrected_at'] ?? ($report['author_updated_at'] ?? ($report['updated_at'] ?? null));
?>

<style>
    [id^="section-"], #top {
        scroll-margin-top: <?= (int)$scrollOffset ?>px;
    }
</style>

<div class="container-fluid">
    <div id="top"></div>

    <?= view('admin/reports/_steps', [
        'step'     => 'preview',
        'reportId' => (int)($report['id'] ?? 0),
        'canEdit'  => true,
    ]) ?>

    <div class="d-flex justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1">Aperçu : <?= esc($report['title'] ?? '') ?></h1>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= site_url('admin/reports/' . (int)$report['id'] . '/sections') ?>" class="btn btn-outline-primary">
                Retour à la rédaction
            </a>
            <a href="<?= site_url('admin/reports') ?>" class="btn btn-outline-secondary">
                Retour à la liste
            </a>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= esc($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors) && is_array($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $err): ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Card centrée et large : Bureau de l'intégration -->
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10 col-xxl-9">

            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-center">
                    <h1 class="fw-semibold text-primary">Bureau de l'intégration</h1>
                </div>

                <div class="card-body">
                    <div class="col-12 text-center">
                        <div class="fw-semibold">
                            <h2><?= esc($appName) ?> <?= $appVersion !== '' ? esc($appVersion) : '—' ?></h2>
                        </div>
                    </div>

                    <h2 class="mb-4 text-center"><?= esc($report['title'] ?? '') ?></h2>

                    <div class="row g-4">
                        <div class="col-12">
                            <div class="mt-3 mb-1">
                                <span class="fw-bold">Version :</span>
                                <?= $docVersion !== '' ? esc($docVersion) : '—' ?>
                                du <?= esc($fmtDate($createdAt)) ?>
                            </div>
                        </div>

                        <div class="col-12">
                            <?php if (!empty($report['file_path'])): ?>
                                <?php $fileUrl = base_url(ltrim((string)$report['file_path'], '/')); ?>
                                <div class="mt-3 mb-1">
                                    <span class="fw-bold">Fichier :</span>
                                    <a href="<?= esc($fileUrl) ?>" target="_blank" rel="noopener">
                                        <?= esc($fileName ?: 'Ouvrir le fichier') ?>
                                    </a>
                                </div>
                                <div class="mt-2">
                                    <a class="btn btn-sm btn-outline-secondary"
                                       href="<?= esc($fileUrl) ?>" download>
                                        Télécharger
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="mt-3 mb-1">
                                    <span class="fw-bold">Fichier :</span> <?= esc($entFile) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12">
                            <div class="mt-3 mb-1">
                                <span class="fw-bold">Statut :</span> <?= esc($statusLabel) ?>
                            </div>
                        </div>

                        <div class="col-12">
                            <?php if (!empty($report['id'])): ?>
                                <span class="text-muted small">Id dans la base : #<?= (int)$report['id'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Card centrée et large : Objectif et domaine d'application -->
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10 col-xxl-9">

            <div class="card mb-4">
                <div class="card-body">

                    <div class="card mb-4">
                        <div class="card-header mb-2">
                            <h5 class="fw-semibold">Objet et domaine d'application</h5>
                        </div>
                        <div class="card-body">
                            <div class="col-12 mb-4">
                                <p class="mb-0">
                                    Ce document a pour objet la description des résultats obtenus lors de la campagne
                                    de tests de performance menée durant la campagne d'intégration sur
                                    l'application <?= esc($appName) ?> avant sa mise en production.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="fw-semibold mb-0">Statut</h5>
                        </div>

                        <div class="card-body">
                            <div class="d-flex flex-column gap-2">
                                <div><?= $cb($docStatus === 'validated') ?> Document validé</div>
                                <div><?= $cb($docStatus === 'approved') ?> Document approuvé</div>
                                <div><?= $cb($docStatus === 'work') ?> Document de travail</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="card-body">
                            <div class="row info-grid">
                                <div class="col-12 col-md-6 info-cell">
                                    <h5 class="fw-semibold mb-1">Rédigé par</h5>
                                    <p class="mb-0"><?= esc($author ?: '—') ?></p>
                                </div>

                                <div class="col-12 col-md-6 info-cell info-cell-left">
                                    <h5 class="fw-semibold mb-1">Date de dernière modification</h5>
                                    <p class="mb-0"><?= esc($fmtDate($updatedAt)) ?></p>
                                </div>

                                <div class="col-12 col-md-6 info-cell info-cell-top">
                                    <h5 class="fw-semibold mb-1">Validé par</h5>
                                    <span><?= esc($validator ?: '—') ?></span>
                                </div>

                                <div class="col-12 col-md-6 info-cell info-cell-top info-cell-left">
                                    <h5 class="fw-semibold mb-1">Date de validation</h5>
                                    <p class="mb-0"><?= esc($fmtDate($validatedAt ?? null)) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header mb-2">
                            <h5 class="fw-semibold mb-0">Modification par rapport à l'existant</h5>
                        </div>

                        <div class="card-body">
                            <div class="d-flex flex-column gap-2">
                                <div><?= $cb($modKind === 'creation') ?> Création</div>
                                <div><?= $cb($modKind === 'replace') ?> Annule et remplace la version précédente</div>
                            </div>
                        </div>

                        <div class="card-header border-top mb-2">
                            <h5 class="fw-semibold mb-0">Historique des évolutions</h5>
                        </div>

                        <div class="card-body text-center">
                            <div class="row info-grid">

                                <div class="col-12 col-md-3 info-cell"><h5 class="fw-semibold">Version</h5></div>
                                <div class="col-12 col-md-3 info-cell"><h5 class="fw-semibold">Date</h5></div>
                                <div class="col-12 col-md-6 info-cell"><h5 class="fw-semibold">Commentaires</h5></div>

                                <?php if (empty($versions)): ?>
                                    <div class="col-12 info-cell text-muted">
                                        Aucun historique pour le moment.
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($versions as $v): ?>
                                        <?php
                                        $versionLabel = (string)($v['version_label'] ?? '—');
                                        $date         = $v['created_at'] ?? null;
                                        $comment      = trim((string)($v['comment'] ?? ''));
                                        ?>
                                        <div class="col-12 col-md-3 info-cell">
                                            <span class="mb-0"><?= esc($versionLabel) ?></span>
                                        </div>
                                        <div class="col-12 col-md-3 info-cell">
                                            <p class="mb-0"><?= esc($fmtDate($date)) ?></p>
                                        </div>
                                        <div class="col-12 col-md-6 info-cell">
                                            <p class="mb-0"><?= esc($comment !== '' ? $comment : '—') ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                            </div>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>

    <?php if (!empty($roots)): ?>
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="fw-semibold">Sommaire</span>
                <span class="text-muted small">Cliquez pour naviguer</span>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($roots as $r): ?>
                        <a class="btn btn-sm btn-outline-primary" href="#section-<?= (int)$r['id'] ?>">
                            <?= esc($r['code'] ?? '') ?>. <?= esc($r['title'] ?? '') ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php
    $render = function (array $node) use (&$render, $indentClass, $headingClass, $badgeLevel) {

        $level   = (int)($node['level'] ?? 1);
        $code    = (string)($node['code'] ?? '');
        $title   = (string)($node['title'] ?? '');
        $content = (string)($node['content'] ?? '');

        $wrap = $indentClass($level);
        $hCls = $headingClass($level);
        $bCls = $badgeLevel($level);

        $isRoot = ($level === 1);
        ?>

        <?php if ($isRoot): ?>
            <div class="card mb-4" id="section-<?= (int)$node['id'] ?>">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between gap-2">
                        <div class="<?= $hCls ?>">
                            <?php if ($code !== ''): ?>
                                <span class="badge <?= $bCls ?> me-2"><?= esc($code) ?></span>
                            <?php endif; ?>
                            <?= esc($title) ?>
                        </div>
                        <a href="#top" class="btn btn-sm btn-outline-secondary" title="Revenir en haut">↑</a>
                    </div>

                    <?php if (trim($content) !== ''): ?>
                        <div class="mt-2 p-3 rounded border report-content">
                            <?= clean_html($content) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($node['children'])): ?>
                        <div class="mt-3">
                            <?php foreach ($node['children'] as $child): ?>
                                <?php $render($child); ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <section class="mb-3 <?= $wrap ?>" id="section-<?= (int)$node['id'] ?>">
                <div class="<?= $hCls ?>">
                    <?php if ($code !== ''): ?>
                        <span class="badge <?= $bCls ?> me-2"><?= esc($code) ?></span>
                    <?php endif; ?>
                    <?= esc($title) ?>
                </div>

                <?php if (trim($content) !== ''): ?>
                    <div class="p-3 rounded border report-content">
                        <?= clean_html($content) ?>
                    </div>
                <?php else: ?>
<!--                    <div class="text-muted small">Contenu non renseigné.</div>-->
                <?php endif; ?>

                <?php if (!empty($node['children'])): ?>
                    <div class="mt-3">
                        <?php foreach ($node['children'] as $child): ?>
                            <?php $render($child); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php
    };
    ?>

    <?php if (empty($sectionsTree)): ?>
        <div class="alert alert-info mb-0">Aucune section pour l’instant.</div>
    <?php else: ?>
        <?php foreach ($sectionsTree as $root): ?>
            <?php $render($root); ?>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<script>
    (function () {
        const OFFSET = <?= (int)$scrollOffset ?>;

        function scrollToHash(hash) {
            if (!hash) return;
            const target = document.querySelector(hash);
            if (!target) return;

            const top = target.getBoundingClientRect().top + window.pageYOffset - OFFSET;
            window.scrollTo({top, behavior: 'smooth'});
        }

        document.addEventListener('click', function (e) {
            const a = e.target.closest('a[href^="#"]');
            if (!a) return;

            const hash = a.getAttribute('href');
            if (!hash || hash === '#') return;

            if (!hash.startsWith('#section-') && hash !== '#top') return;

            e.preventDefault();
            history.pushState(null, '', hash);
            scrollToHash(hash);
        });

        window.addEventListener('load', function () {
            if (window.location.hash) scrollToHash(window.location.hash);
        });
    })();
</script>

<style>
    .report-content img {
        max-width: 100%;
        height: auto;
        border-radius: .5rem;
        display: block;
        margin-left: auto;
        margin-right: auto;
    }

    .report-content p {
        margin-bottom: .75rem;
    }

    .report-content ul, .report-content ol {
        padding-left: 1.2rem;
    }

    .report-content blockquote {
        border-left: 4px solid #ddd;
        padding-left: .75rem;
        color: #555;
    }

    .report-content table {
        width: 100%;
        border-collapse: collapse;
    }

    .report-content th, .report-content td {
        border: 1px solid #ddd;
        padding: .5rem;
    }

    .info-grid {
        border-top: 1px solid #dee2e6;
        border-left: 1px solid #dee2e6;
    }

    .info-cell {
        padding: 1rem 1.25rem;
        border-right: 1px solid #dee2e6;
        border-bottom: 1px solid #dee2e6;
    }

    @media (max-width: 767.98px) {
        .info-grid { border-left: 0; }
        .info-cell { border-right: 0; }
    }
</style>
