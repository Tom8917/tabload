<?php
$errors  = $errors ?? (session('errors') ?? []);
$success = $success ?? session('success');

// utilisateur connecté pour afficher le nom (non envoyé en POST)
$user = session()->get('user');
$author = trim((string)($user->firstname ?? '') . ' ' . (string)($user->lastname ?? ''));
if ($author === '') $author = 'Utilisateur';
?>

<div class="container-fluid">

    <?= view('front/reports/_steps', ['step' => 'config']) ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Configuration du bilan</h1>
        <a href="<?= site_url('report') ?>" class="btn btn-outline-secondary">
            Retour à la liste
        </a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= esc($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors) && is_array($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $err): ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="post" action="<?= site_url('report') ?>">
                <?= csrf_field() ?>

                <h5 class="mb-3">Informations générales</h5>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Titre du bilan <span class="text-danger">*</span></label>
                        <input type="text"
                               name="title"
                               class="form-control"
                               value="<?= old('title') ?>"
                               required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Application étudiée <span class="text-danger">*</span></label>
                        <input type="text"
                               name="application_name"
                               class="form-control"
                               value="<?= old('application_name') ?>"
                               required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Version du document</label>
                        <input type="text"
                               name="version"
                               class="form-control"
                               placeholder="ex : v1.0, 2025-01"
                               value="<?= old('version') ?>">
                    </div>

                    <div class="col-md-8 mb-3">
                        <label class="form-label">Auteur</label>
                        <input type="text"
                               class="form-control"
                               value="<?= esc($author) ?>"
                               disabled>
                        <div class="form-text">Renseigné automatiquement à la création.</div>
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="mb-3">Sections à inclure</h5>
                <div class="text-muted small mb-3">
                    Cochez les blocs de tests que vous souhaitez inclure. Le squelette sera créé automatiquement.
                </div>

                <div class="row">
                <!-- Test à la cible -->
                <div class="col-md-3">
                <div class="border rounded p-3 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="tplTarget"
                               name="tpl_target_enabled" value="1"
                            <?= old('tpl_target_enabled', '1') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="tplTarget">
                            Inclure le test à la cible
                        </label>
                    </div>
                </div>
                </div>

                <!-- Endurance -->
                <div class="col-md-3">
                <div class="border rounded p-3 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="tplEndurance"
                               name="tpl_endurance_enabled" value="1"
                            <?= old('tpl_endurance_enabled', '1') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="tplEndurance">
                            Inclure le test d’endurance
                        </label>
                    </div>
                </div>
                </div>

                <!-- Limites -->
                <div class="col-md-3">
                <div class="border rounded p-3 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="tplLimits"
                               name="tpl_limits_enabled" value="1"
                            <?= old('tpl_limits_enabled', '1') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="tplLimits">
                            Inclure le test aux limites
                        </label>
                    </div>
                </div>
                </div>

                <!-- Surcharge -->
                <div class="col-md-3">
                <div class="border rounded p-3 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="tplOverload"
                               name="tpl_overload_enabled" value="1"
                            <?= old('tpl_overload_enabled', '1') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="tplOverload">
                            Inclure le test de surcharge
                        </label>
                    </div>
                </div>
                </div>

                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        Créer le bilan et générer le squelette
                    </button>
                    <a href="<?= site_url('report') ?>" class="btn btn-link">
                        Annuler
                    </a>
                </div>

            </form>
        </div>
    </div>

</div>
