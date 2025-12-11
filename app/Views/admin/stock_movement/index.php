<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Mouvements de stock - <?= esc($item['name']) ?></h4>
        <a href="<?= base_url('/admin/stockitem') ?>" class="btn btn-secondary">← Retour au stock</a>
    </div>
    <div class="card-body">
        <form method="post" action="<?= base_url('/admin/stockmovement/manualadd') ?>" class="row g-3 mb-4">
            <input type="hidden" name="id_stock_item" value="<?= esc($item['id']) ?>">

            <div class="col-md-2">
                <select name="type" class="form-control" required>
                    <option value="in">Entrée</option>
                    <option value="out">Sortie</option>
                </select>
            </div>

            <div class="col-md-2">
                <input type="number" name="quantity" step="0.01" class="form-control" placeholder="Quantité" required>
            </div>

            <div class="col-md-6">
                <input type="text" name="note" class="form-control" placeholder="Note (ex: correction, erreur...)">
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-success w-100">Ajouter</button>
            </div>
        </form>

        <table id="movementTable" class="table table-striped">
            <thead>
            <tr>
                <th>Type</th>
                <th>Quantité</th>
                <th>Note</th>
                <th>Date</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#movementTable').DataTable({
            ajax: {
                url: "<?= base_url('/admin/stockmovement/search/' . $item['id']) ?>",
                type: "POST"
            },
            order: [[3, 'desc']],
            columns: [
                {
                    data: 'type',
                    render: function (data) {
                        const label = data === 'in' ? 'Entrée' : 'Sortie';
                        const badge = data === 'in' ? 'success' : 'danger';
                        return `<span class="badge bg-${badge}">${label}</span>`;
                    }
                },
                { data: 'quantity' },
                { data: 'note' },
                { data: 'created_at' }
            ]
        });
    });
</script>
