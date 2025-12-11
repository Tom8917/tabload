<div class="container py-4">
    <h1 class="mb-4">Mon panier</h1>

    <?php if (empty($items)): ?>
        <div class="alert alert-info text-center">
            Votre panier est vide.
        </div>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead class="table-light">
                        <tr>
                            <th>Recette</th>
                            <th class="text-center">Quantité</th>
                            <th class="text-end">Prix</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $total = 0; ?>
                        <?php foreach ($items as $item): ?>
                            <?php $lineTotal = $item['qty'] * $item['price']; ?>
                            <tr>
                                <td><?= esc($item['name']) ?></td>
                                <td class="text-center"><?= $item['qty'] ?></td>
                                <td class="text-end"><?= number_format($item['price'], 2, ',', ' ') ?> €</td>
                                <td class="text-end"><?= number_format($lineTotal, 2, ',', ' ') ?> €</td>
                                <td class="text-center">
                                    <form method="post" action="<?= site_url('/cart/remove/' . $item['id']) ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Retirer">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php $total += $lineTotal; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
                    <h5 class="mb-0">Total : <?= number_format($total, 2, ',', ' ') ?> €</h5>
                </div>

                <div class="text-end mt-4">
                    <form action="<?= site_url('/cart/checkout') ?>" method="post">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-info btn-lg">
                            <i class="fa fa-credit-card"></i> Payer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
