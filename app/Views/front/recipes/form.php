<div class="container py-4">
    <h1>Créer ma propre recette</h1>

    <?php if (!empty(session()->getFlashdata('errors'))): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach (session()->getFlashdata('errors') as $err): ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= site_url('recipe/create') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="name" class="form-label">Nom de la recette :</label>
            <input
                    type="text"
                    name="name"
                    id="name"
                    class="form-control <?= isset(session()->getFlashdata('errors')['name']) ? 'is-invalid' : '' ?>"
                    value="<?= esc(old('name', session()->getFlashdata('oldInput')['name'] ?? '')) ?>"
            >
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description :</label>
            <textarea
                    name="description"
                    id="description"
                    rows="5"
                    class="form-control <?= isset(session()->getFlashdata('errors')['description']) ? 'is-invalid' : '' ?>"
            ><?= esc(old('description', session()->getFlashdata('oldInput')['description'] ?? '')) ?></textarea>
        </div>

        <div class="mb-3">
            <label for="image" class="form-label">Image (optionnel) :</label>
            <input type="file" name="image" id="image" class="form-control">
        </div>

        <hr>
        <h4>Ingrédients par rôle</h4>

        <ul>
            <?php foreach ($ingredients as $role => $item): ?>
                <li>
                    <?= ucfirst($role) ?> :
                    <?= esc($item['name']) ?>
                    <?php if ($role !== 'fiole'): ?>
                        (<?= esc($recipe['dosages'][$role] ?? 0) ?> ml)
                    <?php else: ?>
                        (1 fiole)
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <p>Coût réel de la recette : <b><?= number_format($recipe['cost'],2,',',' ') ?> €</b></p>
        <p>Prix de vente estimé : <b><?= number_format($recipe['price'],2,',',' ') ?> €</b></p>


        <button type="submit" class="btn btn-primary">Enregistrer la recette</button>
    </form>
</div>
