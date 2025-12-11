<div class="container py-4">
    <?php if(session('message')): ?><div class="alert alert-success"><?= esc(session('message')) ?></div><?php endif; ?>
    <?php if(session('error')): ?><div class="alert alert-danger"><?= esc(session('error')) ?></div><?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-2">Réalisations Professionnelles</h3>
        <div>
            <a href="<?= site_url('admin/pages/create') ?>" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Nouvelle page</a>
        </div>
    </div>

    <?php if (empty($pages)): ?>
        <div class="text-muted">Aucune page.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($pages as $p): ?>
                <div class="col-md-4">
                    <div class="card h-100">
                        <?php if(!empty($p['image'])): ?>
                            <img src="<?= esc(base_url($p['image'])) ?>" class="card-img-top rounded-top-4" alt="<?= esc($p['title']) ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= esc($p['title']) ?></h5>
                            <p class="card-text text-truncate"><?= esc($p['description']) ?></p>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex flex-wrap gap-2">
                                <a href="<?= site_url('pages/show/'.$p['slug']) ?>" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fa-solid fa-eye me-2"></i>Voir</a>
                                <a href="<?= site_url('admin/pages/edit/'.$p['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen me-2"></i>Modifier</a>
                                <a href="<?= site_url('admin/pages/delete/'.$p['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer cette page ?')"><i class="fa-solid fa-trash me-2"></i>Supprimer</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
