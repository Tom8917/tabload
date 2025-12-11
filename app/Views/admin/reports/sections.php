<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                Bilan : <?= esc($report['title']) ?>
            </h1>
            <div class="text-muted small">
                Application : <?= esc($report['application_name']) ?>
                <?php if (!empty($report['version'])): ?>
                    &nbsp;·&nbsp; Version : <?= esc($report['version']) ?>
                <?php endif; ?>
                <?php if (!empty($report['author_name'])): ?>
                    &nbsp;·&nbsp; Auteur : <?= esc($report['author_name']) ?>
                <?php endif; ?>
            </div>
        </div>

        <a href="<?= site_url('admin/reports') ?>" class="btn btn-outline-secondary">
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

    <!-- Ajouter une PARTIE (niveau 1) -->
    <div class="card mb-4">
        <div class="card-header">
            Ajouter une partie (niveau 1)
        </div>
        <div class="card-body">
            <form method="post" action="<?= site_url('admin/reports/' . $report['id'] . '/sections/root') ?>">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label">Titre de la partie <span class="text-danger">*</span></label>
                    <input type="text"
                           name="title"
                           class="form-control <?= isset($errors['title_root']) ? 'is-invalid' : '' ?>"
                           value="<?= old('title') ?>">
                    <?php if (isset($errors['title_root'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['title_root']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contenu (optionnel)</label>
                    <textarea name="content" rows="3" class="form-control"><?= old('content') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    Ajouter la partie
                </button>
            </form>
        </div>
    </div>

    <!-- Plan du bilan -->
    <div class="card">
        <div class="card-header">
            Plan du bilan
        </div>
        <div class="card-body">
            <?php if (empty($sections)): ?>
                <p class="text-muted mb-0">
                    Aucune section pour l’instant. Commencez par ajouter une partie ci-dessus.
                </p>
            <?php else: ?>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($sections as $section): ?>
                        <?php
                        // Indentation visuelle selon le niveau
                        $level     = (int) ($section['level'] ?? 1);
                        $marginCls = 'ms-0';
                        if ($level === 2) {
                            $marginCls = 'ms-3';
                        } elseif ($level === 3) {
                            $marginCls = 'ms-5';
                        } elseif ($level >= 4) {
                            $marginCls = 'ms-5'; // tu pourras affiner si tu veux
                        }
                        ?>
                        <li class="mb-3 <?= $marginCls ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong><?= esc($section['code']) ?></strong>
                                    &nbsp; <?= esc($section['title']) ?>
                                </div>
                                <div class="ms-2 d-flex gap-2">
                                    <a href="<?= site_url('admin/reports/' . $report['id'] . '/sections/' . $section['id'] . '/edit') ?>"
                                       class="btn btn-sm btn-outline-secondary">
                                        Modifier
                                    </a>

                                    <form method="post"
                                          action="<?= site_url('admin/reports/' . $report['id'] . '/sections/' . $section['id'] . '/delete') ?>"
                                          onsubmit="return confirm('Supprimer cette section et toutes ses sous-sections ?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Supprimer
                                        </button>
                                    </form>

                                    <button class="btn btn-sm btn-outline-primary"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#childForm<?= $section['id'] ?>">
                                        + Sous-partie
                                    </button>
                                </div>
                            </div>

                            <?php if (!empty($section['content'])): ?>
                                <div class="mt-1 small text-muted">
                                    <?= esc(mb_strimwidth(strip_tags($section['content']), 0, 200, '…', 'UTF-8')) ?>
                                </div>
                            <?php endif; ?>

                            <!-- Formulaire sous-partie -->
                            <div class="collapse mt-2" id="childForm<?= $section['id'] ?>">
                                <div class="card card-body">
                                    <form method="post"
                                          action="<?= site_url('admin/reports/' . $report['id'] . '/sections/' . $section['id'] . '/child') ?>">
                                        <?= csrf_field() ?>

                                        <div class="mb-2">
                                            <label class="form-label">Titre de la sous-partie <span class="text-danger">*</span></label>
                                            <input type="text"
                                                   name="title"
                                                   class="form-control <?= isset($errors['title_child_' . $section['id']]) ? 'is-invalid' : '' ?>"
                                                   value="<?= old('title_child_' . $section['id']) ?>">
                                            <?php if (isset($errors['title_child_' . $section['id']])): ?>
                                                <div class="invalid-feedback">
                                                    <?= esc($errors['title_child_' . $section['id']]) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label">Contenu</label>
                                            <textarea name="content" rows="3" class="form-control"></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-sm btn-primary">
                                            Ajouter la sous-partie
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

</div>