<form action="<?= isset($reception) ? base_url('/admin/stockreception/update') : base_url('/admin/stockreception/create') ?>" method="post">
    <?= csrf_field() ?>

    <?php if (isset($reception)): ?>
        <input type="hidden" name="id" value="<?= esc($reception['id']) ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label for="id_stock_product">Produit final *</label>
        <select name="id_stock_product" id="id_stock_product" class="form-select" required>
            <option value="">Sélectionner…</option>
            <?php foreach ($products as $product): ?>
                <option value="<?= $product['id'] ?>" <?= (isset($reception) && $reception['id_stock_product'] == $product['id']) ? 'selected' : '' ?>>
                    <?= esc($product['type_name']) ?> — <?= esc($product['provider_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="units">Quantité reçue *</label>
        <input type="number" step="0.01" name="units" id="units" class="form-control"
               value="<?= old('units', $reception['units'] ?? '') ?>" required>
    </div>

    <div class="mb-3">
        <label for="price_total">Prix total (€) *</label>
        <input type="number" step="0.01" name="price_total" id="price_total" class="form-control"
               value="<?= old('price_total', $reception['price_total'] ?? '') ?>" required>
    </div>

    <div class="mb-2" id="priceCheckBadge"></div>

    <div class="mb-3">
        <label for="note">Note (facultatif)</label>
        <textarea name="note" id="note" class="form-control" rows="3"><?= old('note', $reception['note'] ?? '') ?></textarea>
    </div>

    <div class="text-end">
        <a href="<?= base_url('/admin/stockreception') ?>" class="btn btn-secondary">Annuler</a>
        <button type="submit" class="btn btn-success">
            <?= isset($reception) ? 'Mettre à jour' : 'Enregistrer' ?>
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const unitsInput = document.getElementById('units');
        const priceTotalInput = document.getElementById('price_total');
        const productSelect = document.getElementById('id_stock_product');
        const badge = document.getElementById('priceCheckBadge');

        const products = <?= json_encode($products) ?>;

        function updateBadge() {
            const qty = parseFloat(unitsInput.value);
            const total = parseFloat(priceTotalInput.value);
            badge.innerHTML = '';

            if (qty > 0 && !isNaN(total)) {
                const unitPrice = total / qty;

                const selectedId = productSelect.value;
                const selected = products.find(p => p.id == selectedId);
                if (selected && !isNaN(parseFloat(selected.unit_price))) {
                    const expected = parseFloat(selected.unit_price);

                    let colorClass = 'bg-secondary';
                    if (unitPrice < expected) {
                        colorClass = 'bg-info text-white';
                    } else if (unitPrice <= expected * 1.05) {
                        colorClass = 'bg-success text-white';
                    } else if (unitPrice <= expected * 1.15) {
                        colorClass = 'bg-warning text-white';
                    } else {
                        colorClass = 'bg-danger';
                    }

                    badge.innerHTML = `<span class="badge ${colorClass}">≈ ${unitPrice.toFixed(4)} €</span>`;
                }
            }
        }

        unitsInput.addEventListener('input', updateBadge);
        priceTotalInput.addEventListener('input', updateBadge);
        productSelect.addEventListener('change', updateBadge);

        updateBadge(); // Initialisation
    });
</script>