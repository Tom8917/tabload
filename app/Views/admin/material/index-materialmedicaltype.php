<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Liste des
            <mark>Types</mark>
            de Matériels Médicales
        </h4>
        <a href="<?= base_url('/admin/materialmedicaltype/new'); ?>"><i class="fa-solid fa-plus"></i></a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tableMaterialMedicalTypes" class="table table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Slug</th>
                    <th>Modifier</th>
                    <th>Supprimer</th>
                </tr>
                </thead>
                <tbody>
                <?php if (isset($materialmedicaltypes) && !empty($materialmedicaltypes)): ?>
                    <?php foreach ($materialmedicaltypes as $materialmedicaltype): ?>
                        <tr>
                            <td><?= $materialmedicaltype['id']; ?></td>
                            <td><?= $materialmedicaltype['type']; ?></td>
                            <td><?= $materialmedicaltype['slug']; ?></td>
                            <td>
                                <a href="<?= base_url('admin/materialmedicaltype/' . $materialmedicaltype['id']); ?>"><i
                                            class="fa-solid fa-pencil"></i></a>
                            </td>
                            <td>
                                <a href="<?= base_url('admin/materialmedicaltype/delete/' . $materialmedicaltype['id']); ?>"
                                   class="text-danger delete-btn" data-id="<?= $materialmedicaltype['id']; ?>"><i
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
                var materialmedicaltypeId = this.getAttribute("data-id");
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
    #tableMaterialMedicalTypes th, #tableMaterialMedicalTypes td {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
        max-width: 150px;
    }

    @media (max-width: 768px) {
        #tableMaterialMedicalTypes th, #tableMaterialMedicalTypes td {
            font-size: 12px;
            max-width: 100px;
        }
    }
</style>