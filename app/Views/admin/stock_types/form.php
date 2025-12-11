<?php

/**
 * $type           : si on est en édition, tableau associatif du type existant
 * $stockRoles     : liste des rôles possibles
 * $existingRoleIds: IDs de rôles déjà liés à ce type (édition uniquement)
 */

$selectedRoles = $existingRoleIds ?? [];

?>

<form action="<?= isset($type) ? base_url('/admin/stocktype/update') : base_url('/admin/stocktype/create') ?>"
      method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <?php if (! empty($type['id'])): ?>
        <input type="hidden" name="id" value="<?= esc($type['id']) ?>">
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header">
            <h4>
                <?= isset($type) ? 'Modifier un type de produit' : 'Ajouter un type de produit' ?>
            </h4>
        </div>

        <div class="card-body">
            <div class="mb-3">
                <label for="name">Nom du type *</label>
                <input type="text"
                       name="name"
                       id="name"
                       class="form-control"
                       required
                       value="<?= esc($type['name'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label for="unit_volume_ml">Contenance par unité (ml) *</label>
                <input type="number"
                       step="0.01"
                       name="unit_volume_ml"
                       id="unit_volume_ml"
                       class="form-control"
                       min="1"
                       required
                       value="<?= esc($type['unit_volume_ml'] ?? '') ?>">
                <small class="text-muted">Ex. 1000 pour 1 L, 50 pour 50 ml, etc.</small>
            </div>

            <div class="mb-5">
                <label>Utilisations possibles (rôles)</label>
                <div class="mt-2">
                    <?php foreach ($stockRoles as $role): ?>
                        <div class="form-check form-check-inline">
                            <input type="checkbox"
                                   name="roles[]"
                                   id="role_<?= $role['id'] ?>"
                                   value="<?= $role['id'] ?>"
                                   class="form-check-input"
                                <?= in_array($role['id'], $selectedRoles) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="role_<?= $role['id'] ?>">
                                <?= esc($role['name']) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mb-3">
                <label for="image">Image (facultatif)</label>
                <input type="file" name="image" id="image" class="form-control">
                <?php if (!empty($type['image'])): ?>
                    <img src="<?= base_url('uploads/stock_types/' . esc($type['image'])) ?>"
                         alt="Aperçu"
                         class="mt-2"
                         style="max-height: 200px; object-fit: contain; border: none;">
                <?php endif; ?>
            </div>
        </div>

        <div class="card-footer text-end">
            <a href="<?= base_url('/admin/stocktype') ?>" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-success">Enregistrer</button>
        </div>
    </div>
</form>
