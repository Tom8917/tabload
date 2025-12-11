<div class="container py-4">
    <?php if(session('error')): ?><div class="alert alert-danger"><?= esc(session('error')) ?></div><?php endif; ?>
    <h3 class="mb-4">Réalisations Professionnelles</h3>

    <?php if (empty($pages)): ?>
        <div class="text-muted">Aucune page publiée.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($pages as $p): ?>
                <div class="col-md-4">
                    <a href="<?= site_url('pages/show/'.$p['slug']) ?>" class="text-decoration-none text-dark">
                        <div class="card h-100 shadow-sm">
                            <?php if(!empty($p['image'])): ?>
                                <img src="<?= esc(base_url($p['image'])) ?>" class="card-img-top rounded-top-4" alt="<?= esc($p['title']) ?>">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= esc($p['title']) ?></h5>
                                <p class="card-text text-truncate"><?= esc($p['description']) ?></p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
