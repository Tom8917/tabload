<div class="row">
    <div class="col">
        <form action="<?= isset($task) ? base_url("/admin/task/update") : base_url("/admin/task/create") ?>" method="POST">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">
                        <?= isset($task) ? "Editer " . $task['title'] : "Créer une Tâche" ?>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Titre</label>
                        <input type="text" class="form-control" id="title" placeholder="Titre" value="<?= isset($task) ? $task['title'] : ""; ?>" name="title">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" placeholder="Description" name="description"><?= isset($task) ? $task['description'] : ""; ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="limit_time" class="form-label">Date limite</label>
                        <input type="date" class="form-control" id="limit_time" name="limit_time"
                               value="<?= isset($task) && !empty($task['limit_time']) ? date('Y-m-d', strtotime($task['limit_time'])) : ''; ?>">
                    </div>
                    <?php if (isset($task)): ?>
                        <div class="mt-4 p-3 border rounded">
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
                <div class="card-footer text-end">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        Annuler
                    </button>
                    <?php if (isset($task)): ?>
                        <input type="hidden" name="id" value="<?= $task['id']; ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <?= isset($task) ? "Sauvegarder" : "Enregistrer" ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
