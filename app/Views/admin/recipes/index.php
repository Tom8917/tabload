<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center mb-4">
        <h2>Recettes</h2>
        <a href="<?= base_url('/admin/recipe/new') ?>" class="btn btn-primary">
            <i class="fa fa-plus"></i> Nouvelle recette
        </a>
    </div>

    <div class="card-body">
        <?php if (empty($recipes)): ?>
            <p class="text-muted">Aucune recette disponible.</p>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-6 g-4">
                <?php foreach ($recipes as $recipe): ?>
                    <?php $dosages = json_decode($recipe['dosages'] ?? '{}', true); ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm" style="max-width: 100%;">
                            <?php if (!empty($recipe['image'])): ?>
                                <img src="<?= base_url('uploads/recipes/' . $recipe['image']) ?>"
                                     class="card-img-top"
                                     style="max-height: 180px; object-fit: contain;"
                                     alt="<?= esc($recipe['name']) ?>">
                            <?php else: ?>
                                <div class="card-img-top text-muted text-center"
                                     style="height: 180px; display: flex; align-items: center; justify-content: center;">
                                    <span>Pas d’image</span>
                                </div>
                            <?php endif; ?>

                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= esc($recipe['name']) ?></h5>
                                <p class="mb-1"><strong>Volume :</strong> <?= esc($recipe['volume_ml']) ?> ml</p>
                                <p class="mb-1"><strong>Coût :</strong> <?= number_format($recipe['cost'], 2, ',', ' ') ?> €</p>
                                <p class="mb-2"><strong>Prix :</strong> <?= number_format($recipe['price'], 2, ',', ' ') ?> €</p>

                                <?php if (!empty($dosages)): ?>
                                    <div class="mb-2 small text-muted">
                                        <strong>Composition :</strong><br>
                                        Base : <?= round($dosages['base'] ?? 0, 4) ?> ml<br>
                                        Arôme : <?= round($dosages['concentrate'] ?? 0, 4) ?> ml<br>
                                        Nicotine : <?= round($dosages['nicotine'] ?? 0, 4) ?> ml
                                    </div>
                                <?php endif; ?>

                                <div class="mt-auto d-flex justify-content-around">
                                    <a href="<?= base_url('/admin/recipe/edit/' . $recipe['id']) ?>"
                                       class="btn btn-primary btn-sm"><i class="fa-solid fa-pen"></i></a>
                                    <a href="<?= base_url('/admin/recipe/delete/' . $recipe['id']) ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Supprimer cette recette ?')"><i class="fa-solid fa-trash"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
