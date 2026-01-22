<?php
$myReports    = $myReports ?? [];
$otherReports = $otherReports ?? [];
$success      = $success ?? session('success');
?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Bilans</h1>
        <a href="<?= site_url('report/new') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nouveau bilan
        </a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= esc($success) ?></div>
    <?php endif; ?>

    <?php if (empty($myReports) && empty($otherReports)): ?>
        <div class="alert alert-info mb-4">
            Aucun bilan pour le moment. Cliquez sur <strong>Nouveau bilan</strong> pour en créer un.
        </div>
    <?php endif; ?>

    <!-- Mes bilans -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Mes bilans</strong>
            <span class="text-muted small"><?= count($myReports) ?> élément(s)</span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($myReports)): ?>
                <div class="p-3 text-muted">Vous n’avez pas encore créé de bilan.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table">
                        <tr>
                            <th style="width:60px;">ID</th>
                            <th>Titre</th>
                            <th>Application</th>
                            <th>Version</th>
                            <th>Statut</th>
                            <th style="width:260px;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($myReports as $report): ?>
                            <?php
                            $status = $report['status'] ?? 'brouillon';
                            $badgeClass = 'bg-secondary';
                            if ($status === 'en_relecture') $badgeClass = 'bg-warning';
                            if ($status === 'final') $badgeClass = 'bg-success';
                            ?>
                            <tr>
                                <td>#<?= esc($report['id']) ?></td>
                                <td><?= esc($report['title']) ?></td>
                                <td><?= esc($report['application_name']) ?></td>
                                <td><?= esc($report['version']) ?></td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= esc(ucfirst(str_replace('_', ' ', $status))) ?>
                                    </span>
                                </td>
                                <td class="d-flex flex-wrap gap-2">
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="<?= site_url('report/' . $report['id']) ?>">
                                        Aperçu
                                    </a>
                                    <a class="btn btn-sm btn-outline-secondary"
                                       href="<?= site_url('report/' . $report['id'] . '/sections') ?>">
                                        Rédiger
                                    </a>
                                    <form method="post"
                                          action="<?= site_url('report/' . $report['id'] . '/delete') ?>"
                                          onsubmit="return confirm('Supprimer ce bilan ?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bilans des autres -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Bilans des autres</strong>
            <span class="text-muted small"><?= count($otherReports) ?> élément(s)</span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($otherReports)): ?>
                <div class="p-3 text-muted">Aucun autre bilan pour le moment.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th style="width:60px;">ID</th>
                            <th>Titre</th>
                            <th>Application</th>
                            <th>Version</th>
                            <th>Auteur</th>
                            <th style="width:120px;">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($otherReports as $report): ?>
                            <tr>
                                <td>#<?= esc($report['id']) ?></td>
                                <td><?= esc($report['title']) ?></td>
                                <td><?= esc($report['application_name']) ?></td>
                                <td><?= esc($report['version']) ?></td>
                                <td><?= esc($report['author_name']) ?></td>
                                <td>
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="<?= site_url('report/' . $report['id']) ?>">
                                        Consulter
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>
