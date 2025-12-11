<div class="row">
    <div class="col">
        <form action="<?= isset($ticket) ? base_url("/ticket/update/" . $ticket['id']) : base_url("/ticket/create") ?>" method="post">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">
                        <?= isset($ticket) ? "Éditer le Ticket #" . $ticket['id'] : "Créer un Ticket" ?>
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (session()->has('error')): ?>
                        <div class="alert alert-danger"><?= session('error') ?></div>
                    <?php endif; ?>
                    <?php if (session()->has('success')): ?>
                        <div class="alert alert-success"><?= session('success') ?></div>
                    <?php endif; ?>

                    <input type="hidden" name="id_status" value="1">

                    <div class="mb-3">
                        <label for="id_ticketcategory" class="form-label">Catégorie</label>
                        <select class="form-select" id="id_ticketcategory" name="id_ticketcategory" required>
                            <option disabled <?= !isset($ticket) ? "selected" : ""; ?>>Sélectionner une catégorie
                            </option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id']; ?>" <?= (isset($ticket) && $category['id'] == $ticket['id_ticketcategory']) ? "selected" : ""; ?>>
                                    <?= $category['type']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">Objet du Ticket</label>
                        <input type="text" class="form-control" id="title" placeholder="Type de Ticket"
                               value="<?= isset($ticket) ? $ticket['title'] : ""; ?>" name="title" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea type="text" class="form-control" id="description" placeholder="Description"
                                  name="description"
                                  required><?= isset($ticket) ? $ticket['description'] : ""; ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="id_priority" class="form-label">Priorité</label>
                        <select class="form-select" id="id_priority" name="id_priority">
                            <option value="" <?= !isset($ticket) || empty($ticket['id_priority']) ? "selected" : ""; ?>>
                                Non renseignée
                            </option>
                            <?php foreach ($priorities as $priority): ?>
                                <option value="<?= $priority['id']; ?>" <?= (isset($ticket) && $priority['id'] == $ticket['id_priority']) ? "selected" : ""; ?>>
                                    <?= $priority['type']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="card-footer text-end">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        Annuler
                    </button>
                    <?php if (isset($ticket)): ?>
                        <input type="hidden" name="id" value="<?= $ticket['id']; ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <?= isset($ticket) ? "Sauvegarder" : "Enregistrer" ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
