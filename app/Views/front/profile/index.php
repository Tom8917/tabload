<?php
$user = $user ?? [];
$userEntity = $userEntity ?? null;

$name = (string)($user['name'] ?? ('Utilisateur #'.(int)($user['id'] ?? 0)));

$fullName = trim((string)($user['firstname'] ?? '') . ' ' . (string)($user['lastname'] ?? ''));
if ($fullName === '') $fullName = $name;
$firstname = trim((string)($user['firstname'] ?? ''));
$lastname = trim((string)($user['lastname'] ?? ''));

$perm = (int)($user['id_permission'] ?? 0);
$permLabel = match ($perm) {
    1 => 'Administrateur',
    2 => 'Manager',
    3 => 'Utilisateur',
    default => 'Inconnu (' . $perm . ')',
};

$avatarPath = '/assets/img/avatars/unknow.png';
if (is_object($userEntity) && method_exists($userEntity, 'getProfileImage')) {
    $avatarPath = (string)$userEntity->getProfileImage();
}
$avatarUrl = base_url(ltrim($avatarPath, '/'));
?>

<div class="container py-4">

    <?php if (session('message')): ?>
        <div class="alert alert-success"><?= esc(session('message')) ?></div>
    <?php endif; ?>

    <?php if (session('error')): ?>
        <div class="alert alert-danger"><?= esc(session('error')) ?></div>
    <?php endif; ?>

    <div class="col-md-12 mb-3 mt-5 text-center text-md-center">
        <img
                src="<?= esc($avatarUrl) ?>"
                alt="Avatar"
                class="img-fluid rounded shadow-sm"
                style="max-width:220px;">
    </div>
    <h3 class="text-center mb-5"><?= esc($name) ?></h3>

    <div class="row g-4 align-items-center justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">ID</dt>
                        <dd class="col-sm-9"><?= esc((string)($user['id'] ?? '')) ?></dd>

                        <dt class="col-sm-3">Nom</dt>
                        <dd class="col-sm-9"><?= esc($lastname) ?></dd>

                        <dt class="col-sm-3">Prénom</dt>
                        <dd class="col-sm-9"><?= esc($firstname) ?></dd>

                        <dt class="col-sm-3">Email</dt>
                        <dd class="col-sm-9"><?= esc((string)($user['email'] ?? '')) ?></dd>

                        <dt class="col-sm-3">Permission</dt>
                        <dd class="col-sm-9"><?= esc($permLabel) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

</div>