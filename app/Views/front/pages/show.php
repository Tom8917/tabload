<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><title><?= esc($page['title']) ?></title></head>
<body>
<div class="container py-4">
    <header class="text-center mb-3">
        <h1><?= esc($page['title']) ?></h1>
<!--        --><?php //if (!empty($page['image'])): ?>
<!--            <img src="--><?php //= esc(base_url($page['image'])) ?><!--" alt="" style="max-width:640px;height:auto;">-->
<!--        --><?php //endif; ?>
        <hr class="mt-5 mb-4">
    </header>

    <?php if (!empty($page['description'])): ?>
        <p class="lead"><?= esc($page['description']) ?></p>
    <hr class="mb-4 mt-4">
    <?php else: ?>
    <?php endif; ?>

    <?php if (!empty($page['content'])): ?>
        <article class="mt-3">
            <?= $page['content'] ?>
        </article>
    <?php endif; ?>

    <?php if ($user->id_permission == 3): ?>
        <footer class="mt-5">
            <a href="<?= site_url('pages') ?>">⬅ Retour à la liste</a>
        </footer>
    <?php else: ?>
        <footer class="mt-5">
            <a href="javascript:window.close();" class="btn btn-outline-secondary">Fermer la page</a>
        </footer>
    <?php endif; ?>
</div>
</body>
</html>
