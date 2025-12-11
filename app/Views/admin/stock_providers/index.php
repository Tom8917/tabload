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
        <h4>Liste des fournisseurs</h4>
        <a href="<?= base_url('/admin/stockprovider/new') ?>" class="btn btn-primary">
            <i class="fa fa-plus"></i> Ajouter
        </a>
    </div>

    <div class="card-body">
        <?php if (empty($providers)): ?>
            <p class="text-muted">Aucun fournisseur enregistré.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                    <tr>
                        <th>Image</th>
                        <th>Nom</th>
                        <th class="text-center">Modifier</th>
                        <th class="text-center">Supprimer</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($providers as $provider): ?>
                        <tr>
                            <td class="text-center">
                                <?php if (!empty($provider['image'])): ?>
                                    <img src="<?= base_url('uploads/stock_providers/' . esc($provider['image'])) ?>"
                                         alt="Aperçu"
                                         class="mt-2"
                                         style="max-height: 70px; object-fit: contain;">
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($provider['name']) ?></td>
                            <td class="text-center">
                                <a href="<?= base_url('/admin/stockprovider/edit/' . $provider['id']) ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa fa-pencil-alt"></i>
                                </a>
                            </td>
                            <td class="text-center">
                                <a href="<?= base_url('/admin/stockprovider/delete/' . $provider['id']) ?>"
                                   class="btn btn-sm btn-outline-danger delete-btn"
                                   data-id="<?= $provider['id'] ?>">
                                    <i class="fa fa-trash"></i>
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

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const url = this.href;

                Swal.fire({
                    title: 'Supprimer ce fournisseur ?',
                    text: 'Cette action est irréversible.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Oui, supprimer',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });
        });
    });
</script>
