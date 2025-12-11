<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Liste des
            <mark>Marques</mark>
            de Matériels
        </h4>
        <a href="<?= base_url('/admin/materialbrand/new'); ?>"><i class="fa-solid fa-plus"></i></a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tableMaterialBrands" class="table table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Marque</th>
                    <th>Slug</th>
                    <th>Modifier</th>
                    <th>Supprimer</th>
                </tr>
                </thead>
                <tbody>
                <?php if (isset($materialbrands) && !empty($materialbrands)): ?>
                    <?php foreach ($materialbrands as $materialbrand): ?>
                        <tr>
                            <td><?= $materialbrand['id']; ?></td>
                            <td><?= $materialbrand['marque']; ?></td>
                            <td><?= $materialbrand['slug']; ?></td>
                            <td>
                                <a href="<?= base_url('admin/materialbrand/' . $materialbrand['id']); ?>"><i
                                            class="fa-solid fa-pencil"></i></a>
                            </td>
                            <td>
                                <a href="<?= base_url('admin/materialbrand/delete/' . $materialbrand['id']); ?>"
                                   class="text-danger delete-btn" data-id="<?= $materialbrand['id']; ?>"><i
                                            class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Aucun centre disponible.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".delete-btn").forEach(function (button) {
            button.addEventListener("click", function (e) {
                e.preventDefault();
                var materialbrandId = this.getAttribute("data-id");
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
    });
</script>

<style>
    #tableMaterialBrands th, #tableMaterialBrands td {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
        max-width: 150px;
    }

    @media (max-width: 768px) {
        #tableMaterialBrands th, #tableMaterialBrands td {
            font-size: 12px;
            max-width: 100px;
        }
    }
</style>