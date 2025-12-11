<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success">
        <?= session('success') ?>
    </div>
<?php elseif (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger">
        <?= session('error') ?>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><i class="fa-solid fa-dolly me-2"></i>Historique des réceptions</h4>
        <a href="<?= base_url('/admin/stockreception/new') ?>" class="btn btn-primary btn-sm">
            <i class="fa fa-plus me-1"></i> Nouvelle réception
        </a>
    </div>
    <div class="card-body">
        <?php if (empty($receptions)): ?>
            <p class="text-muted">Aucune réception enregistrée.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Quantité</th>
                        <th>Prix unitaire (€)</th>
                        <th>Total (€)</th>
                        <th>Date</th>
                        <th class="text-center">Détail</th>
                        <th class="text-center">Modifier</th>
                        <th class="text-center">Supprimer</th>
                    </tr>
                    <tbody>
                    <?php foreach ($receptions as $recv): ?>
                        <?php
                        $unitPrice = ($recv['units'] > 0) ? $recv['price_total'] / $recv['units'] : 0;
                        $expected = (float) $recv['expected_price'];
                        $totalMl = $recv['unit_volume_ml'] * $recv['units'];

// Couleurs personnalisées
                        $priceBadge = 'bg-secondary';
                        if ($unitPrice < $expected) {
                            $priceBadge = 'bg-info text-white';
                        } elseif ($unitPrice <= $expected * 1.05) {
                            $priceBadge = 'bg-success text-white';
                        } elseif ($unitPrice <= $expected * 1.15) {
                            $priceBadge = 'bg-warning text-white'; // ajouter la classe en CSS ci-dessous
                        } else {
                            $priceBadge = 'bg-danger';
                        }
                        ?>
                        <tr>
                            <td>#<?= esc($recv['id']) ?></td>
                            <td class="d-flex align-items-center gap-2">
                                <?php if (!empty($recv['type_image'])): ?>
                                    <img src="<?= base_url('uploads/stock_types/' . esc($recv['type_image'])) ?>"
                                         style="height: 50px; width: auto;" alt="">
                                <?php endif; ?>
                                <?= esc($recv['type_name']) ?> — <?= esc($recv['provider_name']) ?>
                            </td>
                            <td>
            <span class="badge bg-secondary px-3 py-2">
                <?= number_format($recv['units'], 2, ',', ' ') ?> u<br>
                <small><?= number_format($totalMl, 2, ',', ' ') ?> ml</small>
            </span>
                            </td>
                            <td>
    <span class="badge <?= $priceBadge ?>">
        <?= number_format($unitPrice, 4, ',', ' ') ?> €
    </span>
                            </td>

                            <td>€ <?= number_format($recv['price_total'], 2, ',', ' ') ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($recv['created_at'])) ?></td>
                            <td class="text-center">
                                <a href="<?= base_url('/admin/stockreception/show/' . $recv['id']) ?>" class="btn btn-sm"><i class="fa fa-eye"></i></a>
                            </td>
                            <td class="text-center">
                                <a href="<?= base_url('/admin/stockreception/edit/' . $recv['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fa fa-edit"></i></a>
                            </td>
                            <td class="text-center">
                                <button data-id="<?= $recv['id'] ?>" class="btn btn-outline-danger btn-sm delete-btn"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>

                </table>
            </div>
        <?php endif ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;

                Swal.fire({
                    title: "Supprimer cette réception ?",
                    text: "Cette action est irréversible.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Oui, supprimer",
                    cancelButtonText: "Annuler"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "<?= base_url('admin/stockreception/delete/') ?>" + id;
                    }
                });
            });
        });
    });
</script>

<style>
    .badge {
        font-size: 0.95rem;
    }
</style>
