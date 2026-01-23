<?php
/**
 * Variables attendues :
 * - $step : 'config' | 'write' | 'preview'
 * - $reportId : int|null
 * - $canEdit : bool|null
 */
$step     = $step ?? 'config';
$reportId = $reportId ?? null;
$canEdit  = $canEdit ?? null;

$configUrl  = site_url('report/new');
$writeUrl   = $reportId ? site_url('report/' . $reportId . '/sections') : '#';
$previewUrl = $reportId ? site_url('report/' . $reportId) : '#';

$configLocked = ($reportId !== null);
$writeDisabled = ($reportId === null) || ($canEdit === false);
$prevDisabled  = ($reportId === null);
?>

<?php if ($writeDisabled): ?>

<?php else: ?>
<ul class="nav nav-pills mb-4">

    <li class="nav-item">
        <?php if ($configLocked): ?>
            <span class="nav-link disabled">
                1) Configuration
            </span>
        <?php else: ?>
            <a class="nav-link <?= $step === 'config' ? 'active' : '' ?>"
               href="<?= $configUrl ?>">
                1) Configuration
            </a>
        <?php endif; ?>
    </li>

    <li class="nav-item">
        <?php if ($writeDisabled): ?>
            <span class="nav-link disabled">
                2) Rédaction
            </span>
        <?php else: ?>
            <a class="nav-link <?= $step === 'write' ? 'active' : '' ?>"
               href="<?= $writeUrl ?>">
                2) Rédaction
            </a>
        <?php endif; ?>
    </li>

    <li class="nav-item">
        <?php if ($prevDisabled): ?>
            <span class="nav-link disabled">
                3) Aperçu
            </span>
        <?php else: ?>
            <a class="nav-link <?= $step === 'preview' ? 'active' : '' ?>"
               href="<?= $previewUrl ?>">
                3) Aperçu
            </a>
        <?php endif; ?>
    </li>

</ul>
<?php endif; ?>
