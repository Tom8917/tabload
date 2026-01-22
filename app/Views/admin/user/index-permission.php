<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Liste des rôles</h4>

        <a href="<?= base_url('/admin/userpermission/new'); ?>"
           class="btn btn-sm btn-primary">
            <i class="fa-solid fa-plus me-1"></i>
            Nouveau rôle
        </a>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                <tr>
                    <th style="width:300px;">ID</th>
                    <th style="width:500px;">Nom</th>
                    <th>Slug</th>
                </tr>
                </thead>
                <tbody>

                <?php if (!empty($permissions)): ?>
                    <?php foreach ($permissions as $permission): ?>
                        <tr>
                            <td><?= esc($permission['id']) ?></td>
                            <td><?= esc($permission['name']) ?></td>
                            <td class="text-muted"><?= esc($permission['slug']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            Aucun rôle enregistré
                        </td>
                    </tr>
                <?php endif; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>
