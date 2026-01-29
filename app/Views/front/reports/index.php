<?php
$myReports = $myReports ?? [];
$otherReports = $otherReports ?? [];
$success = $success ?? session('success');
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
                            <th>Version de l'application</th>
                            <th>Version du document</th>
                            <th>Statut</th>
                            <th style="width:300px;">Actions</th>
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
                                <td><?= esc($report['application_version']) ?></td>
                                <td><?= esc($report['doc_version']) ?></td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= esc(ucfirst(str_replace('_', ' ', $status))) ?>
                                    </span>
                                </td>
                                <td class="d-flex flex-wrap gap-2">
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="<?= site_url('report/' . $report['id']) ?>">
                                        <i class="fa-solid fa-eye"></i> Aperçu
                                    </a>
                                    <a class="btn btn-sm btn-outline-secondary"
                                       href="<?= site_url('report/' . $report['id'] . '/sections') ?>">
                                        <i class="fa-solid fa-pen"></i> Rédiger
                                    </a>
                                    <form method="post"
                                          class="js-delete-report"
                                          data-title="<?= esc($report['title']) ?>"
                                          action="<?= site_url('report/' . $report['id'] . '/delete') ?>">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-trash"></i> Supprimer
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

    <script>
        (function () {
            const forms = document.querySelectorAll('form.js-delete-report');
            if (!forms.length) return;

            forms.forEach((form) => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    const title = (form.dataset.title || '').trim();
                    const label = title ? `« ${title} »` : 'ce bilan';

                    Swal.fire({
                        title: 'Confirmer la suppression ?',
                        html: `Tu es sur le point de supprimer ${label}.<br>Cette action est <span style="text-decoration: underline" class="fw-bold">irréversible</span> !`,
                        icon: 'warning',
                        iconColor: '#dc3545',
                        showCancelButton: true,
                        cancelButtonText: 'Annuler',
                        confirmButtonText: 'Supprimer',
                        reverseButtons: false,
                        buttonsStyling: false,
                        customClass: {
                            cancelButton: 'btn btn-outline-secondary',
                            confirmButton: 'btn btn-danger me-2'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        })();
    </script>

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
                        <thead class="table">
                        <tr>
                            <th style="width:60px;">ID</th>
                            <th>Titre</th>
                            <th>Application</th>
                            <th>Version de l'application</th>
                            <th>Version du document</th>
                            <th>Statut</th>
                            <th>Auteur</th>
                            <th style="width:120px;">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($otherReports as $report): ?>
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
                                <td><?= esc($report['application_version']) ?></td>
                                <td><?= esc($report['doc_version']) ?></td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= esc(ucfirst(str_replace('_', ' ', $status))) ?>
                                    </span>
                                </td>
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


    <hr class="mt-5 mb-5">


    <!-- Bilans des autres (card) -->
    <div class="d-flex justify-content-end align-items-center mb-3">
        <strong>Bilans des autres en card :</strong>
        <span class="text-muted small ms-1"><?= count($otherReports) ?> élément(s)</span>
    </div>
    <?php if (empty($otherReports)): ?>
        <div class="p-2 text-center text-muted">Aucun autre bilan pour le moment.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($otherReports as $report): ?>
                <div class="col-3">
                    <div class="card rounded-4">
                        <div class="card-header d-flex justify-content-between align-items-center p-2">
                            <strong><?= esc($report['title']) ?></strong> Dans la base : #<?= esc($report['id']) ?><br>
                        </div>
                        <div class="card-body p-2">
                            <div class="text-center">
                                <strong><h4><?= esc($report['application_name']) ?></h4></strong><br>
                            </div>
                            <div class="mb-2">
                                <strong>Version de l'application : </strong><?= esc($report['application_version']) ?><br>
                            </div>
                            <div class="mb-2">
                                <strong>Rédigé par : </strong><?= esc($report['author_name']) ?><br>
                            </div>
                            <div class="mb-2">
                                <strong>Statut de rédaction : </strong><?= esc($report['status']) ?><br>
                            </div>
                            <div class="mb-2">
                                <strong>Statut du document : </strong><?= esc($report['doc_status']) ?><br>
                            </div>
                            <div class="mb-2">
                                <strong>Version du document : </strong><?= esc($report['doc_version']) ?><br>
                            </div>
                            <div class="mb-2">
                                <strong>Dernière correction : </strong><?= esc($report['updated_at']) ?><br>
                            </div>
                            <span class="d-flex justify-content-end">
                            <a class="btn btn-sm btn-outline-primary mt-2 mb-1"
                               href="<?= site_url('report/' . $report['id']) ?>">
                                Consulter
                            </a>
                        </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif ?>
</div>
