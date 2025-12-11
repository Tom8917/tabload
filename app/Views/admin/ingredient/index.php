<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Gestion des ingrédients</h3>
    <a href="<?= base_url('admin/ingredient/new') ?>" class="btn btn-primary">Ajouter un ingrédient</a>
</div>

<table class="table table-bordered table-hover" id="ingredientTable">
    <thead>
    <tr>
        <th>Nom</th>
        <th>Type</th>
        <th>Stock</th>
        <th>Unité</th>
        <th>Prix / unité</th>
        <th>Action</th>
    </tr>
    </thead>
    <tbody></tbody>
</table>

<script>
    $(document).ready(function () {
        $('#ingredientTable').DataTable({
            ajax: {
                url: "<?= base_url('admin/ingredient/search') ?>",
                type: "POST"
            },
            columns: [
                { data: 'name' },
                { data: 'type' },
                { data: 'stock' },
                { data: 'unit' },
                { data: 'price_per_unit' },
                {
                    data: 'id',
                    orderable: false,
                    render: function (data) {
                        return `
                            <a href="<?= base_url('admin/ingredient/edit') ?>/${data}" class="btn btn-sm btn-warning">✏️</a>
                            <a href="<?= base_url('admin/ingredient/delete') ?>/${data}" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet ingrédient ?')">🗑️</a>
                        `;
                    }
                }
            ]
        });
    });
</script>
