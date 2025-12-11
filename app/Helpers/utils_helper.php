<?php

if (!function_exists('generate_slug')) {
     function generateSlug($string)
    {
        // Normaliser la chaîne pour enlever les accents
        $string = \Normalizer::normalize($string, \Normalizer::FORM_D);
        $string = preg_replace('/[\p{Mn}]/u', '', $string);

        // Convertir les caractères spéciaux en minuscules et les espaces en tirets
        $string = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));

        return $string;
    }
}

if (!function_exists('upload_file')) {

    /**
     * Gérer l'upload d'un fichier dans un dossier organisé par année/mois, avec un nom de fichier slugifié.
     * Mettre à jour ou insérer les informations du média dans la base de données.
     *
     * @param \CodeIgniter\Files\File $file - Le fichier à uploader
     * @param string $subfolder - Un sous-dossier optionnel pour organiser les fichiers
     * @param string|null $customName - Le nom personnalisé pour le fichier (par exemple, nom d'utilisateur ou titre d'item)
     * @param array|null $mediaData - Données du média à insérer dans la base de données
     * @param bool $isMultiple - Indique si plusieurs images sont autorisées pour cet entity_id et entity_type
     * @param array $acceptedMimeTypes - Liste des types MIME acceptés (par défaut : images seulement)
     * @param int $maxSize - Taille maximale du fichier en Ko (par défaut : 2048 Ko = 2 Mo)
     * @return array|bool|string - Le chemin du fichier uploadé ou un tableau d'erreur
     * @throws ReflectionException
     */
    function upload_file(\CodeIgniter\Files\File $file, string $subfolder = '', string $customName = null, array $mediaData = null, bool $isMultiple = false, array $acceptedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'], int $maxSize = 2048): array|bool|string
    {

        // Vérifier le code d'erreur d'upload
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return [
                'status' => 'error',
                'message' => getUploadErrorMessage($file->getError())
            ];
        }

        // Vérifier si le fichier a déjà été déplacé
        if ($file->hasMoved()) {
            return [
                'status' => 'error',
                'message' => 'Le fichier a déjà été déplacé.'
            ];
        }

        // Valider le type MIME
        if (!in_array($file->getMimeType(), $acceptedMimeTypes)) {
            return [
                'status' => 'error',
                'message' => 'Type de fichier non accepté. Types acceptés : ' . implode(', ', $acceptedMimeTypes)
            ];
        }

        // Valider la taille du fichier
        if ($file->getSizeByUnit('kb') > $maxSize) {
            return [
                'status' => 'error',
                'message' => 'La taille du fichier dépasse la limite autorisée de ' . $maxSize . ' Ko.'
            ];
        }
        // Vérifier que le fichier est valide
        if (!$file->isValid()) {
            return [
                'status' => 'error',
                'message' => 'Le fichier est invalide.'
            ];
        }
        // Obtenir l'année et le mois actuels
        $year = date('Y');
        $month = date('m');

        // Construire le chemin de l'upload dans /public/uploads/année/mois/
        $uploadPath = "uploads/$year/$month";

        // Ajouter un sous-dossier si spécifié
        if (!empty($subfolder)) {
            $uploadPath .= "/$subfolder";
        }

        // Créer le dossier s'il n'existe pas
        if (!is_dir(FCPATH . $uploadPath)) {
            mkdir(FCPATH . $uploadPath, 0755, true);
        }

        // Générer un nom de fichier unique basé sur le nom personnalisé (ou un nom aléatoire)
        if ($customName) {
            // Générer un slug à partir du nom personnalisé
            $slug = generateSlug($customName);
            // Ajouter l'extension originale du fichier
            $extension = $file->getExtension();
            // Créer un nom unique en ajoutant un timestamp pour éviter les doublons
            $newName = $slug . '-' . time() . '.' . $extension;
        } else {
            // Si aucun nom personnalisé, utiliser un nom aléatoire
            $newName = $file->getRandomName();
        }

        // Déplacer le fichier vers le dossier uploads/année/mois
        if (!$file->move(FCPATH . $uploadPath, $newName)) {
            return [
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de l\'upload du fichier.'
            ];
        }

        // Retourner le chemin relatif du fichier
        $filePath = "$uploadPath/$newName";

        // Gérer l'enregistrement du fichier dans la base de données si $mediaData est fourni
        if ($mediaData) {
            $mediaModel = model('MediaModel');

            if ($isMultiple) {
                // Pour les entités où plusieurs images sont autorisées, on insère directement
                $mediaModel->insert(array_merge($mediaData, ['file_path' => $filePath,'created_at' => date('Y-m-d H:i:s')]));
            } else {
                // Pour les entités avec une image unique, on gère la mise à jour ou la suppression des anciennes entrées
                $existingMedia = $mediaModel->where([
                    'entity_id' => $mediaData['entity_id'],
                    'entity_type' => $mediaData['entity_type'],
                    'created_at' => date('Y-m-d H:i:s')
                ])->first();

                if ($existingMedia) {
                    // Mettre à jour les champs file_path et created_at de l'enregistrement existant
                    $mediaModel->update($existingMedia['id'], [
                        'file_path' => $filePath,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                } else {
                    // Supprimer les anciennes entrées pour le même entity_id et entity_type
                    $mediaModel->where([
                        'entity_id' => $mediaData['entity_id'],
                        'entity_type' => $mediaData['entity_type']
                    ])->delete();

                    // Insérer le nouvel enregistrement
                    $mediaModel->insert(array_merge($mediaData, ['file_path' => $filePath,'created_at' => date('Y-m-d H:i:s')]));
                }
            }
        }

        return $filePath; // Retourner le chemin du fichier uploadé
    }
}

/**
 * Convertit le code d'erreur d'upload en message explicite.
 *
 * @param int $errorCode - Le code d'erreur
 * @return string - Le message d'erreur correspondant
 */
function getUploadErrorMessage(int $errorCode): string {
    switch ($errorCode) {
        case UPLOAD_ERR_OK:
            return 'Aucune erreur, le fichier est valide.';
        case UPLOAD_ERR_INI_SIZE:
            return 'Le fichier dépasse la taille maximale autorisée par la configuration PHP.';
        case UPLOAD_ERR_FORM_SIZE:
            return 'Le fichier dépasse la taille maximale autorisée par le formulaire HTML.';
        case UPLOAD_ERR_PARTIAL:
            return 'Le fichier n\'a été que partiellement téléchargé.';
        case UPLOAD_ERR_NO_FILE:
            return 'Aucun fichier n\'a été téléchargé.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Le dossier temporaire est manquant.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Échec de l\'écriture du fichier sur le disque.';
        case UPLOAD_ERR_EXTENSION:
            return 'Une extension PHP a arrêté l\'upload du fichier.';
        default:
            return 'Une erreur inconnue est survenue lors de l\'upload.';
    }
}
