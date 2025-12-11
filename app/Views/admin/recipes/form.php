<form method="post" enctype="multipart/form-data" action="<?= base_url('/admin/recipe/' . (isset($recipe['id']) ? 'update' : 'create')) ?>">
    <?= csrf_field() ?>
    <?php if (!empty($recipe['id'])): ?>
        <input type="hidden" name="id" value="<?= $recipe['id'] ?>">
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header">
            <h4><?= isset($recipe) ? 'Modifier' : 'Ajouter' ?> une recette</h4>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label>Nom de la recette *</label>
                <input type="text" name="name" class="form-control" required value="<?= esc($recipe['name'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3"><?= esc($recipe['description'] ?? '') ?></textarea>
            </div>

            <div class="mb-3">
                <label>Choisir une fiole (définit le volume)</label>
                <select name="role_id[fiole]" id="fioleSelect" class="form-select" required>
                    <option value="">-- Choisir une fiole --</option>
                    <?php foreach ($itemsByRole['fiole'] ?? [] as $item): ?>
                        <option value="<?= $item['id'] ?>"
                                data-volume="<?= $item['unit_volume_ml'] ?>"
                            <?= (isset($selected['fiole']) && $selected['fiole'] == $item['id']) ? 'selected' : '' ?>>
                            <?= esc($item['name']) ?> (<?= $item['unit_volume_ml'] ?> ml)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label>Base</label>
                <select name="role_id[base]" class="form-select" required>
                    <option value="">-- Choisir une base --</option>
                    <?php foreach ($itemsByRole['base'] ?? [] as $item): ?>
                        <option value="<?= $item['id'] ?>" <?= (isset($selected['base']) && $selected['base'] == $item['id']) ? 'selected' : '' ?>>
                            <?= esc($item['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label>Arôme</label>
                <select name="role_id[concentrate]" class="form-select" required>
                    <option value="">-- Choisir un arôme --</option>
                    <?php foreach ($itemsByRole['concentrate'] ?? [] as $item): ?>
                        <option value="<?= $item['id'] ?>" <?= (isset($selected['concentrate']) && $selected['concentrate'] == $item['id']) ? 'selected' : '' ?>>
                            <?= esc($item['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label>Booster de nicotine</label>
                <select name="role_id[nicotine]" class="form-select">
                    <option value="">-- Choisir un booster --</option>
                    <?php foreach ($itemsByRole['nicotine'] ?? [] as $item): ?>
                        <option value="<?= $item['id'] ?>"
                            <?= (isset($selected['nicotine']) && $selected['nicotine'] == $item['id']) ? 'selected' : '' ?>>
                            <?= esc($item['name']) ?>
                        </option>
                    <?php endforeach; ?>
                    <?php if (empty($itemsByRole['nicotine'])): ?>
                        <div class="text-danger small mt-1">⚠️ Aucun booster de nicotine disponible en stock</div>
                    <?php endif; ?>
                </select>
            </div>

            <div class="mb-3">
                <label>Taux de nicotine souhaité (mg/ml)</label>
                <select name="nicotine_target" id="nicotineSelect" class="form-select">
                    <?php foreach ([0, 3, 6, 9, 12] as $rate): ?>
                        <option value="<?= $rate ?>" <?= (isset($recipe['nicotine']) && (int)$recipe['nicotine'] == $rate) ? 'selected' : '' ?>>
                            <?= $rate ?> mg/ml
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="dosagePreview" class="mb-3 text-muted small"></div>

            <input type="hidden" name="volume_ml" id="volumeFinal">
            <input type="hidden" name="dosage[base]" id="dosageBase">
            <input type="hidden" name="dosage[concentrate]" id="dosageConcentrate">
            <input type="hidden" name="dosage[nicotine]" id="dosageNicotine">

            <div class="mb-3">
                <label>Image</label>
                <input type="file" name="image" class="form-control">
                <?php if (!empty($recipe['image'])): ?>
                    <img src="<?= base_url('uploads/recipes/' . $recipe['image']) ?>" class="img-thumbnail mt-2" width="120">
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <b>Coût de revient estimé :</b>
                <span id="costEstimation">sera calculé</span>
            </div>

            <div class="mb-3">
                <label>Prix de vente (en €)</label>
                <input type="number" name="price" step="0.01" min="0" class="form-control" value="<?= esc($recipe['price'] ?? '') ?>">
            </div>
        </div>

        <div class="card-footer text-end">
            <a href="<?= base_url('/admin/recipe') ?>" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-success">Enregistrer</button>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fioleSelect = document.getElementById('fioleSelect');
        const nicotineSelect = document.getElementById('nicotineSelect');
        const volumeInput = document.getElementById('volumeFinal');
        const preview = document.getElementById('dosagePreview');

        function updateDosages() {
            const fioleOption = fioleSelect.options[fioleSelect.selectedIndex];
            const volume = parseFloat(fioleOption?.dataset?.volume) || 100;
            const rate = parseFloat(nicotineSelect.value) || 0;

            const base = volume * 0.75;
            let concentrate = volume * 0.25;
            let nicotine = 0;

            if (rate > 0) {
                nicotine = (volume * rate) / 200;
                concentrate = Math.max(0, concentrate - nicotine);
            }

            document.getElementById('dosageBase').value = base;
            document.getElementById('dosageConcentrate').value = concentrate;
            document.getElementById('dosageNicotine').value = nicotine;
            volumeInput.value = volume;

            preview.innerHTML = `
                <b>Dosage calculé :</b><br>
                Volume : ${volume} ml<br>
                Base : ${base} ml<br>
                Arôme : ${concentrate} ml<br>
                Nicotine : ${nicotine} ml
            `;
        }

        fioleSelect.addEventListener('change', updateDosages);
        nicotineSelect.addEventListener('change', updateDosages);
        updateDosages();
    });
</script>
