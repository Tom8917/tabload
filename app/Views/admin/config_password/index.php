<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Configuration des mots de passe par page</h4>
        <a href="<?= base_url('admin/configpassword/new'); ?>" class="btn btn-primary"><i class="fa-solid fa-plus"></i></a>
    </div>
    <div class="card-body">
        <table class="table table-hover">
            <thead>
            <tr>
                <th>ID</th>
                <th>Page</th>
                <th>Password</th>
                <th>Label</th>
                <th>Modifier</th>
                <th>Supprimer</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($passwords)) : ?>
                <?php foreach ($passwords as $password) : ?>
                    <tr>
                        <td><?= esc($password['id']) ?></td>
                        <td><?= esc($password['page_slug']) ?></td>
                        <td><?= esc($password['password_hash']) ?></td>
                        <td><?= esc($password['label']) ?></td>
                        <td>
                            <a href="/admin/configpassword/<?= $password['id'] ?>">Modifier</a>
                        </td>
                        <td>
                            <a href="/admin/configpassword/delete/<?= $password['id'] ?>" onclick="return confirm('Supprimer ?')">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="2">Aucun mot de passe configuré.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
