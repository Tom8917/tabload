<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Détails de la réception #<?= esc($reception['id']) ?></h4>
        <a href="<?= base_url('/admin/stockreception') ?>" class="btn btn-secondary btn-sm">
            ← Retour à la liste
        </a>
    </div>

    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">ID</dt>
            <dd class="col-sm-9"><?= esc($reception['id']) ?></dd>

            <dt class="col-sm-3">Produit final</dt>
            <dd class="col-sm-9">
                <?= esc($product['type_name']) ?> — <?= esc($product['provider_name']) ?>
                (<?= number_format($product['unit_volume_ml'], 0, ',', ' ') ?> ml / unité)
            </dd>

            <dt class="col-sm-3">Quantité reçue</dt>
            <dd class="col-sm-9">
                <?= number_format($reception['units'], 2, ',', ' ') ?> unité(s)
            </dd>

            <dt class="col-sm-3">Prix unitaire (indiqué)</dt>
            <dd class="col-sm-9">
                <?= $reception['unit_price'] !== null
                    ? number_format($reception['unit_price'], 4, ',', ' ') . ' €'
                    : '—' ?>
            </dd>

            <dt class="col-sm-3">Prix total d’achat</dt>
            <dd class="col-sm-9">
                <?= $reception['price_total'] !== null
                    ? number_format($reception['price_total'], 2, ',', ' ') . ' €'
                    : '—' ?>
            </dd>

            <dt class="col-sm-3">Note</dt>
            <dd class="col-sm-9">
                <?= nl2br(esc($reception['note'] ?? '—')) ?>
            </dd>

            <dt class="col-sm-3">Créé le</dt>
            <dd class="col-sm-9">
                <?= date('d/m/Y H:i', strtotime($reception['created_at'])) ?>
            </dd>

            <dt class="col-sm-3">Mis à jour le</dt>
            <dd class="col-sm-9">
                <?= date('d/m/Y H:i', strtotime($reception['updated_at'])) ?>
            </dd>
        </dl>
    </div>
</div>
