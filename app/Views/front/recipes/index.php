<div class="container py-4">
    <h1 class="mb-4">Nos Recettes</h1>

    <?php if (empty($recipes)): ?>
        <p class="text-muted">Aucune recette disponible.</p>
    <?php else: ?>
        <div class="row row-cols-md-4 g-4">
            <?php foreach ($recipes as $recipe): ?>
                <div class="col mb-5">
                    <div class="card h-100 shadow-sm" style="max-width: 250px; min-width: 150px">
                        <?php if (!empty($recipe['image'])): ?>
                            <img src="<?= base_url('uploads/recipes/' . $recipe['image']) ?>"
                                 class="card-img-top mb-1 mt-2"
                                 style="max-height: 200px; object-fit: contain;"
                                 alt="<?= esc($recipe['name']) ?>">
                        <?php else: ?>
                            <div class="card-img-top text-muted text-center"
                                 style="height: 200px; display: flex; align-items: center; justify-content: center;">
                                <span>Pas d’image</span>
                            </div>
                        <?php endif; ?>

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= esc($recipe['name']) ?></h5>
                            <p class="card-text"><?= esc($recipe['description']) ?></p>

                            <?php if (!empty($recipe['decoded_roles'])): ?>
                                <div class="mb-2">
                                    <?php foreach ($recipe['decoded_roles'] as $role => $itemName): ?>
                                        <span class="badge bg-secondary me-1 mb-1">
                                            <?= ucfirst($role) ?>: <?= esc($itemName) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="mt-auto d-flex justify-content-between align-items-end">
                                <span class="fw-bold">
                                    <?= number_format($recipe['price'], 2, ',', ' ') ?> €
                                </span>

                                <a href="<?= site_url('recipe/show/' . $recipe['id']) ?>"
                                   class="btn btn-outline-primary btn-sm">Voir</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
