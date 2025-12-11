<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Liste des
            <mark>Tâches</mark>
        </h4>
        <a href="<?= base_url('/admin/task/new'); ?>"><i class="fa-solid fa-plus"></i></a>
    </div>
    <div class="card-body">
        <table id="tableTasks" class="table table-hover">
            <thead>
            <tr>
                <th>Titre</th>
                <th>Description</th>
                <th>Échéance</th>
                <th>Status</th>
                <th class="text-center">Modifier</th>
                <th class="text-center">Supprimer</th>
            </tr>
            </thead>
            <tbody>
            <?php if (isset($tasks) && !empty($tasks)): ?>
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><?= $task['title']; ?></td>
                        <td><?= $task['description'] ? nl2br($task['description']) : 'Aucune description'; ?></td>
                        <td><?= isset($task['limit_time']) && $task['limit_time'] !== null ? date('d/m/Y', strtotime($task['limit_time'])) : 'Pas de limite'; ?>
                            <?php if (isset($task['limit_time']) && $task['limit_time'] !== null && $task['status'] !== 'Fait') : ?>
                                <span class="badge date-badge ms-2"
                                      data-limit="<?= $task['limit_time']; ?>">
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                <span class="status-label <?= $task['status'] === 'Fait' ? 'status-done' : 'status-todo'; ?>">
                    <?= $task['status']; ?>
                </span>
                        </td>
                        <td class="text-center">
                            <a href="<?= base_url('admin/task/' . $task['id']); ?>"><i
                                        class="fa-solid fa-pencil"></i></a>
                        </td>
                        <td class="text-center">
                            <a href="<?= base_url('admin/task/delete/' . $task['id']); ?>"
                               class="text-danger delete-btn"
                               data-id="<?= $task['id']; ?>">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Aucune tâche disponible ou en cours.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".delete-btn").forEach(function (button) {
            button.addEventListener("click", function (e) {
                e.preventDefault();
                var taskId = this.getAttribute("data-id");
                var deleteUrl = this.getAttribute("href");

                Swal.fire({
                    title: "Êtes-vous sûr ?",
                    text: "Cette action est irréversible !",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Oui, supprimer !",
                    cancelButtonText: "Annuler"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = deleteUrl;
                    }
                });
            });
        });

        function updateBadges() {
            const badges = document.querySelectorAll(".date-badge");

            badges.forEach(badge => {
                const limitDateStr = badge.getAttribute("data-limit");
                const limitDate = new Date(limitDateStr);
                const today = new Date();

                // Calcul du nombre de jours restants
                const timeDiff = limitDate - today;
                const daysRemaining = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));

                let badgeText = "";
                let badgeClass = "bg-success"; // Vert par défaut

                if (daysRemaining > 0) {
                    badgeText = `Il reste ${daysRemaining} jour${daysRemaining > 1 ? "s" : ""}`;

                    // Changement de couleur en fonction du temps restant
                    if (daysRemaining <= 2) {
                        badgeClass = "bg-warning"; // Orange si dans 2 jours
                    }
                    if (daysRemaining <= 0) {
                        badgeClass = "bg-danger"; // Rouge si dépassé
                        badgeText = "Échéance dépassée";
                    }
                } else {
                    badgeClass = "bg-danger";
                    badgeText = "Échéance dépassée";
                }

                badge.textContent = badgeText;
                badge.classList.add("badge", badgeClass);
            });
        }

        updateBadges(); // Appel initial
        setInterval(updateBadges, 5 * 60 * 1000);
    });
</script>

<style>
    .status-label {
        font-weight: bold;
        padding: 5px 10px;
        border-radius: 5px;
    }

    .status-done {
        color: #155724;
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
    }

    .status-todo {
        color: #721c24;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
    }

    table td {
        vertical-align: middle;
    }
</style>
