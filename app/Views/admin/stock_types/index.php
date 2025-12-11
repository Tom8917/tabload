<?php if (session()->getFlashdata('success')): ?>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            toastr.success("<?= esc(session('success'), 'js') ?>");
        });
    </script>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            toastr.error("<?= esc(session('error'), 'js') ?>");
        });
    </script>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Liste des types</h4>
        <a href="<?= base_url('/admin/stocktype/new') ?>" class="btn btn-primary">
            <i class="fa fa-plus"></i> Ajouter
        </a>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                <tr>
                    <th>Image</th>
                    <th>Nom</th>
                    <th>Contenance</th>
                    <th class="text-center">Modifier</th>
                    <th class="text-center">Supprimer</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($types as $type): ?>
                    <tr>
                        <td class="text-center">
                            <?php if (!empty($type['image'])): ?>
                                <img src="<?= base_url('uploads/stock_types/' . esc($type['image'])) ?>"
                                     alt="Aperçu"
                                     class="mt-2"
                                     style="max-height: 100px; object-fit: contain; border: none;">
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($type['name']) ?></td>
                        <td><?= number_format((float)$type['unit_volume_ml'], 2, ',', ' ') ?></td>
                        <td class="text-center">
                            <a href="<?= base_url('/admin/stocktype/edit/' . $type['id']) ?>" class="btn btn-sm me-5"><i
                                        class="fa-solid fa-pencil"></i></a>
                        </td>
                        <td class="text-center">
                            <a href="#"
                               data-id="<?= $type['id'] ?>"
                               class="text-danger delete-btn btn btn-sm"><i
                                        class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
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
                window.location.href = "<?= base_url('admin/stocktype/delete/') ?>" + id;
            }
        });
    });
</script>

<style>
    @media (max-width: 576px) {
        table td,
        table th {
            white-space: normal !important;
            word-break: break-word;
            font-size: 0.875rem;
        }

        table img {
            max-width: 100%;
            height: auto;
        }
    }
</style>
