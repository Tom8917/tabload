<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Bilans</h1>
        <a href="<?= site_url('admin/reports/new') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nouveau bilan
        </a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= esc($success) ?></div>
    <?php endif; ?>

    <?php if (empty($reports)): ?>
        <div class="alert alert-info mb-0">
            Aucun bilan pour le moment. Cliquez sur <strong>Nouveau bilan</strong> pour en créer un.
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive mb-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Titre</th>
                            <th>Application</th>
                            <th>Version</th>
                            <th>Auteur</th>
                            <th>Statut</th>
                            <th style="width: 130px;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($reports as $report): ?>
                            <tr>
                                <td>#<?= esc($report['id']) ?></td>
                                <td><?= esc($report['title']) ?></td>
                                <td><?= esc($report['application_name']) ?></td>
                                <td><?= esc($report['version']) ?></td>
                                <td><?= esc($report['author_name']) ?></td>
                                <td>
                                    <?php
                                    $status = $report['status'] ?? 'brouillon';
                                    $badgeClass = 'bg-secondary';
                                    if ($status === 'en_relecture') {
                                        $badgeClass = 'bg-warning';
                                    } elseif ($status === 'final') {
                                        $badgeClass = 'bg-success';
                                    }
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= esc(ucfirst(str_replace('_', ' ', $status))) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= site_url('admin/reports/' . $report['id'] . '/sections') ?>"
                                       class="btn btn-sm btn-outline-primary">
                                        Rédiger
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>