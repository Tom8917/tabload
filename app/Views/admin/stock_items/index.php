<h1 class="mb-4 text-center">Gestion des stocks</h1>

<div class="mb-3 d-flex gap-2">
    <a href="<?= base_url('admin/stockitem/setAll/100') ?>" class="btn btn-primary btn-sm">
        <i class="fa fa-sync"></i> Tout à 100
    </a>
    <a href="<?= base_url('admin/stockitem/setAll/10') ?>" class="btn btn-warning btn-sm">
        <i class="fa fa-sync"></i> Tout à 10
    </a>
    <a href="<?= base_url('admin/stockitem/setAll/1') ?>" class="btn btn-light btn-sm">
        <i class="fa fa-sync"></i> Tout à 1
    </a>
    <a href="<?= base_url('admin/stockitem/setAll/0') ?>" class="btn btn-secondary btn-sm">
        <i class="fa fa-sync-alt"></i> Tout à 0
    </a>
</div>

<?php
// Liste unique de produits : Type + Fournisseur
$productLabels = [];
foreach ($items as $item) {
    $label = $item['type_name'] . ' — ' . $item['provider_name'];
    $productLabels[$label] = true;
}
ksort($productLabels);
?>

<form id="filterForm" class="mb-3">
    <div class="row g-2 align-items-center">
        <div class="col-auto">
            <label for="typeFilter" class="col-form-label fw-bold">Filtrer par produit :</label>
        </div>
        <div class="col-auto">
            <select id="typeFilter" class="form-select form-select-sm">
                <option value="">Tous les produits</option>
                <?php foreach (array_keys($productLabels) as $label): ?>
                    <option value="<?= esc($label) ?>"><?= esc($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</form>

<table class="table table-striped table-bordered mt-4 align-middle" id="stockTable">
    <thead>
    <tr>
        <th>Produit</th>
        <th>Fournisseur</th>
        <th>Qté</th>
        <th>Volume/u</th>
        <th>Total volume</th>
        <th>Prix/u (€)</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $item): ?>
        <?php
        $label     = $item['type_name'] . ' — ' . $item['provider_name'];
        $qty       = (float)$item['quantity'];
        $unitMl    = (float)$item['unit_volume_ml'];
        $totalMl   = $qty * $unitMl;

        $qtyClass  = match(true) {
            $qty <= 5  => 'bg-danger text-white',
            $qty <= 10 => 'bg-warning text-dark',
            $qty <= 20 => 'bg-success text-white',
            default    => 'bg-primary text-white',
        };
        ?>
        <tr data-product="<?= strtolower($label) ?>">
            <td><?= esc($item['type_name']) ?></td>
            <td><?= esc($item['provider_name']) ?></td>
            <td class="text-center">
            <span class="badge <?= $qtyClass ?> px-3 py-2">
                <?= number_format($qty, 2, ',', ' ') ?>
            </span>
            </td>
            <td class="text-center"><?= esc($item['unit_volume_ml']) ?> ml</td>
            <td class="text-center"><?= number_format($totalMl, 2, ',', ' ') ?> ml</td>
            <td class="text-center">€ <?= number_format($item['unit_price'], 4, ',', ' ') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        // Toastr config
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };

        // Flash messages
        <?php if ($msg = session()->getFlashdata('success')): ?>
        toastr.success("<?= esc($msg) ?>");
        <?php endif; ?>

        <?php if ($msg = session()->getFlashdata('error')): ?>
        toastr.error("<?= esc($msg) ?>");
        <?php endif; ?>

        <?php if ($msg = session()->getFlashdata('warning')): ?>
        toastr.warning("<?= esc($msg) ?>");
        <?php endif; ?>

        <?php if ($msg = session()->getFlashdata('info')): ?>
        toastr.info("<?= esc($msg) ?>");
        <?php endif; ?>

        // Filtre par produit (type + fournisseur)
        const filterSelect = document.getElementById('typeFilter');
        if (filterSelect) {
            filterSelect.addEventListener('change', function () {
                const selected = this.value.toLowerCase();
                const rows = document.querySelectorAll('#stockTable tbody tr');

                rows.forEach(row => {
                    const label = row.dataset.product;
                    row.style.display = (selected === '' || label === selected) ? '' : 'none';
                });
            });
        }
    });
</script>

<style>
    #toast-container.toast-top-right {
        top: 75px;
        right: 12px;
    }

    @media (max-width: 576px) {
        table td,
        table th {
            white-space: normal !important;
            word-break: break-word;
            font-size: 0.875rem;
        }
    }

    .stock-box-danger {
        background-color: #f8d7da;
        border: 1px solid #f5c2c7;
    }

    .stock-box-warning {
        background-color: #fff3cd;
        border: 1px solid #ffecb5;
    }

    .stock-box-success {
        background-color: #d1e7dd;
        border: 1px solid #badbcc;
    }

    .stock-box-primary {
        background-color: #cfe2ff;
        border: 1px solid #9ec5fe;
    }

    .text-center {
        text-align: center;
    }
</style>
