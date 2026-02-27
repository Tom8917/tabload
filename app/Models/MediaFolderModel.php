<?php

namespace App\Models;

use CodeIgniter\Model;

class MediaFolderModel extends Model
{
    protected $table      = 'media_folders';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'name',
        'parent_id',
        'user_id',
        'sort_order',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Récupère un dossier.
     */
    public function getById(int $id): ?array
    {
        return $this->find($id) ?: null;
    }

    /**
     * Récupère un dossier en imposant le propriétaire.
     * (Front : suppression autorisée uniquement si le créateur correspond)
     */
    public function getByIdForUser(int $folderId, int $userId): ?array
    {
        return $this->where('id', $folderId)
            ->where('user_id', $userId)
            ->first() ?: null;
    }

    /**
     * Liste des enfants d'un dossier (tri stable).
     */
    public function getChildren(?int $parentId): array
    {
        $builder = $this->builder();

        if ($parentId === null) {
            $builder->where('parent_id IS NULL', null, false);
        } else {
            $builder->where('parent_id', $parentId);
        }

        return $builder
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Liste des enfants d'un dossier pour un user (si tu en as besoin ailleurs).
     */
    public function getChildrenForUser(?int $parentId, int $userId): array
    {
        $builder = $this->builder();

        if ($parentId === null) {
            $builder->where('parent_id IS NULL', null, false);
        } else {
            $builder->where('parent_id', $parentId);
        }

        $builder->where('user_id', $userId);

        return $builder
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Vérifie si le dossier appartient à l'utilisateur.
     */
    public function isOwner(int $folderId, int $userId): bool
    {
        return (bool) $this->where('id', $folderId)
            ->where('user_id', $userId)
            ->countAllResults();
    }

    /**
     * Renomme un dossier (admin uniquement côté controller).
     * Ici on fait juste l'update safe.
     */
    public function renameFolder(int $folderId, string $newName): bool
    {
        $newName = trim($newName);
        if ($newName === '' || mb_strlen($newName) > 150) {
            return false;
        }

        return (bool) $this->update($folderId, ['name' => $newName]);
    }

    /**
     * Renomme un dossier en imposant le propriétaire (si tu veux un jour l'activer en front).
     * (Pour ton besoin actuel, front n'a PAS le droit de rename, mais ça peut servir.)
     */
    public function renameFolderForUser(int $folderId, int $userId, string $newName): bool
    {
        if (! $this->isOwner($folderId, $userId)) {
            return false;
        }
        return $this->renameFolder($folderId, $newName);
    }

    /**
     * Récupère tous les IDs d'un dossier + ses descendants.
     * Utile pour sécuriser une suppression "branche entière" (front/admin).
     */
    public function getTreeIds(int $folderId): array
    {
        $ids = [];
        $queue = [$folderId];

        while (! empty($queue)) {
            $current = array_shift($queue);
            if (in_array($current, $ids, true)) {
                continue;
            }
            $ids[] = $current;

            $children = $this->select('id')
                ->where('parent_id', $current)
                ->findAll();

            foreach ($children as $child) {
                $queue[] = (int) $child['id'];
            }
        }

        return $ids;
    }

    /**
     * Vérifie que tout un arbre appartient à un user (important si multi-user).
     */
    public function treeBelongsToUser(int $folderId, int $userId): bool
    {
        $ids = $this->getTreeIds($folderId);
        if (empty($ids)) return false;

        $count = $this->whereIn('id', $ids)
            ->where('user_id', $userId)
            ->countAllResults();

        return $count === count($ids);
    }


    public function getFoldersWithAuthor(): array
    {
        return $this->select('media_folders.*, u.firstname, u.lastname')
            ->join('`user` u', 'u.id = media_folders.user_id', 'left')
            ->orderBy('media_folders.created_at', 'DESC')
            ->findAll();
    }

    public function getByIdWithAuthor(int $id): ?array
    {
        $row = $this->select('media_folders.*, u.firstname, u.lastname')
            ->join('`user` u', 'u.id = media_folders.user_id', 'left')
            ->where('media_folders.id', $id)
            ->first();

        return $row ?: null;
    }

    public function getChildrenWithAuthor(?int $parentId): array
    {
        $b = $this->select('media_folders.*, u.firstname, u.lastname')
            ->join('`user` u', 'u.id = media_folders.user_id', 'left');

        if ($parentId === null) {
            $b->where('media_folders.parent_id IS NULL', null, false);
        } else {
            $b->where('media_folders.parent_id', $parentId);
        }

        return $b->orderBy('media_folders.sort_order', 'ASC')
            ->orderBy('media_folders.name', 'ASC')
            ->findAll();
    }
}
