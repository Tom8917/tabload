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

    public function getById(int $id): ?array
    {
        return $this->find($id) ?: null;
    }

    public function getByIdForUser(int $folderId, int $userId): ?array
    {
        return $this->where('id', $folderId)
            ->where('user_id', $userId)
            ->first() ?: null;
    }

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

    public function isOwner(int $folderId, int $userId): bool
    {
        return (bool) $this->where('id', $folderId)
            ->where('user_id', $userId)
            ->countAllResults();
    }

    public function renameFolder(int $folderId, string $newName): bool
    {
        $newName = trim($newName);
        if ($newName === '' || mb_strlen($newName) > 150) {
            return false;
        }

        return (bool) $this->update($folderId, ['name' => $newName]);
    }

    public function renameFolderForUser(int $folderId, int $userId, string $newName): bool
    {
        if (! $this->isOwner($folderId, $userId)) {
            return false;
        }
        return $this->renameFolder($folderId, $newName);
    }

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
