<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Liste des
            <mark>Centres</mark>
        </h4>
        <a href="<?= base_url('/admin/center/new'); ?>"><i class="fa-solid fa-plus"></i></a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tableCenters" class="table table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Ville</th>
                    <th>Diminutif</th>
                    <th>Code Postal</th>
                    <th>Adresse</th>
                    <th>Modifier</th>
                    <th>Supprimer</th>
                </tr>
                </thead>
                <tbody>
                <?php if (isset($centers) && !empty($centers)): ?>
                    <?php foreach ($centers as $center): ?>
                        <tr>
                            <td><?= $center['id']; ?></td>
                            <td><?= $center['ville']; ?></td>
                            <td><?= $center['diminutif']; ?></td>
                            <td><?= $center['cp']; ?></td>
                            <td><?= $center['adresse']; ?></td>
                            <td>
                                <a href="<?= base_url('admin/center/' . $center['id']); ?>"><i
                                            class="fa-solid fa-pencil"></i></a>
                            </td>
                            <td>
                                <a href="<?= base_url('admin/center/delete/' . $center['id']); ?>"
                                   class="text-danger delete-btn"
                                   data-id="<?= $center['id']; ?>">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
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

<div id="mapCenters" style="width:100%; height:500px; margin-top:30px;"></div>

<script>

    const apiKey = '2b3c8d6137dd40a5ad811ffedeb8908e';
    const centers = <?= json_encode($centers); ?>;

    document.addEventListener("DOMContentLoaded", () => {
        const mapCenters = L.map('mapCenters').setView([46.603354, 1.888334], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors', maxZoom: 19
        }).addTo(mapCenters);

        // Géocode en série pour respecter quota (max 1 requête/sec)
        let delay = 0;
        centers.forEach(center => {
            if (!center.adresse?.trim()) return;
            const addr = `${center.adresse}, ${center.cp} ${center.ville}`;

            setTimeout(() => {
                fetch(`https://api.opencagedata.com/geocode/v1/json?q=${encodeURIComponent(addr)}&key=${apiKey}`)
                    .then(r => r.json())
                    .then(json => {
                        if (json.results?.length) {
                            const {lat, lng} = json.results[0].geometry;
                            L.marker([lat, lng])
                                .addTo(mapCenters)
                                .bindPopup(`<strong>${center.ville}</strong><br>${center.adresse}<br>${center.cp}`);
                        } else {
                            console.warn('Non trouvé :', addr);
                        }
                    })
                    .catch(err => console.error('Erreur geocode all:', err));
            }, delay);

            delay += 1000; // 1s entre chaque
        });
    });


    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".delete-btn").forEach(function (button) {
            button.addEventListener("click", function (e) {
                e.preventDefault();
                var centerId = this.getAttribute("data-id");
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
    #tableCenters th, #tableCenters td {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
        max-width: 150px;
    }

    @media (max-width: 768px) {
        #tableCenters th, #tableCenters td {
            font-size: 12px;
            max-width: 100px;
        }
    }
</style>