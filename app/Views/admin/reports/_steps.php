<?php
/**
 * Variables attendues :
 * - $step : 'write' | 'preview'
 * - $reportId : int|null
 * - $canEdit : bool|null (optionnel)
 */
$step     = $step ?? 'preview';
$reportId = $reportId ?? null;
$canEdit  = $canEdit ?? null;

$reportIdInt = (int)($reportId ?? 0);

// Liens
$writeUrl   = $reportIdInt > 0 ? site_url('admin/reports/' . $reportIdInt . '/sections') : '#';
$previewUrl = $reportIdInt > 0 ? site_url('admin/reports/' . $reportIdInt) : '#';

// Règles
$writeDisabled = ($reportIdInt <= 0) || ($canEdit === false);
$prevDisabled  = ($reportIdInt <= 0);
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">

    <ul class="nav nav-pills">
        <!-- RÉDACTION -->
        <li class="nav-item">
            <?php if ($writeDisabled): ?>
                <span class="nav-link disabled">
                    1) Rédaction
                </span>
            <?php else: ?>
                <a class="nav-link <?= $step === 'write' ? 'active' : '' ?>"
                   href="<?= $writeUrl ?>">
                    1) Rédaction
                </a>
            <?php endif; ?>
        </li>

        <!-- APERÇU -->
        <li class="nav-item">
            <?php if ($prevDisabled): ?>
                <span class="nav-link disabled">
                    2) Aperçu
                </span>
            <?php else: ?>
                <a class="nav-link <?= $step === 'preview' ? 'active' : '' ?>"
                   href="<?= $previewUrl ?>">
                    2) Aperçu
                </a>
            <?php endif; ?>
        </li>
    </ul>
</div>
