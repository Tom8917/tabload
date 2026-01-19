<?php
/**
 * Variables attendues :
 * - $step : 'config' | 'write' | 'preview'
 * - $reportId : int|null
 * - $canEdit : bool|null (optionnel : si tu veux griser "Rédaction" quand lecture seule)
 */
$step     = $step ?? 'config';
$reportId = $reportId ?? null;
$canEdit  = $canEdit ?? null;

// liens
$configUrl  = site_url('report/new');
$writeUrl   = $reportId ? site_url('report/' . $reportId . '/sections') : '#';
$previewUrl = $reportId ? site_url('report/' . $reportId) : '#';

// si tu veux : désactiver rédaction quand pas owner
$writeDisabled = ($reportId === null) || ($canEdit === false);
$prevDisabled  = ($reportId === null);
?>

<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link <?= $step === 'config' ? 'active' : '' ?>"
           href="<?= $configUrl ?>">
            1) Configuration
        </a>
    </li>

    <li class="nav-item">
        <?php if ($writeDisabled): ?>
            <span class="nav-link disabled">2) Rédaction</span>
        <?php else: ?>
            <a class="nav-link <?= $step === 'write' ? 'active' : '' ?>"
               href="<?= $writeUrl ?>">
                2) Rédaction
            </a>
        <?php endif; ?>
    </li>

    <li class="nav-item">
        <?php if ($prevDisabled): ?>
            <span class="nav-link disabled">3) Aperçu</span>
        <?php else: ?>
            <a class="nav-link <?= $step === 'preview' ? 'active' : '' ?>"
               href="<?= $previewUrl ?>">
                3) Aperçu
            </a>
        <?php endif; ?>
    </li>
</ul>
