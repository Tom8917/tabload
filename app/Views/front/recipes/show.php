<div class="container py-4">
    <div class="row">
        <!-- Image -->
        <div class="col-md-6">
            <?php if (!empty($recipe['image'])): ?>
                <img src="<?= base_url('uploads/recipes/' . $recipe['image']) ?>"
                     class="img-fluid rounded" alt="<?= esc($recipe['name']) ?>">
            <?php else: ?>
                <div class="border rounded bg-light text-muted d-flex justify-content-center align-items-center" style="height:300px;">
                    Pas d’image
                </div>
            <?php endif; ?>
        </div>

        <!-- Description et achat -->
        <div class="col-md-6">
            <h1><?= esc($recipe['name']) ?></h1>
            <p><?= nl2br(esc($recipe['description'], 'html')) ?></p>

            <h5>Ingrédients :</h5>
            <ul class="list-group mb-3">
                <?php if (!empty($ingredients)): ?>
                    <?php foreach ($ingredients as $ing): ?>
                        <li class="list-group-item">
                            <strong><?= ucfirst($ing['role']) ?> :</strong> <?= esc($ing['item']['name']) ?>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="list-group-item text-muted">Aucun ingrédient listé.</li>
                <?php endif; ?>
            </ul>

            <!-- Prix -->
            <p class="fs-5 fw-bold">Prix : <?= number_format($recipe['price'], 2, ',', ' ') ?> €</p>

            <!-- Ajouter au panier -->
            <form action="<?= base_url('cart/add') ?>" method="post" class="w-100 w-md-50">
                <?= csrf_field() ?>
                <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">

                <div class="mb-3">
                    <label for="qty" class="form-label">Quantité :</label>
                    <input type="number" name="qty" id="qty" class="form-control" min="1" value="1" required>
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fa fa-shopping-cart me-1"></i> Ajouter au panier
                </button>
            </form>
        </div>
    </div>
</div>
