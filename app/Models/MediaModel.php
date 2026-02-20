<?php

namespace App\Models;

use CodeIgniter\Model;

class MediaModel extends Model
{
    protected $table      = 'media';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'folder_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'kind',
        'entity_id',
        'entity_type',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getById(int $id): ?array
    {
        return $this->find($id) ?: null;
    }

    public function getByPath(string $filePath): ?array
    {
        return $this->where('file_path', $filePath)->first() ?: null;
    }

    public function getAll(int $limit = 0, int $offset = 0): array
    {
        if ($limit > 0) {
            return $this->findAll($limit, $offset);
        }
        return $this->findAll();
    }

    public function getAllByEntityType(string $entityType, int $limit = 0, int $offset = 0): array
    {
        $builder = $this->where('entity_type', $entityType);

        if ($limit > 0) {
            return $builder->findAll($limit, $offset);
        }
        return $builder->findAll();
    }

    public function getByEntity(string $entityType, int $entityId): array
    {
        return $this->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->findAll();
    }

    public function getFirstByEntity(string $entityType, int $entityId): ?array
    {
        return $this->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->first() ?: null;
    }

    /**
     * Supprime un media (DB + fichier).
     * - Sécurise le chemin (anti traversal)
     * - Si le fichier n'existe pas, on supprime quand même la ligne DB.
     */
    public function deleteMedia(int $id): bool
    {
        $media = $this->find($id);
        if (! $media) {
            return false;
        }

        $relative = (string) ($media['file_path'] ?? '');
        $relative = str_replace(['\\'], '/', $relative);

        // sécurité basique : pas de chemin parent
        if (str_contains($relative, '../') || str_contains($relative, '..\\')) {
            // on supprime juste l'entrée DB (pour ne pas risquer de supprimer un autre fichier)
            return (bool) $this->delete($id);
        }

        $fullPath = rtrim(FCPATH, '/\\') . '/' . ltrim($relative, '/');

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }

        return (bool) $this->delete($id);
    }

    /**
     * Supprime tous les medias d'un dossier (DB + fichiers).
     */
    public function deleteByFolderId(int $folderId): int
    {
        $items = $this->where('folder_id', $folderId)->findAll();
        $deleted = 0;

        foreach ($items as $m) {
            if ($this->deleteMedia((int) $m['id'])) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Supprime tous les medias d'une liste de dossiers.
     */
    public function deleteByFolderIds(array $folderIds): int
    {
        $folderIds = array_values(array_filter(array_map('intval', $folderIds)));
        if (empty($folderIds)) return 0;

        $items = $this->whereIn('folder_id', $folderIds)->findAll();
        $deleted = 0;

        foreach ($items as $m) {
            if ($this->deleteMedia((int) $m['id'])) {
                $deleted++;
            }
        }

        return $deleted;
    }
}
