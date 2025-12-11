<div class="row">
    <div class="col">
        <form action="<?= isset($center) ? base_url("/admin/center/update") : base_url("/admin/center/create") ?>" method="POST">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">
                        <?= isset($center) ? "Editer " . $center['ville'] : "Créer un Centre" ?>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="ville" class="form-label">Ville</label>
                        <input type="text" class="form-control" id="ville" placeholder="Ville" value="<?= isset($center) ? $center['ville'] : ""; ?>" name="ville">
                    </div>
                    <div class="mb-3">
                        <label for="diminutif" class="form-label">Diminutif</label>
                        <input type="text" class="form-control" id="diminutif" placeholder="Diminutif" value="<?= isset($center) ? $center['diminutif'] : ""; ?>" name="diminutif">
                    </div>
                    <div class="mb-3">
                        <label for="cp" class="form-label">Code Postal</label>
                        <input type="text" class="form-control" id="cp" placeholder="Code Postal" value="<?= isset($center) ? $center['cp'] : ""; ?>" name="cp">
                    </div>
                    <div class="mb-3">
                        <label for="adresse" class="form-label">Adresse</label>
                        <textarea class="form-control" id="adresse" placeholder="Adresse" name="adresse"><?= isset($center) ? $center['adresse'] : ""; ?></textarea>
                    </div>

                    <div id="mapForm" style="width:100%; height:350px; margin-top:30px;"></div>

                </div>
                <div class="card-footer text-end">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        Annuler
                    </button>
                    <?php if (isset($center)): ?>
                        <input type="hidden" name="id" value="<?= $center['id']; ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <?= isset($center) ? "Sauvegarder" : "Enregistrer" ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const apiKey = '2b3c8d6137dd40a5ad811ffedeb8908e';

    document.addEventListener("DOMContentLoaded", () => {
        const mapForm = L.map('mapForm').setView([46.603354,1.888334],6);
        L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
            attribution:'&copy; OpenStreetMap contributors', maxZoom:19
        }).addTo(mapForm);

        let markerOne;
        const geocodeOne = () => {
            const v = document.getElementById("ville").value.trim();
            const c = document.getElementById("cp").value.trim();
            const a = document.getElementById("adresse").value.trim();
            if (!v || !c || !a) return;
            const addr = `${a}, ${c} ${v}`;

            fetch(`https://api.opencagedata.com/geocode/v1/json?q=${encodeURIComponent(addr)}&key=${apiKey}`)
                .then(r => r.json())
                .then(json => {
                    if (json.results?.length) {
                        const {lat, lng} = json.results[0].geometry;
                        mapForm.setView([lat, lng],15);
                        if (markerOne) mapForm.removeLayer(markerOne);
                        markerOne = L.marker([lat, lng]).addTo(mapForm)
                            .bindPopup("Position du centre").openPopup();
                    }
                })
                .catch(err => console.error('Erreur geocode one:', err));
        };

        // Déclencheurs avec debounce
        let to;
        ["adresse","cp","ville"].forEach(id => {
            document.getElementById(id).addEventListener("input", () => {
                clearTimeout(to);
                to = setTimeout(geocodeOne, 700);
            });
        });

        // Initial si déjà valeurs
        if (
            document.getElementById("adresse").value.trim() !== '' &&
            document.getElementById("cp").value.trim() !== '' &&
            document.getElementById("ville").value.trim() !== ''
        ) {
            geocodeOne();
        }
    });
</script>