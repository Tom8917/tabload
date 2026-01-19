<div class="container py-4">
    <?php if(session('message')): ?>
        <div class="alert alert-success"><?= esc(session('message')) ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?= esc(session('error')) ?></div>
    <?php endif; ?>

    <h3 class="text-center mb-5">Profil de <?= esc($user['name'] ?: 'Utilisateur #'.$user['id']) ?></h3>

    <div class="row g-4 align-items-center">
        <div class="col-md-3">
            <img
                    src="<?= esc(base_url($user['avatar'])) ?>"
                    alt="Avatar"
                    class="img-fluid rounded shadow-sm"
                    style="max-width:220px;">
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">ID</dt>
                        <dd class="col-sm-9"><?= esc($user['id'] ?? '') ?></dd>

                        <dt class="col-sm-3">Nom</dt>
                        <dd class="col-sm-9"><?= esc($user['firstname'] . ' ' . $user['lastname'] ?? '') ?></dd>

                        <dt class="col-sm-3">Email</dt>
                        <dd class="col-sm-9"><?= esc($user['email'] ?? '') ?></dd>

                        <dt class="col-sm-3">Permission</dt>
                        <dd class="col-sm-9">
                            <?php
                            $perm = (int)($user['id_permission'] ?? 0);
                            echo match ($perm) {
                                1 => 'Administrateur',
                                2 => 'Manager',
                                3 => 'Utilisateur',
                                default => 'Inconnu ('.$perm.')',
                            };
                            ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
