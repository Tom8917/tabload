<form action="<?= base_url(!empty($item['id']) ? '/admin/stockitem/update' : '/admin/stockitem/create') ?>"
      method="post"
      enctype="multipart/form-data">
    <?php if (!empty($item['id'])): ?>
        <input type="hidden" name="id" value="<?= esc($item['id']) ?>">
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header">
            <h4><?= isset($item) ? 'Modifier' : 'Ajouter' ?> un produit en stock</h4>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="id_stock_type">Type de produit*</label>
                    <select name="id_stock_type"
                            id="stockTypeSelect"
                            class="form-control"
                            required>
                        <option value="">-- Choisir un type --</option>
                        <?php foreach ($stockTypes as $type): ?>
                            <option value="<?= $type['id'] ?>"
                                <?= isset($item['id_stock_type']) && $item['id_stock_type'] == $type['id']
                                    ? 'selected' : '' ?>>
                                <?= esc($type['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="name">Nom du produit (client)</label>
                    <input type="text"
                           name="name"
                           class="form-control"
                           value="<?= esc($item['name'] ?? '') ?>">
                </div>

                <div class="col-md-4">
                    <label for="quantity">Quantité (unités)*</label>
                    <input type="number"
                           step="0.01"
                           name="quantity"
                           class="form-control"
                           required
                           value="<?= esc($item['quantity'] ?? '') ?>">
                </div>
            </div>

<!--            <div id="dynamicFields" class="mb-3 mt-2"></div>-->
        </div>

        <div class="card-footer text-end">
            <a href="<?= base_url('/admin/stockitem') ?>" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-success">Enregistrer</button>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const priceTotal = document.querySelector('[name="purchase_price_total"]');
        const quantity   = document.querySelector('[name="quantity"]');
        const volume     = document.querySelector('[name="unit_volume_ml"]');
        const priceUnit  = document.querySelector('[name="purchase_price_unit"]');
    });

    $(document).ready(function () {
        const container = $('#dynamicFields');

        $('#stockTypeSelect').change(function () {
            const typeName = $('#stockTypeSelect option:selected').text().toLowerCase();
            container.empty();

            // Si le nom du type contient "base"
            //if (typeName.includes('base')) {
            //    container.append(`
            //        <div class="row mb-3">
            //            <div class="col-md-4">
            //                <label>Ratio PG (%)</label>
            //                <input type="number"
            //                       name="pg"
            //                       class="form-control"
            //                       min="0"
            //                       max="100"
            //                       value="<?php //= esc($item['pg'] ?? '') ?>//">
            //            </div>
            //            <div class="col-md-4">
            //                <label>Ratio VG (%)</label>
            //                <input type="number"
            //                       name="vg"
            //                       class="form-control"
            //                       min="0"
            //                       max="100"
            //                       value="<?php //= esc($item['vg'] ?? '') ?>//">
            //            </div>
            //            <div class="col-md-4">
            //                <label>Niveau de nicotine (mg/ml)</label>
            //                <input type="number"
            //                       name="nicotine"
            //                       class="form-control"
            //                       step="0.1"
            //                       value="<?php //= esc($item['nicotine'] ?? '') ?>//">
            //            </div>
            //        </div>
            //    `);
            //}

            // Si le nom du type contient "fiole"
            if (typeName.includes('fiole')) {
                container.append(`
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label>Avec bouchon sécurisé ?</label>
                            <select name="secure_cap" class="form-control">
                                <option value="1" <?= isset($item['secure_cap']) && $item['secure_cap'] ? 'selected':'' ?>>Oui</option>
                                <option value="0" <?= isset($item['secure_cap']) && !$item['secure_cap']  ? 'selected':'' ?>>Non</option>
                            </select>
                        </div>
                    </div>
                `);
            }

            // Si le nom du type contient "arôme" ou "concentrate"
            if (typeName.includes('arôme') || typeName.includes('concentrate')) {
                container.append(`
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Dosage recommandé (%)</label>
                            <input type="number"
                                   name="dosage"
                                   class="form-control"
                                   step="0.01"
                                   value="<?= esc($item['dosage'] ?? '') ?>">
                        </div>
                    </div>
                `);
            }


            if (typeName.includes('nicotine') || typeName.includes('concentrate')) {
                container.append(`
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <label>mg/ml (%)</label>
                            <input type="number"
                                   name="dosage"
                                   class="form-control"
                                   step="0.01"
                                   value="<?= esc($item['dosage'] ?? '') ?>">
                        </div>
                    </div>
                `);
            }
        });

        // Si on est en édition, on déclenche l’event pour pré-remplir
        <?php if (! empty($item['id_stock_type'])): ?>
        $('#stockTypeSelect').trigger('change');
        <?php endif; ?>
    });
</script>
