$(document).ready(function () {
    //Chargement de l'image (unique ou multiple) lors de l'upload pour la previsualisation
    $('#image').change(function (event) {
        let previewsContainer = $('#preview'); // L'élément contenant toutes les prévisualisations
        previewsContainer.empty(); // Vider le conteneur pour éviter d'accumuler les anciennes prévisualisations

        let fichiers = event.target.files; // Récupère tous les fichiers sélectionnés

        if (fichiers && fichiers.length > 0) { // Vérifie s'il y a des fichiers sélectionnés
            Array.from(fichiers).forEach(function (fichier) { // Parcourt chaque fichier
                let lecteur = new FileReader(); // Crée un lecteur de fichier
                lecteur.onload = function (e) {
                    let img = $('<img >', { // Crée un élément <img>
                        src: e.target.result,
                        loading: 'lazy',
                        class: 'img-thumbnail', // Classe pour le style
                        // Style optionnel
                    });
                    // Crée un div avec la classe "col" et y ajoute l'image
                    let colDiv = $('<div>', {
                        class: 'col mb-2'
                    }).append(img);

                    // Ajoute le div contenant l'image au conteneur
                    previewsContainer.append(colDiv);                };
                lecteur.readAsDataURL(fichier); // Lit le fichier en tant qu'URL de données
            });
        } else {
            previewsContainer.text('Aucun fichier sélectionné.'); // Message si aucun fichier
        }
    });
});