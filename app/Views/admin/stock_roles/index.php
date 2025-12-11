<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Liste des rôles</h4>
        <a href="<?= base_url('/admin/stockrole/new') ?>" class="btn btn-primary">
            <i class="fa fa-plus"></i> Ajouter
        </a>
    </div>
    <div class="card-body">
        <?php if (!empty($roles)): ?>
            <ul class="list-group">
                <?php foreach ($roles as $role): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= esc($role['name']) ?>
                        <span>
                        <a href="<?= base_url('/admin/stocktype/edit/' . $role['id']) ?>" class="btn btn-sm me-5"><i
                                    class="fa-solid fa-pencil"></i></a>
                        <a href="#"
                           data-id="<?= $role['id'] ?>"
                           class="text-danger delete-btn btn btn-sm"><i
                                    class="fa-solid fa-trash"></i>
                        </a>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Aucun rôle défini.</p>
        <?php endif; ?>
    </div>
</div>

<script>
    $(document).on("click", ".delete-btn", function (e) {
        e.preventDefault();
        const id = $(this).data("id");

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
                window.location.href = "<?= base_url('admin/stockrole/delete/') ?>" + id;
            }
        });
    });
</script>
