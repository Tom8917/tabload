<form action="<?= isset($eliquid) ? base_url('/admin/eliquid/update') : base_url('/admin/eliquid/create') ?>" method="POST">
    <?php if (isset($eliquid)): ?>
        <input type="hidden" name="id" value="<?= esc($eliquid['id']) ?>">
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header">
            <h4><?= isset($eliquid) ? 'Modifier' : 'Créer' ?> un e-liquide</h4>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label>Nom*</label>
                <input type="text" name="name" class="form-control" value="<?= esc($eliquid['name'] ?? '') ?>" required>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Volume (ml)*</label>
                    <input type="number" name="volume_ml" class="form-control" value="<?= esc($eliquid['volume_ml'] ?? '') ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Prix (€)*</label>
                    <input type="number" step="0.01" name="price" class="form-control" value="<?= esc($eliquid['price'] ?? '') ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Stock (unités)*</label>
                    <input type="number" name="stock" class="form-control" value="<?= esc($eliquid['stock'] ?? '') ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control"><?= esc($eliquid['description'] ?? '') ?></textarea>
            </div>

            <hr>

            <h5>Ingrédients</h5>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Ingrédient</label>
                    <select id="ingredientSelect" class="form-control">
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($ingredients as $i): ?>
                            <option value="<?= $i['id'] ?>" data-unit="<?= esc($i['unit']) ?>">
                                <?= esc($i['name']) ?> (<?= esc($i['type']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Quantité</label>
                    <input type="number" id="ingredientQty" class="form-control" step="0.01">
                </div>
                <div class="col-md-3">
                    <label>&nbsp;</label>
                    <button type="button" id="addIngredient" class="btn btn-primary w-100">Ajouter</button>
                </div>
            </div>

            <table class="table table-bordered" id="ingredientsTable">
                <thead>
                <tr>
                    <th>Nom</th>
                    <th>Quantité</th>
                    <th>Unité</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($recipes ?? [])): ?>
                    <?php foreach ($recipes as $r): ?>
                        <tr>
                            <td><?= esc($r['ingredient_name']) ?></td>
                            <td><?= esc($r['quantity']) ?></td>
                            <td><?= esc($r['unit']) ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-danger remove-ing">X</button>
                            </td>
                            <input type="hidden" name="ingredients[<?= $i ?>][id]" value="<?= $r['id_ingredient'] ?>">
                            <input type="hidden" name="ingredients[<?= $i ?>][quantity]" value="<?= $r['quantity'] ?>">
                            <input type="hidden" name="ingredients[<?= $i ?>][unit]" value="<?= $r['unit'] ?>">
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>

        </div>
        <div class="card-footer text-end">
            <a href="<?= base_url('/admin/eliquid') ?>" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-success">Enregistrer</button>
        </div>
    </div>
</form>

<script>
    $(document).ready(function () {
        $('#ingredientSelect').select2({
            theme: 'bootstrap-5',
            placeholder: 'Choisir un ingrédient',
            allowClear: true
        });

        $('#addIngredient').click(function () {
            const select = $('#ingredientSelect');
            const selectedId = select.val();
            const selectedText = select.find('option:selected').text();
            const unit = select.find('option:selected').data('unit');
            const qty = $('#ingredientQty').val();

            if (!selectedId || !qty || qty <= 0) {
                toastr.warning("Veuillez choisir un ingrédient et une quantité valide.");
                return;
            }

            const rowIndex = $('#ingredientsTable tbody tr').length;

            $('#ingredientsTable tbody').append(`
        <tr>
            <td>${selectedText}</td>
            <td>${qty}</td>
            <td>${unit}</td>
            <td><button type="button" class="btn btn-sm btn-danger remove-ing">X</button></td>
            <input type="hidden" name="ingredients[${rowIndex}][id]" value="${selectedId}">
            <input type="hidden" name="ingredients[${rowIndex}][quantity]" value="${qty}">
            <input type="hidden" name="ingredients[${rowIndex}][unit]" value="${unit}">
        </tr>
    `);

            select.val(null).trigger('change');
            $('#ingredientQty').val('');
        });
    });
</script>
