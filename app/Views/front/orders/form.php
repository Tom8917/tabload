<form action="<?= site_url('order/create') ?>" method="post">
    <?= csrf_field() ?>

    <h3>Composition de votre e-liquide</h3>

    <!-- 1) Sélection de l’arôme (concentré) -->
    <div class="mb-3">
        <label for="concentrate_id">Arôme (concentré) *</label>
        <select name="concentrate_id" id="concentrate_id" class="form-control" required>
            <option value="">-- Choisir un concentré --</option>
            <?php foreach ($concentrates as $c): ?>
                <option value="<?= $c['id'] ?>">
                    <?= esc($c['name']) ?> (reste <?= (float)$c['quantity'] ?> ml)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- 2) Sélection de la base -->
    <div class="mb-3">
        <label for="base_id">Base (50PG/50VG, etc.) *</label>
        <select name="base_id" id="base_id" class="form-control" required>
            <option value="">-- Choisir une base --</option>
            <?php foreach ($bases as $b): ?>
                <option value="<?= $b['id'] ?>">
                    <?= esc($b['name']) ?> (reste <?= (float)$b['quantity'] ?> ml)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- 3) Sélection de la nicotine -->
    <div class="mb-3">
        <label for="nicotine_id">Nicotine (mg/ml) *</label>
        <select name="nicotine_id" id="nicotine_id" class="form-control" required>
            <option value="">-- Choisir une nicotine --</option>
            <?php foreach ($nicotines as $n): ?>
                <option value="<?= $n['id'] ?>">
                    <?= esc($n['name']) ?> (reste <?= (float)$n['quantity'] ?> ml)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- 4) Volume total souhaité -->
    <div class="mb-3">
        <label for="volume_total">Volume total (en ml)*</label>
        <input type="number"
               name="volume_total"
               id="volume_total"
               class="form-control"
               required
               min="1"
               value="<?= esc($defaultVolume) ?>">
    </div>

    <!-- 5) Taux de nicotine final (mg/ml) -->
    <div class="mb-3">
        <label for="nic_strength_desired">Taux de nicotine désiré (mg/ml)*</label>
        <input type="number"
               name="nic_strength_desired"
               id="nic_strength_desired"
               class="form-control"
               required
               min="0"
               value="<?= esc($defaultNicStrength) ?>">
    </div>

    <!-- 6) Sélection de la fiole (50 ml, 100 ml, 200 ml, ...) -->
    <div class="mb-3">
        <label for="bottle_id">Fiole vide *</label>
        <select name="bottle_id" id="bottle_id" class="form-control" required>
            <option value="">-- Choisir une contenance de fiole --</option>
            <?php foreach ($bottles as $fiole): ?>
                <option value="<?= $fiole['id'] ?>">
                    <?= esc($fiole['name']) ?> (reste <?= (float)$fiole['quantity'] ?> unités, <?= (int)$fiole['unit_volume_ml'] ?> ml chacune)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Valider la composition</button>
</form>
