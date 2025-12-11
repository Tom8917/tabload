<div class="container py-4">
    <h1>Commandes validées</h1>

    <?php if (empty($orders)): ?>
        <p class="text-muted">Aucune commande pour le moment.</p>
    <?php else: ?>
        <div class="accordion" id="accordionOrders">
            <?php foreach ($orders as $order): ?>
                <div class="accordion-item mb-2">
                    <h2 class="accordion-header" id="heading-<?= $order['id'] ?>">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapse-<?= $order['id'] ?>" aria-expanded="false"
                                aria-controls="collapse-<?= $order['id'] ?>">
                            Commande #<?= esc($order['id']) ?> — le <?= esc($order['created_at']) ?>
                        </button>
                    </h2>
                    <div id="collapse-<?= $order['id'] ?>" class="accordion-collapse collapse"
                         aria-labelledby="heading-<?= $order['id'] ?>" data-bs-parent="#accordionOrders">
                        <div class="accordion-body">
                            <ul class="list-group">
                                <?php foreach ($order['items'] as $item): ?>
                                    <li class="list-group-item">
                                        <?= esc($item['ingredient_name']) ?> :
                                        <?= esc($item['quantity_ml']) ?> ml
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
