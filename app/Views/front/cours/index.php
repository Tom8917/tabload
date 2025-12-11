<div class="container py-4">
    <?php if(session('error')): ?><div class="alert alert-danger"><?= esc(session('error')) ?></div><?php endif; ?>
    <h3 class="mb-4">Cours</h3>

    <?php if (empty($list)): ?>
        <div class="text-muted">Aucun cours publié.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($list as $c): ?>
                <div class="col-md-4">
                    <a href="<?= site_url('cours/show/'.$c['slug']) ?>" class="text-decoration-none text-dark">
                        <div class="card h-100 shadow-sm">
                            <?php if(!empty($c['image'])): ?>
                                <img src="<?= esc(base_url($c['image'])) ?>" class="card-img-top rounded-top-4" alt="<?= esc($c['title']) ?>">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= esc($c['title']) ?></h5>
                                <p class="card-text text-truncate"><?= esc($c['description']) ?></p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
