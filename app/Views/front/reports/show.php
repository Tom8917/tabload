<?php
$sectionsTree = $sectionsTree ?? [];
$canEdit = $canEdit ?? false;

// helpers d'indentation
$indentClass = function(int $level): string {
    return match (true) {
        $level <= 1 => 'ms-0',
        $level === 2 => 'ms-3',
        default => 'ms-5',
    };
};

$headingClass = function(int $level): string {
    return match ($level) {
        1 => 'h4 fw-bold mb-2',
        2 => 'h5 fw-semibold mb-2',
        default => 'h6 fw-semibold mb-2 text-body',
    };
};

$badgeLevel = function(int $level): string {
    return match ($level) {
        1 => 'bg-primary',
        2 => 'bg-secondary',
        default => 'bg-dark',
    };
};

// Sommaire (roots uniquement)
$roots = $sectionsTree;

// ⚠️ Ajuste si tu as un header fixe (CoreUI/Bootstrap sticky topbar)
$scrollOffset = 90; // px
?>

<?php helper('html'); ?>

<style>
    /* Permet aux ancres (#section-xx) de ne pas être cachées derrière un header sticky */
    [id^="section-"], #top {
        scroll-margin-top: <?= (int)$scrollOffset ?>px;
    }
</style>

<div class="container-fluid">
    <div id="top"></div>

    <?= view('front/reports/_steps', [
        'step'     => 'preview',
        'reportId' => $report['id'],
        'canEdit'  => $canEdit,
    ]) ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><?= ($report['title']) ?></h1>
            <div class="text-muted small">
                Application : <?= ($report['application_name']) ?>
                <?php if (!empty($report['version'])): ?>
                    &nbsp;·&nbsp; Version : <?= ($report['version']) ?>
                <?php endif; ?>
                <?php if (!empty($report['author_name'])): ?>
                    &nbsp;·&nbsp; Auteur : <?= ($report['author_name']) ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="<?= site_url('report/' . $report['id'] . '/sections') ?>" class="btn btn-outline-secondary">
                Retour à la rédaction
            </a>
        </div>
    </div>

    <?php if (!empty($roots)): ?>
        <!-- Sommaire -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="fw-semibold">Sommaire</span>
                <span class="text-muted small">Cliquez pour naviguer</span>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($roots as $r): ?>
                        <a class="btn btn-sm btn-outline-primary"
                           href="#section-<?= (int)$r['id'] ?>">
                            <?= ($r['code'] ?? '') ?>. <?= ($r['title'] ?? '') ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php
    $render = function(array $node) use (&$render, $indentClass, $headingClass, $badgeLevel) {

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
                                <span class="badge <?= $bCls ?> me-2"><?= ($code) ?></span>
                            <?php endif; ?>
                            <?= ($title) ?>
                        </div>
                        <a href="#top" class="btn btn-sm btn-outline-secondary" title="Revenir en haut">↑</a>
                    </div>

                    <?php if (trim($content) !== ''): ?>
                        <div class="mt-2 p-3 rounded bg-light border report-content">
                            <?= clean_html($content) ?>
                        </div>
                    <?php else: ?>

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
                        <span class="badge <?= $bCls ?> me-2"><?= ($code) ?></span>
                    <?php endif; ?>
                    <?= ($title) ?>
                </div>

                <?php if (trim($content) !== ''): ?>
                    <div class="p-3 rounded bg-white border report-content">
                        <?= clean_html($content) ?>
                    </div>
                <?php else: ?>
                    <div class="text-muted small">Contenu non renseigné.</div>
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
    (function() {
        const OFFSET = <?= (int)$scrollOffset ?>;

        function scrollToHash(hash) {
            if (!hash) return;
            const target = document.querySelector(hash);
            if (!target) return;

            const top = target.getBoundingClientRect().top + window.pageYOffset - OFFSET;
            window.scrollTo({ top, behavior: 'smooth' });
        }

        // Intercepte clics sur ancres internes
        document.addEventListener('click', function(e) {
            const a = e.target.closest('a[href^="#"]');
            if (!a) return;

            const hash = a.getAttribute('href');
            if (!hash || hash === '#') return;

            // uniquement nos ancres utiles
            if (!hash.startsWith('#section-') && hash !== '#top') return;

            e.preventDefault();
            history.pushState(null, '', hash); // garde l’URL avec l’ancre
            scrollToHash(hash);
        });

        // Si on arrive déjà avec une ancre dans l'URL
        window.addEventListener('load', function() {
            if (window.location.hash) {
                scrollToHash(window.location.hash);
            }
        });
    })();
</script>

<style>
    .report-content img {
        max-width: 100%;
        height: auto;
        border-radius: .5rem;
    }
    .report-content p { margin-bottom: .75rem; }
    .report-content ul, .report-content ol { padding-left: 1.2rem; }
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
</style>