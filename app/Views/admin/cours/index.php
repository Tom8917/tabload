<div class="container py-4">
    <?php if(session('message')): ?><div class="alert alert-success"><?= esc(session('message')) ?></div><?php endif; ?>
    <?php if(session('error')): ?><div class="alert alert-danger"><?= esc(session('error')) ?></div><?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-2">Cours</h3>
        <div>
            <a href="<?= site_url('admin/cours/create') ?>" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Nouveau cours</a>
        </div>
    </div>

    <?php if (empty($cours)): ?>
        <div class="text-muted">Aucun cours.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($cours as $c): ?>
                <div class="col-md-4">
                    <div class="card h-100">
                        <?php if(!empty($c['image'])): ?>
                            <img src="<?= esc(base_url($c['image'])) ?>" class="card-img-top rounded-top-4" alt="<?= esc($c['title']) ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= esc($c['title']) ?></h5>
                            <p class="card-text text-truncate"><?= esc($c['description']) ?></p>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex flex-wrap gap-2">
                                <a href="<?= site_url('cours/show/'.$c['slug']) ?>" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fa-solid fa-eye me-2"></i>Voir</a>
                                <a href="<?= site_url('admin/cours/edit/'.$c['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen me-2"></i>Modifier</a>
                                <a href="<?= site_url('admin/cours/delete/'.$c['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ce cours ?')"><i class="fa-solid fa-trash me-2"></i>Supprimer</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
