<div class="row">
    <!-- Formulaire à gauche -->
    <div class="col-md-3 mb-4">
        <form action="<?= isset($task) ? base_url("/admin/task/update") : base_url("/admin/task/create") ?>"
              method="POST">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">
                        <?= isset($task) ? "Editer " . $task['title'] : "Créer une Tâche" ?>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <div class="mb-3">
                            <label for="title" class="form-label">Titre</label>
                            <input type="text" class="form-control" id="title" placeholder="Titre"
                                   value="<?= isset($task) ? $task['title'] : ""; ?>" name="title">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" placeholder="Description"
                                      name="description"><?= isset($task) ? $task['description'] : ""; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="limit_time" class="form-label">Date limite</label>
                            <input type="date" class="form-control" id="limit_time" name="limit_time"
                                   value="<?= isset($task) && !empty($task['limit_time']) ? date('Y-m-d', strtotime($task['limit_time'])) : ''; ?>">
                        </div>

                        <?php if (isset($task)): ?>
                            <div class="mt-4 p-3 border rounded bg-light">
                                <label for="status" class="form-label fw-bold fs-5">Statut de la tâche</label>
                                <div class="form-check mt-2">
                                    <input type="hidden" name="status" value="À faire">
                                    <input type="checkbox"
                                           class="form-check-input mark-done"
                                           name="status"
                                           value="Fait"
                                           id="taskStatus"
                                           data-task-id="<?= isset($task) ? $task['id'] : ''; ?>"
                                        <?= isset($task) && $task['status'] === 'Fait' ? 'checked' : ''; ?> />
                                    <label class="form-check-label ms-2 fs-6" for="taskStatus">
                                        Marquer comme <strong>Fait</strong>
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <?php if (isset($task)): ?>
                        <input type="hidden" name="id" value="<?= $task['id']; ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <?= isset($task) ? "Sauvegarder" : "Ajouter" ?>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Liste des tâches à droite -->
    <div class="col-md-9 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Liste des Tâches</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tableTasks" class="table table-hover">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Description</th>
                            <th>Échéance</th>
                            <th>Status</th>
                            <th class="text-center">Voir</th>
                            <th class="text-center">Modifier</th>
                            <th class="text-center">Supprimer</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal pour afficher les informations tâche -->
<div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-center">
                <!-- Titre et ID centrés -->
                <div class="w-100">
                    <h5 class="modal-title" id="taskModalLabel">
                        <strong><span id="taskTitle"></span></strong>
                    </h5>
                    <strong>ID :</strong> <span id="taskId"></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Description :</strong> <span id="taskDescription"></span></p>
                <p><strong>Échéance :</strong> <span id="taskLimitTime"></span></p>
                <p class="mb-2"><strong>Status :</strong> <span id="taskStatus"></span></p>
            </div>
        </div>
    </div>
</div>

<!-- Script -->
<script>
    $(document).ready(function () {
        var baseUrl = "<?= base_url(); ?>";

        function nl2br(str) {
            return str.replace(/(?:\r\n|\r|\n)/g, '<br>');
        }

        var dataTable = $('#tableTasks').DataTable({
            "responsive": true,
            "processing": true,
            "serverSide": true,
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, 100, 1000], [10, 25, 50, 100, "Tous"]],
            "language": {
                url: baseUrl + 'js/datatable/datatable-2.1.4-fr-FR.json',
            },
            "ajax": {
                "url": baseUrl + "admin/task/SearchTask",
                "type": "POST",
                "error": function (xhr, error, thrown) {
                    console.error("Erreur AJAX : ", xhr.responseText);
                }
            },
            "columns": [
                {"data": "id"},
                {"data": "title"},
                {
                    "data": "description",
                    "render": function (data) {
                        return `<div style="max-width: 250px; white-space: pre-wrap; word-wrap: break-word;">${data ? nl2br(data) : 'Aucune description'}</div>`;
                    }
                },
                {
                    "data": "limit_time",
                    "render": function (data, type, row) {
                        let badgeHtml = '';
                        let formattedDate = '';

                        // Affichage de la date sans l'heure
                        if (data) {
                            const limitDate = new Date(data);
                            formattedDate = limitDate.toLocaleDateString('fr-FR'); // Format "JJ/MM/AAAA"
                        } else {
                            formattedDate = 'Pas de limite';
                        }

                        if (data && row.status !== 'Fait') {
                            const today = new Date();
                            const timeDiff = new Date(data) - today;
                            const daysRemaining = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));

                            let badgeText = '';
                            let badgeClass = 'bg-success'; // Vert par défaut

                            if (daysRemaining > 0) {
                                badgeText = `Il reste ${daysRemaining} jour${daysRemaining > 1 ? "s" : ""}`;
                                if (daysRemaining <= 2) {
                                    badgeClass = 'bg-warning'; // Orange si dans 2 jours
                                }
                            } else if (daysRemaining <= 0) {
                                badgeClass = 'bg-danger'; // Rouge si dépassé
                                badgeText = 'Échéance dépassée';
                            }

                            badgeHtml = `<span class="badge ${badgeClass} ms-2">${badgeText}</span>`;
                        }

                        return `${formattedDate} ${badgeHtml}`;
                    }
                },
                {
                    "data": "status",
                    "render": function (data) {
                        let labelClass = data === 'Fait' ? 'status-done' : 'status-todo';
                        return `<span class="status-label ${labelClass}">${data}</span>`;
                    }
                },
                {
                    data: null,
                    sortable: false,
                    render: function (data) {
                        return `<div class="text-center"><a class="btn btn-sm task-info"
                                        data-id="${data.id}"
                                        data-title="${data.title}"
                                        data-description="${data.description}"
                                        data-limit_time="${data.limit_time}"
                                        data-status="${data.status}"
                                        data-bs-toggle="modal" data-bs-target="#taskModal">
                                    <i class="fa-solid fa-eye"></i>
                                </a></div>`;
                    }
                },
                {
                    data: 'id',
                    sortable: false,
                    render: function (data) {
                        return `<div class="text-center"><a href="${baseUrl}admin/task/${data}"><i class="fa-solid fa-pencil"></i></a></div>`;
                    }
                },
                {
                    data: 'id',
                    sortable: false,
                    render: function (data) {
                        return `<div class="text-center"><a href='${baseUrl}admin/task/delete/${data}' class="text-danger"><i class="fa-solid fa-trash"></i></a></div>`;
                    }
                },
            ]
        });

        // Gestion du clic sur le bouton "eye" pour afficher la modal
        $('#tableTasks tbody').on('click', '.task-info', function () {
            var limitTime = $(this).data('limit_time');
            var formattedLimitTime = '';
            if (limitTime) {
                const date = new Date(limitTime);
                formattedLimitTime = date.toLocaleDateString('fr-FR'); // Format "JJ/MM/AAAA"
            } else {
                formattedLimitTime = 'Pas de limite';
            }

            $('#taskId').text($(this).data('id'));
            $('#taskTitle').text($(this).data('title'));
            $('#taskDescription').text($(this).data('description'));
            $('#taskLimitTime').text(formattedLimitTime);
            $('#taskStatus').text($(this).data('status'));
        });
    });

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

        updateBadges();
        setInterval(updateBadges, 5 * 60 * 1000); // Mettre à jour les badges toutes les 5 minutes
    });
</script>

<style>
    .card {
        border: 1px solid #ddd;
        box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
    }

    .card-body {
        overflow-x: auto;
    }

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

    /*table td {*/
    /*    vertical-align: middle;*/
    /*}*/

    #tableTasks {
        min-width: 800px;
    }

    #tableTasks th, #tableTasks td {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
        max-width: 150px;
    }

    @media (max-width: 768px) {
        #tableTasks th, #tableTasks td {
            font-size: 12px;
            max-width: 100px;
        }
    }
</style>