<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Produits finis</h4>
        <a href="<?= base_url('/admin/stockproduct/new') ?>" class="btn btn-primary">
            <i class="fa fa-plus"></i> Ajouter
        </a>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Type</th>
                    <th>Fournisseur</th>
                    <th>Volume (ml)</th>
                    <th>Prix unitaire (€)</th>
                    <th class="text-center">Modifier</th>
                    <th class="text-center">Supprimer</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= esc($product['id']) ?></td>
                        <td class="text-center">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?= base_url('uploads/stock_products/' . esc($product['image'])) ?>"
                                     alt="Image produit"
                                     style="max-height: 80px; object-fit: contain;">
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($product['type_name']) ?></td>
                        <td><?= esc($product['provider_name']) ?></td>
                        <td><?= number_format($product['unit_volume_ml'], 2, ',', ' ') ?></td>
                        <td><?= number_format($product['unit_price'], 4, ',', ' ') ?></td>
                        <td class="text-center">
                            <a href="<?= base_url('/admin/stockproduct/edit/' . $product['id']) ?>" class="btn btn-sm"><i class="fa fa-pencil-alt"></i></a>
                        </td>
                        <td class="text-center">
                            <a href="<?= base_url('/admin/stockproduct/delete/' . $product['id']) ?>" class="btn btn-sm text-danger delete-btn" data-id="<?= $product['id'] ?>"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
